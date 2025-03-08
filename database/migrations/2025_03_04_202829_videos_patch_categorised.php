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

        Schema::table('videos', function (Blueprint $table) {
            // add after category_id column
            $table->boolean('categorised')->default(false)->index()->after('category_id');
            $table->timestamp('categorised_at')->nullable()->after('categorised');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('categorised');
            $table->dropColumn('categorised_at');
        });
    }
};
