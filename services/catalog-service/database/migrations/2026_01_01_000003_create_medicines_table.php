<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('base_unit_id')->constrained('units');
            $table->string('code', 50)->unique();
            $table->string('name', 180);
            $table->string('active_ingredient', 250);
            $table->string('strength', 100);
            $table->string('dosage_form', 120)->nullable();
            $table->decimal('minimum_stock', 18, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
