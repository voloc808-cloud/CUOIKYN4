<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('alert_settings', function (Blueprint $table): void { $table->id(); $table->unsignedInteger('expiry_warning_days')->default(30); $table->timestamps(); });
        DB::table('alert_settings')->insert(['expiry_warning_days' => 30, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()]);
    }
    public function down(): void { Schema::dropIfExists('alert_settings'); }
};
