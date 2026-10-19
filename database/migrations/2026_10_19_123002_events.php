<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->engine= 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('sport_id');
            $table->string('created_by');
            $table->string('title');
            $table->text('description');
            $table->date('starts_at');
            $table->integer('duration_minute');
            $table->string('city');
            $table->string('venue');
            $table->integer('capacity');
            $table->char('public_token', 32)->unique();
            $table->enum('status');
            $table->timestamps();

            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_user')->constrained()->onDelete('cascade');
            $table->foreignId('sports')->constrained()->onDelete('cascade');
        });

        Schema::table('events', function (Blueprint $table) {
            \DB::statement('ALTER TABLE events ADD CONSTRAINT chk_capacity CHECK (capacity BETWEEN 2 AND 100)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
