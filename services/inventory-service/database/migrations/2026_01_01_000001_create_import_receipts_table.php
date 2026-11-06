<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_receipts', function (Blueprint $table): void {
            $table->id();
            $table->string('receipt_code', 50)->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->dateTime('imported_at');
            $table->string('invoice_number', 100)->nullable();
            $table->string('note', 500)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('import_receipts'); }
};
