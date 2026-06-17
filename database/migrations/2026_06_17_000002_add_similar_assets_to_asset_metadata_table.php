<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_metadata', function (Blueprint $table) {
            $table->json('similar_assets')->nullable()->after('ai_generated');
        });
    }

    public function down(): void
    {
        Schema::table('asset_metadata', function (Blueprint $table) {
            $table->dropColumn('similar_assets');
        });
    }
};
