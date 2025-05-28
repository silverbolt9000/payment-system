<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'balance',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions where this wallet is the payer.
     */
    public function payerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payer_wallet_id');
    }

    /**
     * Get the transactions where this wallet is the payee.
     */
    public function payeeTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payee_wallet_id');
    }
}
