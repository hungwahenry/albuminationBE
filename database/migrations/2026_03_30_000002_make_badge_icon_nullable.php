<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Icon name strings ('fire', 'pen', etc.) are legacy placeholders and must
     * be removed. The icon column becomes nullable so records without an uploaded
     * image are valid at the DB level; enforcement of a real uploaded image is
     * handled by the Filament form (FileUpload required()) at creation time.
     */
    public function up(): void
    {
        DB::table('badges')
            ->whereNotNull('icon')
            ->where('icon', 'not like', 'badges/%')
            ->update(['icon' => null]);

        Schema::table('badges', function (Blueprint $table) {
            $table->string('icon')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('icon')->nullable(false)->default('')->change();
        });
    }
};
