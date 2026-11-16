<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_receipt_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_receipt_id')->constrained('import_receipts')->cascadeOnDelete();
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('unit_id');
            $table->string('batch_code', 80);
            $table->date('manufactured_at');
            $table->date('expires_at');
            $table->decimal('quantity', 18, 3);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('import_receipt_details'); }
};
