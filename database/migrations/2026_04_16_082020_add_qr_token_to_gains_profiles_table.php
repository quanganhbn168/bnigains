<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gains_profiles', function (Blueprint $table) {
            $table->string('qr_token', 36)->nullable()->unique()->after('slug');
        });

        \DB::table('gains_profiles')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->chunkById(200, function ($profiles): void {
                foreach ($profiles as $profile) {
                    \DB::table('gains_profiles')
                        ->where('id', $profile->id)
                        ->update(['qr_token' => (string) Str::uuid()]);
                }
            });

        Schema::table('gains_profiles', function (Blueprint $table) {
            $table->string('qr_token', 36)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gains_profiles', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });
    }
};
