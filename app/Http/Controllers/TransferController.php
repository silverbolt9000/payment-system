<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTransferRequest;
use App\Models\User;
use App\Models\Transaction;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    protected $authorizationService;
    protected $notificationService;

    public function __construct(AuthorizationService $authorizationService, NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->authorizationService = $authorizationService;
        $this->notificationService = $notificationService;
    }

    /**
     * Show the form for creating a new transfer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Verificar se o usuário é do tipo comum
        if (Auth::user()->user_type !== 'common') {
            return redirect()->route('home')->with('error', 'Apenas usuários comuns podem realizar transferências.');
        }

        return view('transfers.create');
    }

    /**
     * Store a newly created transfer in storage.
     *
     * @param  \App\Http\Requests\StoreTransferRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransferRequest $request)
    {
        $user = Auth::user();
        
        // Verificar se o usuário é do tipo comum
        if ($user->user_type !== 'common') {
            return redirect()->route('home')->with('error', 'Apenas usuários comuns podem realizar transferências.');
        }

        //verificar se o user tem carteira
        if (!$user->wallet) {
            return redirect()->back()->with('error', 'Você não possui uma carteira para realizar transferências.')->withInput();
        }

        $validatedData = $request->validated();
        $payee = User::find($validatedData['payee_id']);
        
        // Verificar se o destinatário existe
        if (!$payee) {
            return redirect()->back()->with('error', 'Destinatário não encontrado.')->withInput();
        }
        
        // Verificar se o usuário está tentando transferir para si mesmo
        if ($payee->id === $user->id) {
            return redirect()->back()->with('error', 'Você não pode transferir dinheiro para si mesmo.')->withInput();
        }

        // Converter o valor para float (substituindo vírgula por ponto)
        $amount = (float) str_replace(',', '.', $validatedData['amount']);
        
        // Verificar se o usuário tem saldo suficiente
        if ($user->wallet->balance < $amount) {
            return redirect()->back()->with('error', 'Saldo insuficiente para realizar a transferência.')->withInput();
        }

        // Verificar autorização do serviço externo
        $isAuthorized = $this->authorizationService->check();
        if (!$isAuthorized) {
            // Registrar a transação como falha
            Transaction::create([
                'payer_wallet_id' => $user->wallet->id,
                'payee_wallet_id' => $payee->wallet->id,
                'amount' => $amount,
                'status' => 'failed',
            ]);
            
            return redirect()->back()->with('error', 'Transferência não autorizada pelo serviço externo.')->withInput();
        }

        // Iniciar transação no banco de dados
        DB::beginTransaction();
        
        try {
            // Atualizar saldos
            $user->wallet->decrement('balance', $amount);
            $payee->wallet->increment('balance', $amount);
            
            // Registrar a transação como concluída
            $transaction = Transaction::create([
                'payer_wallet_id' => $user->wallet->id,
                'payee_wallet_id' => $payee->wallet->id,
                'amount' => $amount,
                'status' => 'completed',
                'authorization_code' => uniqid('auth_'),
                'authorized_at' => now(),
            ]);
            
            DB::commit();
            
            // Enviar notificações
            try {
                $this->notificationService->send($user, "Transferência de R$ {$amount} enviada com sucesso para {$payee->name}.");
                $this->notificationService->send($payee, "Você recebeu uma transferência de R$ {$amount} de {$user->name}.");
            } catch (\Exception $e) {
                Log::error("Erro ao enviar notificações: " . $e->getMessage());
                // Não reverter a transação por falha na notificação
            }
            
            return redirect()->route('home')->with('success', 'Transferência realizada com sucesso!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao processar transferência: " . $e->getMessage());
            
            return redirect()->back()->with('error', 'Ocorreu um erro ao processar a transferência. Por favor, tente novamente.')->withInput();
        }
    }
}
