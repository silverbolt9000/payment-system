<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function transaction_belongs_to_payer_wallet()
    {
        $payerWallet = Wallet::factory()->create(['balance' => 100]);
        $payeeWallet = Wallet::factory()->create();
        
        $transaction = Transaction::create([
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 50,
            'status' => 'completed'
        ]);

        $this->assertInstanceOf(Wallet::class, $transaction->payerWallet);
        $this->assertEquals($payerWallet->id, $transaction->payerWallet->id);
    }

    /** @test */
    public function transaction_belongs_to_payee_wallet()
    {
        $payerWallet = Wallet::factory()->create(['balance' => 100]);
        $payeeWallet = Wallet::factory()->create();
        
        $transaction = Transaction::create([
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 50,
            'status' => 'completed'
        ]);

        $this->assertInstanceOf(Wallet::class, $transaction->payeeWallet);
        $this->assertEquals($payeeWallet->id, $transaction->payeeWallet->id);
    }

    /** @test */
    public function transaction_casts_amount_to_decimal()
    {
        $payerWallet = Wallet::factory()->create(['balance' => 100]);
        $payeeWallet = Wallet::factory()->create();
        
        $transaction = Transaction::create([
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 50.75,
            'status' => 'completed'
        ]);

        $this->assertIsFloat($transaction->amount);
        $this->assertEquals(50.75, $transaction->amount);
    }
}
