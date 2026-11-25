<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_id')->unique();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('area_name')->nullable();
            $table->string('emblem')->nullable();
            $table->date('season_start')->nullable();
            $table->date('season_end')->nullable();
            $table->timestamp('teams_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
