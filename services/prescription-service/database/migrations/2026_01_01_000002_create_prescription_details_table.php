<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescription_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('medicine_id');
            $table->decimal('quantity', 18, 3);
            $table->string('instructions', 500);
            $table->timestamps();
            $table->unique(['prescription_id', 'medicine_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('prescription_details'); }
};
