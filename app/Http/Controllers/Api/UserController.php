<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'cpf_cnpj' => preg_replace('/[^0-9]/', '', $validatedData['cpf_cnpj']), // Store only numbers
                'user_type' => $validatedData['user_type'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Create a wallet for the new user
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0, // Initial balance
            ]);

            DB::commit();

            // Optionally return the user data without sensitive info
            $user->load('wallet'); // Load the wallet relationship
            return response()->json($user->makeHidden(['password', 'remember_token']), 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao criar usuário. Tente novamente mais tarde.'], 500);
        }
    }
}
