<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception; // Keep general exception for DB transaction issues
use Illuminate\Database\Eloquent\ModelNotFoundException; // Catch this specifically if needed, though validation should prevent it

class TransferController extends Controller
{
    protected AuthorizationService $authorizationService;
    protected NotificationService $notificationService;

    public function __construct(AuthorizationService $authorizationService, NotificationService $notificationService)
    {
        $this->authorizationService = $authorizationService;
        $this->notificationService = $notificationService;
    }

    public function store(StoreTransferRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $payer = $request->user();
        $amount = (float) $validatedData["amount"]; // Ensure float type

        // Find payee - validation should guarantee existence
        $payee = User::find($validatedData["payee_id"]);

        // Defensive check: If validation somehow failed and payee is null.
        if (!$payee) {
            Log::error("Payee not found in controller despite validation passing.", ["payee_id" => $validatedData["payee_id"]]);
            // This should ideally be a 422 returned by the FormRequest validation itself.
            // Returning 422 here might mask the root cause if validation isn't working as expected.
            return response()->json(["message" => "Destinatário inválido ou não encontrado."], 422);
        }

        $payerWallet = $payer->wallet;
        $payeeWallet = $payee->wallet; // Safe to access since $payee is confirmed

        // 1. Validate Payer's Balance
        if ($payerWallet->balance < $amount) {
            Log::info("Transferência falhou: Saldo insuficiente", ["payer_id" => $payer->id, "amount" => $amount, "balance" => $payerWallet->balance]);
            return response()->json(["message" => "Saldo insuficiente para realizar a transferência."], 400);
        }

        // 2. Check External Authorizer
        $isAuthorized = $this->authorizationService->check();
        if (!$isAuthorized) {
            Log::info("Transferência falhou: Não autorizada", ["payer_id" => $payer->id, "payee_id" => $payee->id, "amount" => $amount]);
            // Record the failed transaction attempt due to authorization failure
            Transaction::create([
                "payer_wallet_id" => $payerWallet->id,
                "payee_wallet_id" => $payeeWallet->id,
                "amount" => $amount,
                "status" => "failed",
                "authorization_code" => null,
                "authorized_at" => null,
            ]);
            return response()->json(["message" => "Transferência não autorizada pelo serviço externo."], 403);
        }

        // 3. Perform Transfer within DB Transaction
        DB::beginTransaction();
        try {
            // Lock rows for update to prevent race conditions
            // Re-fetch within transaction and lock
            $payerWallet = Wallet::lockForUpdate()->findOrFail($payerWallet->id);
            $payeeWallet = Wallet::lockForUpdate()->findOrFail($payeeWallet->id);

            // Re-check balance within transaction for ultimate safety
            if ($payerWallet->balance < $amount) {
                 DB::rollBack();
                 Log::warning("Transferência falhou DENTRO da transação: Saldo insuficiente", ["payer_id" => $payer->id, "amount" => $amount, "balance" => $payerWallet->balance]);
                 return response()->json(["message" => "Saldo insuficiente para realizar a transferência."], 400);
            }

            // Perform the transfer
            $payerWallet->decrement("balance", $amount);
            $payeeWallet->increment("balance", $amount);

            // Record the successful transaction
            $transaction = Transaction::create([
                "payer_wallet_id" => $payerWallet->id,
                "payee_wallet_id" => $payeeWallet->id,
                "amount" => $amount,
                "status" => "completed",
                "authorization_code" => uniqid("auth_"), // Placeholder
                "authorized_at" => now(),
            ]);

            DB::commit();
            Log::info("Transferência concluída com sucesso", ["transaction_id" => $transaction->id]);

            // 4. Send Notifications (after successful commit)
            try {
                 $payerNotificationSent = $this->notificationService->send($payer, "Transferência de R$ {$amount} enviada com sucesso para {$payee->name}.");
                 $payeeNotificationSent = $this->notificationService->send($payee, "Você recebeu uma transferência de R$ {$amount} de {$payer->name}.");
                 if (!$payerNotificationSent || !$payeeNotificationSent) {
                     Log::warning("Falha ao enviar uma ou mais notificações para a transação ID: {$transaction->id}");
                 }
            } catch (Exception $notificationError) {
                 Log::error("Erro ao enviar notificações para a transação ID: {$transaction->id}. Erro: " . $notificationError->getMessage());
            }

            return response()->json(["message" => "Transferência realizada com sucesso.", "transaction_id" => $transaction->id], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erro crítico durante a transação de transferência: " . $e->getMessage(), [
                "payer_id" => $payer->id,
                "payee_id" => $payee ? $payee->id : $validatedData["payee_id"],
                "amount" => $amount,
                "exception_trace" => $e->getTraceAsString() // More detailed log
            ]);

            // Record a generic failed transaction if the error occurred during the DB operations
            // Check if wallets were loaded before trying to access IDs
            if (isset($payerWallet) && isset($payeeWallet)) {
                 Transaction::create([
                     "payer_wallet_id" => $payerWallet->id,
                     "payee_wallet_id" => $payeeWallet->id,
                     "amount" => $amount,
                     "status" => "failed",
                 ]);
            }

            return response()->json(["message" => "Erro interno ao processar a transferência. Tente novamente mais tarde."], 500);
        }
    }
}

