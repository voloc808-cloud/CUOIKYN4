<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('disposal_details', function (Blueprint $table): void {
            $table->id(); $table->foreignId('disposal_receipt_id')->constrained('disposal_receipts')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches'); $table->decimal('quantity', 18, 3); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('disposal_details'); }
};
