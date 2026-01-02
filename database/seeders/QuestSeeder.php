<?php

namespace Database\Seeders;

use App\Models\Quest;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create some patron users (quest givers)
        $patrons = [];
        for ($i = 1; $i <= 5; $i++) {
            $patrons[] = User::firstOrCreate(
                ['email' => "patron{$i}@example.com"],
                [
                    'name' => "Patron {$i}",
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_QUEST_GIVER,
                    'gold' => 10000, // Give them enough gold to create quests
                    'xp' => 0,
                    'level' => 1,
                ]
            );
        }

        // Get categories and tags
        $categories = Category::all();
        $tags = Tag::all();

        // Sample quest data
        $quests = [
            [
                'title' => 'Build a Modern E-commerce Website',
                'description' => 'I need a fully functional e-commerce website with shopping cart, payment integration, and admin dashboard. The site should be responsive and user-friendly. Looking for someone with experience in Laravel and Vue.js.',
                'price' => 5000,
            ],
            [
                'title' => 'Design a Professional Logo for My Startup',
                'description' => 'Looking for a creative designer to create a modern and memorable logo for my tech startup. The logo should work well in both digital and print formats. I have some initial ideas but open to creative suggestions.',
                'price' => 1500,
            ],
            [
                'title' => 'Develop a Mobile App for Task Management',
                'description' => 'Need an iOS and Android app for task management with features like reminders, categories, and team collaboration. Should have a clean, intuitive interface. Experience with React Native or Flutter preferred.',
                'price' => 8000,
            ],
            [
                'title' => 'Write 10 SEO-Optimized Blog Posts',
                'description' => 'I need 10 high-quality blog posts (1000+ words each) on topics related to digital marketing. Content should be SEO-optimized, engaging, and well-researched. Native English speakers preferred.',
                'price' => 2000,
            ],
            [
                'title' => 'Create Social Media Content Strategy',
                'description' => 'Looking for a social media expert to develop a comprehensive content strategy for Instagram and LinkedIn. Should include content calendar, post ideas, and engagement tactics. Experience with B2B marketing is a plus.',
                'price' => 3000,
            ],
            [
                'title' => 'Build a Data Dashboard with Analytics',
                'description' => 'Need a custom analytics dashboard that visualizes sales data, user metrics, and KPIs. Should integrate with our existing database and provide real-time updates. Experience with Python, SQL, and data visualization tools required.',
                'price' => 6000,
            ],
            [
                'title' => 'Edit and Produce a Product Launch Video',
                'description' => 'I have raw footage for a product launch video and need professional editing. Looking for someone skilled in video editing, color grading, and motion graphics. Final video should be 2-3 minutes long.',
                'price' => 2500,
            ],
            [
                'title' => 'Set Up Virtual Assistant Workflow',
                'description' => 'Need help setting up automated workflows for email management, calendar scheduling, and data entry tasks. Should integrate with our CRM system. Experience with automation tools like Zapier or Make.com preferred.',
                'price' => 1800,
            ],
            [
                'title' => 'Redesign Company Website UI/UX',
                'description' => 'Looking for a UI/UX designer to redesign our company website. Current site needs better user experience and modern design. Should create wireframes, mockups, and provide design system. Portfolio required.',
                'price' => 4000,
            ],
            [
                'title' => 'Develop WordPress Plugin for Custom Forms',
                'description' => 'Need a custom WordPress plugin that creates dynamic forms with conditional logic. Plugin should be well-documented and compatible with latest WordPress version. PHP and WordPress development experience required.',
                'price' => 3500,
            ],
            [
                'title' => 'Create Animated Explainer Video',
                'description' => 'Looking for an animator to create a 90-second animated explainer video for our SaaS product. Should include voiceover, music, and professional animation. Style should be modern and engaging.',
                'price' => 4500,
            ],
            [
                'title' => 'Write Technical Documentation',
                'description' => 'Need comprehensive technical documentation for our API. Should include setup guides, code examples, and troubleshooting sections. Experience with API documentation tools like Swagger or Postman preferred.',
                'price' => 2200,
            ],
            [
                'title' => 'Build Landing Page with A/B Testing',
                'description' => 'Need a high-converting landing page with built-in A/B testing functionality. Should be optimized for conversions and mobile-responsive. Experience with conversion rate optimization is a plus.',
                'price' => 2800,
            ],
            [
                'title' => 'Create Email Marketing Campaign',
                'description' => 'Looking for someone to design and set up an email marketing campaign for our product launch. Should include email templates, automation sequences, and analytics tracking. Experience with Mailchimp or SendGrid required.',
                'price' => 1900,
            ],
            [
                'title' => 'Develop REST API Backend',
                'description' => 'Need a robust REST API backend for our mobile app. Should include authentication, data validation, and error handling. Experience with Node.js, Express, and MongoDB preferred. API documentation required.',
                'price' => 7000,
            ],
        ];

        // Create quests
        foreach ($quests as $index => $questData) {
            $patron = $patrons[array_rand($patrons)];
            
            $quest = Quest::create([
                'title' => $questData['title'],
                'description' => $questData['description'],
                'price' => $questData['price'],
                'status' => Quest::STATUS_OPEN,
                'patron_id' => $patron->id,
            ]);

            // Attach 1-3 random categories
            $randomCategories = $categories->random(rand(1, 3));
            $quest->categories()->attach($randomCategories->pluck('id'));

            // Attach 2-5 random tags
            $randomTags = $tags->random(rand(2, 5));
            $quest->tags()->attach($randomTags->pluck('id'));
        }

        $this->command->info('Created ' . count($quests) . ' dummy open quests!');
    }
}

