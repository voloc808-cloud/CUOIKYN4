<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_receipt_detail_id')->constrained('import_receipt_details');
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('unit_id');
            $table->string('batch_code', 80);
            $table->date('manufactured_at');
            $table->date('expires_at');
            $table->decimal('quantity_imported', 18, 3);
            $table->decimal('quantity_remaining', 18, 3);
            $table->timestamps();
            $table->unique(['medicine_id', 'batch_code']);
            $table->index(['medicine_id', 'expires_at', 'quantity_remaining']);
        });
    }

    public function down(): void { Schema::dropIfExists('batches'); }
};
