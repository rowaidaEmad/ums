<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->unsignedBigInteger('entity_id'); // points to id in the entity table
            $table->string('value_string')->nullable();
            $table->text('value_text')->nullable();
            $table->integer('value_int')->nullable();
            $table->boolean('value_bool')->nullable();
            $table->date('value_date')->nullable();
            $table->timestamps();

            $table->index(['attribute_id','entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
