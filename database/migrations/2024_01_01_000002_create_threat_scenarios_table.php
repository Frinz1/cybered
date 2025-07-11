<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threat_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['phishing', 'malware']);
            $table->enum('severity', ['low', 'medium', 'high']);
            $table->json('keywords');
            $table->text('solution');
            $table->json('mitigation_steps');
            $table->text('explanation');
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threat_scenarios');
    }
};
