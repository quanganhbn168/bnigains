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
        Schema::table('gains_profiles', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('slug');
            $table->string('bni_position')->nullable()->after('full_name'); // VD: Chủ tịch NK20
            $table->string('chapter_name')->nullable()->after('bni_position'); // VD: BNI Power Chapter - HN2
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gains_profiles', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'bni_position', 'chapter_name']);
        });
    }
};
