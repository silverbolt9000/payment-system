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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf_cnpj')->unique()->after('email');
            $table->enum('user_type', ['common', 'shopkeeper'])->after('cpf_cnpj');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cpf_cnpj');
            $table->dropColumn('user_type');
            $table->dropSoftDeletes();
        });
    }
};
