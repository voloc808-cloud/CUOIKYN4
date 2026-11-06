<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('prescription_code', 50)->unique();
            $table->unsignedBigInteger('doctor_id');
            $table->string('patient_name', 150);
            $table->string('patient_code', 50)->nullable();
            $table->text('diagnosis')->nullable();
            $table->string('status', 20)->default('NEW');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('dispensed_at')->nullable();
            $table->timestamps();
            $table->index(['doctor_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('prescriptions'); }
};
