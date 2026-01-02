<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GoldPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $goldPackages = [
            [
                'name' => 'Starter Pack',
                'gold_amount' => 1000,
                'price' => 100000, // IDR 100,000
                'description' => 'Perfect for beginners',
            ],
            [
                'name' => 'Bronze Pack',
                'gold_amount' => 2000,
                'price' => 200000, // IDR 200,000
                'description' => 'Great value package',
            ],
            [
                'name' => 'Silver Pack',
                'gold_amount' => 5000,
                'price' => 500000, // IDR 500,000
                'description' => 'Ideal for regular players',
            ],
            [
                'name' => 'Gold Pack',
                'gold_amount' => 10000,
                'price' => 1000000, // IDR 1,000,000
                'description' => 'For serious adventurers',
            ],
            [
                'name' => 'Platinum Pack',
                'gold_amount' => 25000,
                'price' => 2500000, // IDR 2,500,000
                'description' => 'Premium experience',
            ],
            [
                'name' => 'Diamond Pack',
                'gold_amount' => 50000,
                'price' => 5000000, // IDR 5,000,000
                'description' => 'Ultimate package',
            ],
        ];

        // Store in config for easy access
        $configPath = config_path('gold_packages.php');
        $content = "<?php\n\nreturn " . var_export($goldPackages, true) . ";\n";
        file_put_contents($configPath, $content);
    }
}
