<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('disposal_receipts', function (Blueprint $table): void {
            $table->id(); $table->string('receipt_code', 50)->unique(); $table->dateTime('disposed_at');
            $table->text('reason'); $table->unsignedBigInteger('created_by'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('disposal_receipts'); }
};
