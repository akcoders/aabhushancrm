<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privilege_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('tier')->default('Gold')->index();
            $table->decimal('amount', 14, 2);
            $table->date('issued_at');
            $table->date('expires_at')->nullable()->index();
            $table->string('status')->default('Active')->index();
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privilege_cards');
    }
};
