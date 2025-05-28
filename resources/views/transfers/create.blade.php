@extends('layouts.app')

@section('title', 'Nova Transferência - Sistema de Pagamento')

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <h1 class="text-center text-2xl font-bold text-gray-700 mb-6">Nova Transferência</h1>

                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('transfers.store') }}" x-data="transferForm()">
                    @csrf

                    <div class="mb-4">
                        <label for="payee_id" class="block text-gray-700 text-sm font-bold mb-2">ID do Destinatário</label>
                        <input id="payee_id" type="number" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('payee_id') border-red-500 @enderror" name="payee_id" value="{{ old('payee_id') }}" required>
                        
                        @error('payee_id')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-xs mt-1">Informe o ID numérico do usuário que receberá a transferência.</p>
                    </div>

                    <div class="mb-6">
                        <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">Valor (R$)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">R$</span>
                            </div>
                            <input id="amount" type="text" x-model="amount" x-on:input="formatAmount" class="pl-10 appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('amount') border-red-500 @enderror" name="amount" value="{{ old('amount') }}" required>
                        </div>
                        
                        @error('amount')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Resumo da Transferência</h3>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Valor da Transferência:</span>
                            <span class="font-medium" x-text="'R$ ' + formattedAmount"></span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Saldo Atual:</span>
                            <span class="font-medium">R$ {{ number_format(Auth::user()->wallet->balance ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t mt-2">
                            <span class="text-gray-600">Saldo Após Transferência:</span>
                            <span class="font-medium" x-text="'R$ ' + calculateRemainingBalance()"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('home') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Transferir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function transferForm() {
        return {
            amount: '{{ old('amount') }}',
            formattedAmount: '{{ old('amount') ? number_format(old('amount'), 2, ',', '.') : '0,00' }}',
            
            formatAmount() {
                // Remove non-numeric characters
                let value = this.amount.replace(/\D/g, '');
                
                // Convert to decimal (divide by 100)
                value = (parseInt(value) || 0) / 100;
                
                // Format with 2 decimal places
                this.formattedAmount = value.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                // Update the actual value (for form submission)
                this.amount = value.toFixed(2).toString();
            },
            
            calculateRemainingBalance() {
                const currentBalance = {{ Auth::user()->wallet->balance ?? 0 }};
                const transferAmount = parseFloat(this.amount.replace(',', '.')) || 0;
                const remainingBalance = Math.max(0, currentBalance - transferAmount);
                
                return remainingBalance.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }
    }
</script>
@endsection
