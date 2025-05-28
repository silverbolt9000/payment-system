<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the authenticated user is a common user
        $user = $this->user();
        return $user && $user->isCommon();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payee_id' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    // Ensure payee is not the same as the payer
                    if ($value == $this->user()->id) {
                        $fail('Você não pode transferir dinheiro para si mesmo.');
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'min:0.01'], // Minimum transfer amount
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
            'payee_id.exists' => 'O destinatário informado não existe ou é inválido.',
            'amount.min' => 'O valor da transferência deve ser de pelo menos R$ 0,01.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            // Ensure amount is treated as a number
            'amount' => filter_var($this->amount, FILTER_VALIDATE_FLOAT),
        ]);
    }
}
