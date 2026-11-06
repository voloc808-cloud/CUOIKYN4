<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescription_stock_operations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('prescription_id')->unique();
            $table->string('status', 20);
            $table->text('result_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_stock_operations');
    }
};
