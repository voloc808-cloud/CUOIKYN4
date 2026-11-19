<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches');
            $table->unsignedBigInteger('medicine_id');
            $table->string('movement_type', 40);
            $table->decimal('quantity', 18, 3);
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->unsignedBigInteger('performed_by');
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->index(['medicine_id', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('stock_movements'); }
};
