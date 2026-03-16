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
        Schema::table('posts', function (Blueprint $table) {
            $table->bigInteger('post_serie_id')->nullable()->change();
            $table->dateTime('published_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->bigInteger('post_serie_id')->nullable(false)->change();
            $table->dateTime('published_at')->nullable(false)->change();
        });
    }
};
