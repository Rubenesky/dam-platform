<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE assets ADD CONSTRAINT assets_status_check
                CHECK (status IN ('pending', 'processed', 'error'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE assets DROP CONSTRAINT assets_status_check');
        }
    }
};
