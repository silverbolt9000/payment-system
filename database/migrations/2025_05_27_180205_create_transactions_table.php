<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("transactions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("payer_wallet_id")->constrained("wallets");
            $table->foreignId("payee_wallet_id")->constrained("wallets");
            $table->decimal("amount", 10, 2);
            $table->enum("status", ["pending", "completed", "failed", "reversed"])->default("pending");
            $table->string("authorization_code")->nullable(); // Store code from external service
            $table->timestamp("authorized_at")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("transactions");
    }
};
