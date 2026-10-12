<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_hr')->default(false);
        });
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->json('translations');
            $table->string('location');
            $table->string('employment_type');
            $table->string('application_email');
            $table->string('status')->default('draft')->index();
            $table->date('closing_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_hr'));
    }
};
