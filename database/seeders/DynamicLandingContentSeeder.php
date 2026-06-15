<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DynamicLandingContentSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('public_html/data/cms-content.json');
        if (!is_file($path)) {
            return;
        }
        $content = json_decode((string) file_get_contents($path), true) ?: [];
        $map = [
            'hero' => 'home_sections', 'challenges' => 'section_items', 'learning_path' => 'section_items',
            'skills' => 'section_items', 'portfolios' => 'portfolios', 'portfolio_categories' => 'portfolio_categories',
            'instructor' => 'instructors', 'courses' => 'courses', 'course_features' => 'course_features',
            'course_curriculums' => 'course_curriculums', 'course_results' => 'course_results',
            'testimonials' => 'testimonials', 'faqs' => 'faqs', 'ctas' => 'ctas', 'registrations' => 'registrations',
            'consultations' => 'consultation_requests', 'media' => 'media', 'seo' => 'seo_settings',
            'menus' => 'menus', 'sections' => 'home_sections', 'users' => 'users',
        ];
        foreach ($map as $key => $table) {
            foreach (($content[$key] ?? []) as $item) {
                DB::table($table)->insert([
                    'payload' => json_encode(array_merge(['section_key' => $key], $item), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'sort_order' => (int) ($item['sort_order'] ?? 0),
                    'status' => (string) ($item['status'] ?? 'active'),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }
}
