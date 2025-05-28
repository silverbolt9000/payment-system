<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_has_one_wallet()
    {
        $user = User::factory()->create();
        Wallet::factory()->create(["user_id" => $user->id]);

        $this->assertInstanceOf(Wallet::class, $user->wallet);
    }

    /** @test */
    public function is_shopkeeper_returns_true_for_shopkeeper_user()
    {
        $user = User::factory()->create(["user_type" => "shopkeeper"]);
        $this->assertTrue($user->isShopkeeper());
        $this->assertFalse($user->isCommon());
    }

    /** @test */
    public function is_common_returns_true_for_common_user()
    {
        $user = User::factory()->create(["user_type" => "common"]);
        $this->assertTrue($user->isCommon());
        $this->assertFalse($user->isShopkeeper());
    }
}
