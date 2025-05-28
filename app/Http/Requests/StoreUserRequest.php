<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can attempt to register
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'cpf_cnpj' => ['required', 'string', 'max:18', 'unique:users,cpf_cnpj', function ($attribute, $value, $fail) {
                // Basic validation for CPF/CNPJ format (can be improved with a dedicated library)
                $cleaned = preg_replace('/[^0-9]/', '', $value);
                if (strlen($cleaned) !== 11 && strlen($cleaned) !== 14) {
                    $fail('O campo :attribute deve ser um CPF ou CNPJ válido.');
                }
            }],
            'password' => ['required', 'confirmed', Password::defaults()],
            'user_type' => ['required', 'string', Rule::in(['common', 'shopkeeper'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'user_type.in' => 'O tipo de usuário deve ser \'common\' ou \'shopkeeper\'.',
        ];
    }
}
