<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('webp_path')->nullable()->after('path');
            $table->string('thumbnail_path')->nullable()->after('webp_path');
            $table->string('og_path')->nullable()->after('thumbnail_path');
            $table->json('responsive_paths')->nullable()->after('og_path');
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['webp_path', 'thumbnail_path', 'og_path', 'responsive_paths']);
        });
    }
};
