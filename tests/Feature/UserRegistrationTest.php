<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function a_common_user_can_register_successfully()
    {
        $password = "password123";
        $userData = [
            "name" => $this->faker->name,
            "email" => $this->faker->unique()->safeEmail,
            "cpf_cnpj" => $this->faker->numerify("###########"), // 11 digits for CPF
            "password" => $password,
            "password_confirmation" => $password,
            "user_type" => "common",
        ];

        $response = $this->postJson("/api/users", $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     "id",
                     "name",
                     "email",
                     "cpf_cnpj",
                     "user_type",
                     "created_at",
                     "updated_at",
                     "wallet" => [
                         "id",
                         "user_id",
                         "balance",
                         "created_at",
                         "updated_at",
                     ]
                 ])
                 ->assertJsonFragment(["email" => $userData["email"], "user_type" => "common"]);

        $this->assertDatabaseHas("users", [
            "email" => $userData["email"],
            "cpf_cnpj" => $userData["cpf_cnpj"],
            "user_type" => "common",
        ]);

        $user = User::whereEmail($userData["email"])->first();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertDatabaseHas("wallets", [
            "user_id" => $user->id,
            "balance" => 0,
        ]);
    }

    /** @test */
    public function a_shopkeeper_user_can_register_successfully()
    {
        $password = "password123";
        $userData = [
            "name" => $this->faker->company,
            "email" => $this->faker->unique()->safeEmail,
            "cpf_cnpj" => $this->faker->numerify("##############"), // 14 digits for CNPJ
            "password" => $password,
            "password_confirmation" => $password,
            "user_type" => "shopkeeper",
        ];

        $response = $this->postJson("/api/users", $userData);

        $response->assertStatus(201)
                 ->assertJsonFragment(["email" => $userData["email"], "user_type" => "shopkeeper"]);

        $this->assertDatabaseHas("users", [
            "email" => $userData["email"],
            "cpf_cnpj" => $userData["cpf_cnpj"],
            "user_type" => "shopkeeper",
        ]);
        $user = User::whereEmail($userData["email"])->first();
        $this->assertDatabaseHas("wallets", ["user_id" => $user->id]);
    }

    /** @test */
    public function registration_fails_with_validation_errors()
    {
        $response = $this->postJson("/api/users", []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["name", "email", "cpf_cnpj", "password", "user_type"]);
    }

    /** @test */
    public function registration_fails_with_duplicate_email()
    {
        $existingUser = User::factory()->create();
        $password = "password123";
        $userData = [
            "name" => $this->faker->name,
            "email" => $existingUser->email, // Duplicate email
            "cpf_cnpj" => $this->faker->numerify("###########"),
            "password" => $password,
            "password_confirmation" => $password,
            "user_type" => "common",
        ];

        $response = $this->postJson("/api/users", $userData);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["email"]);
    }

     /** @test */
    public function registration_fails_with_duplicate_cpf_cnpj()
    {
        $existingUser = User::factory()->create();
        $password = "password123";
        $userData = [
            "name" => $this->faker->name,
            "email" => $this->faker->unique()->safeEmail,
            "cpf_cnpj" => $existingUser->cpf_cnpj, // Duplicate cpf_cnpj
            "password" => $password,
            "password_confirmation" => $password,
            "user_type" => "common",
        ];

        $response = $this->postJson("/api/users", $userData);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["cpf_cnpj"]);
    }
}
