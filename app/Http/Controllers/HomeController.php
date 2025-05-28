<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        
        // Buscar transações relacionadas ao usuário (enviadas ou recebidas)
        $transactions = Transaction::whereHas('payerWallet', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhereHas('payeeWallet', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('home', compact('transactions'));
    }
}
