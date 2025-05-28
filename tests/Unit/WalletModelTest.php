<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function wallet_belongs_to_user()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $wallet->user);
        $this->assertEquals($user->id, $wallet->user->id);
    }

    /** @test */
    public function wallet_has_many_payer_transactions()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $wallet1 = Wallet::factory()->create(['user_id' => $user1->id, 'balance' => 100]);
        $wallet2 = Wallet::factory()->create(['user_id' => $user2->id]);
        
        $transaction = Transaction::create([
            'payer_wallet_id' => $wallet1->id,
            'payee_wallet_id' => $wallet2->id,
            'amount' => 50,
            'status' => 'completed'
        ]);

        $this->assertCount(1, $wallet1->payerTransactions);
        $this->assertInstanceOf(Transaction::class, $wallet1->payerTransactions->first());
    }

    /** @test */
    public function wallet_has_many_payee_transactions()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $wallet1 = Wallet::factory()->create(['user_id' => $user1->id, 'balance' => 100]);
        $wallet2 = Wallet::factory()->create(['user_id' => $user2->id]);
        
        $transaction = Transaction::create([
            'payer_wallet_id' => $wallet1->id,
            'payee_wallet_id' => $wallet2->id,
            'amount' => 50,
            'status' => 'completed'
        ]);

        $this->assertCount(1, $wallet2->payeeTransactions);
        $this->assertInstanceOf(Transaction::class, $wallet2->payeeTransactions->first());
    }
}
