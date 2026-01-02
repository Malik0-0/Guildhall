<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Website building, web applications, and online platforms',
                'color' => '#3b82f6', // blue
                'icon' => 'globe',
            ],
            [
                'name' => 'Mobile Development',
                'slug' => 'mobile-development',
                'description' => 'iOS and Android app development',
                'color' => '#10b981', // emerald
                'icon' => 'device-mobile',
            ],
            [
                'name' => 'Design & UI/UX',
                'slug' => 'design-ui-ux',
                'description' => 'Graphic design, user interface, and user experience',
                'color' => '#f59e0b', // amber
                'icon' => 'palette',
            ],
            [
                'name' => 'Content Writing',
                'slug' => 'content-writing',
                'description' => 'Blog posts, articles, copywriting, and content creation',
                'color' => '#8b5cf6', // violet
                'icon' => 'document-text',
            ],
            [
                'name' => 'Marketing & SEO',
                'slug' => 'marketing-seo',
                'description' => 'Digital marketing, SEO, social media management',
                'color' => '#ef4444', // red
                'icon' => 'megaphone',
            ],
            [
                'name' => 'Data & Analytics',
                'slug' => 'data-analytics',
                'description' => 'Data analysis, visualization, and business intelligence',
                'color' => '#06b6d4', // cyan
                'icon' => 'chart-bar',
            ],
            [
                'name' => 'Video & Animation',
                'slug' => 'video-animation',
                'description' => 'Video editing, animation, motion graphics',
                'color' => '#ec4899', // pink
                'icon' => 'video-camera',
            ],
            [
                'name' => 'Administrative',
                'slug' => 'administrative',
                'description' => 'Virtual assistance, data entry, administrative tasks',
                'color' => '#6b7280', // gray
                'icon' => 'clipboard-document',
            ],
        ];

        // Clear existing data to avoid conflicts
        DB::table('quest_category')->delete();
        DB::table('quest_tag')->delete();
        DB::table('categories')->delete();
        DB::table('tags')->delete();

        DB::table('categories')->insert($categories);

        // Create some popular tags
        $tags = [
            'urgent', 'long-term', 'beginner-friendly', 'expert-level',
            'remote', 'onsite', 'flexible-hours', 'full-time',
            'react', 'vue', 'laravel', 'wordpress', 'shopify',
            'logo-design', 'branding', 'social-media', 'email-marketing',
            'research', 'translation', 'proofreading', 'editing'
        ];

        foreach ($tags as $tagName) {
            DB::table('tags')->insert([
                'name' => $tagName,
                'slug' => str_replace(' ', '-', $tagName),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
