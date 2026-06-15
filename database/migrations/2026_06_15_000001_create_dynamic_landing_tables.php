<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['site_settings','menus','home_sections','section_items','portfolio_categories','portfolios','portfolio_images','courses','course_features','course_curriculums','course_results','instructors','testimonials','faqs','ctas','registrations','consultation_requests','media','seo_settings'] as $table) {
            if (!Schema::hasTable($table)) {
                Schema::create($table, function (Blueprint $table) {
                    $table->id();
                    $table->json('payload')->nullable();
                    $table->unsignedInteger('sort_order')->default(0)->index();
                    $table->string('status', 40)->default('active')->index();
                    $table->timestamps();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse(['site_settings','menus','home_sections','section_items','portfolio_categories','portfolios','portfolio_images','courses','course_features','course_curriculums','course_results','instructors','testimonials','faqs','ctas','registrations','consultation_requests','media','seo_settings']) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
