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
        Schema::create('gains_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('slug')->unique();
            $table->string('theme_color')->default('beige'); // Cho phép chọn theme màu
            
            // Định danh & Liên hệ
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('business_category')->nullable();
            $table->string('phone_cv')->nullable();
            $table->string('phone_personal')->nullable();
            $table->string('email_cv')->nullable();
            $table->string('email_personal')->nullable();
            $table->json('social_links')->nullable();
            
            // Tiểu sử & Cá nhân
            $table->string('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->text('family_info')->nullable();
            $table->text('burning_desire')->nullable();
            $table->text('unknown_fact')->nullable();
            $table->text('success_key')->nullable();
            
            // Doanh nghiệp bổ sung
            $table->string('experience_years')->nullable();
            $table->text('qualifications')->nullable();
            $table->text('core_products')->nullable();
            $table->text('accompanying_services')->nullable();
            $table->text('highlight_products')->nullable();
            
            // Bảng GAINS
            $table->text('g_goals')->nullable();
            $table->text('a_accomplishments')->nullable();
            $table->text('i_interests')->nullable();
            $table->text('n_networks')->nullable();
            $table->text('s_skills')->nullable();
            
            // Bí kíp / Hợp tác MQH (Referral)
            $table->text('ideal_referral')->nullable();
            $table->text('connection_wishes')->nullable();
            $table->text('bni_commitment')->nullable();
            $table->text('product_description')->nullable();
            $table->text('competitive_advantage')->nullable();
            $table->text('target_market')->nullable();
            $table->text('connection_fields')->nullable();
            $table->text('conversation_starters')->nullable();
            $table->text('trigger_phrases')->nullable();
            $table->text('good_referral')->nullable();
            $table->text('bad_referral')->nullable();
            $table->text('misconceptions')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gains_profiles');
    }
};
