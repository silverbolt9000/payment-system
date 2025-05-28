<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AuthorizationService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Notification; // Use Laravel's Notification facade for mocking
// use App\Notifications\TransferNotification; // Assuming a notification class exists

class TransferTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $payer;
    protected User $payee;
    protected User $shopkeeper;
    protected Wallet $payerWallet;
    protected Wallet $payeeWallet;
    protected Wallet $shopkeeperWallet;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users and wallets
        $this->payer = User::factory()->create(["user_type" => "common"]);
        $this->payee = User::factory()->create(["user_type" => "common"]);
        $this->shopkeeper = User::factory()->create(["user_type" => "shopkeeper"]);

        $this->payerWallet = Wallet::factory()->create(["user_id" => $this->payer->id, "balance" => 100.00]);
        $this->payeeWallet = Wallet::factory()->create(["user_id" => $this->payee->id, "balance" => 50.00]);
        $this->shopkeeperWallet = Wallet::factory()->create(["user_id" => $this->shopkeeper->id, "balance" => 200.00]);

        // Mock external services using Laravel's mocking
        $this->mock(AuthorizationService::class, function ($mock) {
            // Default behavior: authorized
            $mock->shouldReceive('check')->andReturn(true);
        });
        $this->mock(NotificationService::class, function ($mock) {
            // Default behavior: success
            $mock->shouldReceive('send')->andReturn(true);
        });


        // Mock Laravel Notifications (alternative/complementary)
        Notification::fake();
    }

    /** @test */
    public function a_common_user_can_successfully_transfer_money_to_another_user()
    {
        $amount = 50.00;

        // Ensure mocks are set for success (default in setUp)

        $response = $this->actingAs($this->payer, "sanctum")
                         ->postJson("/api/transfers", [
                             "payee_id" => $this->payee->id,
                             "amount" => $amount,
                         ]);

        $response->assertStatus(200)
                 ->assertJson(["message" => "Transferência realizada com sucesso."]);

        // Check balances
        $this->assertDatabaseHas("wallets", [
            "id" => $this->payerWallet->id,
            "balance" => 50.00, // 100 - 50
        ]);
        $this->assertDatabaseHas("wallets", [
            "id" => $this->payeeWallet->id,
            "balance" => 100.00, // 50 + 50
        ]);

        // Check transaction record
        $this->assertDatabaseHas("transactions", [
            "payer_wallet_id" => $this->payerWallet->id,
            "payee_wallet_id" => $this->payeeWallet->id,
            "amount" => $amount,
            "status" => "completed",
        ]);

        // Assert notifications were attempted using the mocked service
        // We need to get the instance from the container to check mock expectations
        $notificationServiceMock = $this->app->make(NotificationService::class);
        $notificationServiceMock->shouldHaveReceived('send')->twice();

        // Or assert using Laravel's Notification facade if NotificationService dispatches notifications
        // Notification::assertSentTo($this->payer, TransferNotification::class);
        // Notification::assertSentTo($this->payee, TransferNotification::class);
    }

    /** @test */
    public function a_shopkeeper_cannot_transfer_money()
    {
        $response = $this->actingAs($this->shopkeeper, "sanctum")
                         ->postJson("/api/transfers", [
                             "payee_id" => $this->payer->id,
                             "amount" => 50.00,
                         ]);

        // Expecting 403 Forbidden because the StoreTransferRequest authorize method checks user type
        $response->assertStatus(403);
        $this->assertDatabaseHas("wallets", ["id" => $this->shopkeeperWallet->id, "balance" => 200.00]); // Balance unchanged
    }

    /** @test */
    public function transfer_fails_due_to_insufficient_balance()
    {
        $amount = 150.00; // More than payer's balance

        $response = $this->actingAs($this->payer, "sanctum")
                         ->postJson("/api/transfers", [
                             "payee_id" => $this->payee->id,
                             "amount" => $amount,
                         ]);

        $response->assertStatus(400) // Expect 400
                 ->assertJson(["message" => "Saldo insuficiente para realizar a transferência."]);

        $this->assertDatabaseHas("wallets", ["id" => $this->payerWallet->id, "balance" => 100.00]); // Balance unchanged
        $this->assertDatabaseHas("wallets", ["id" => $this->payeeWallet->id, "balance" => 50.00]); // Balance unchanged
        $this->assertDatabaseMissing("transactions", ["payer_wallet_id" => $this->payerWallet->id]);
    }

    /** @test */
    public function transfer_fails_if_authorization_service_denies()
    {
        // Override AuthorizationService mock for this test
        $this->mock(AuthorizationService::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(false); // Denied
        });

        $amount = 50.00;

        $response = $this->actingAs($this->payer, "sanctum")
                         ->postJson("/api/transfers", [
                             "payee_id" => $this->payee->id,
                             "amount" => $amount,
                         ]);

        $response->assertStatus(403) // Expect 403
                 ->assertJson(["message" => "Transferência não autorizada pelo serviço externo."]);

        $this->assertDatabaseHas("wallets", ["id" => $this->payerWallet->id, "balance" => 100.00]); // Balance unchanged
        $this->assertDatabaseHas("wallets", ["id" => $this->payeeWallet->id, "balance" => 50.00]); // Balance unchanged

        // Check for failed transaction record
        $this->assertDatabaseHas("transactions", [
            "payer_wallet_id" => $this->payerWallet->id,
            "payee_wallet_id" => $this->payeeWallet->id,
            "amount" => $amount,
            "status" => "failed",
        ]);

        // Assert notification service was NOT called
         $notificationServiceMock = $this->app->make(NotificationService::class);
         $notificationServiceMock->shouldNotHaveReceived('send');
    }

    /** @test */
    public function transfer_fails_if_payee_does_not_exist()
    {
        $nonExistentPayeeId = 9999;

        $response = $this->actingAs($this->payer, "sanctum")
                         ->postJson("/api/transfers", [
                             "payee_id" => $nonExistentPayeeId,
                             "amount" => 50.00,
                         ]);

        $response->assertStatus(422) // Expect 422 Validation error
                 ->assertJsonValidationErrors(["payee_id"]);
    }
}
