<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $duplicates = DB::table('gains_profiles')
                ->select('user_id')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('user_id');

            foreach ($duplicates as $userId) {
                $profileIdsToDetach = DB::table('gains_profiles')
                    ->where('user_id', $userId)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->pluck('id')
                    ->slice(1);

                if ($profileIdsToDetach->isNotEmpty()) {
                    DB::table('gains_profiles')
                        ->whereIn('id', $profileIdsToDetach->all())
                        ->update(['user_id' => null]);
                }
            }
        });

        Schema::table('gains_profiles', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('theme_color');
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gains_profiles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropColumn('is_public');
        });
    }
};
