<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Demo data may only be installed in a local or testing environment.');
        }

        DB::transaction(function (): void {
            $groups = [
                ['audio', 'Audio', 'Headphones, speakers and earbuds.', ['headphones' => 'Headphones', 'earbuds' => 'Earbuds']],
                ['wearables', 'Wearables', 'Watches and everyday activity accessories.', ['smartwatches' => 'Smartwatches', 'fitness' => 'Fitness trackers']],
                ['computers', 'Computers', 'Portable computers and desk essentials.', ['laptops' => 'Laptops', 'keyboards' => 'Keyboards']],
                ['mobiles', 'Mobiles', 'Smartphones and portable power.', ['smartphones' => 'Smartphones', 'charging' => 'Charging']],
                ['accessories', 'Accessories', 'Useful additions for your setup.', ['mice' => 'Mice', 'desk-accessories' => 'Desk accessories']],
            ];
            $subcategories = [];

            foreach ($groups as [$slug, $name, $description, $children]) {
                $category = Category::firstOrCreate(['slug' => $slug], compact('name', 'description'));

                foreach ($children as $childSlug => $childName) {
                    $subcategories[$childSlug] = Subcategory::firstOrCreate(
                        ['slug' => $childSlug],
                        ['category_id' => $category->id, 'name' => $childName],
                    );
                }
            }

            $catalogue = [
                ['desk-charging-dock', 'Desk Charging Dock', 'desk-accessories', 'charger', null, '1890.00', 'A small desktop charging dock that keeps your everyday devices in one place.'],
                ['focus-lite-band', 'Focus Lite Band', 'fitness', 'watch', '3490.00', '2990.00', 'A lightweight activity band with a simple display and a soft adjustable strap.'],
                ['quiet-touch-keyboard', 'Quiet Touch Keyboard', 'keyboards', 'keyboard', null, '3200.00', 'A compact keyboard with low-profile keys for shared workspaces and home desks.'],
                ['pocket-power-10000', 'Pocket Power 10,000', 'charging', 'charger', '2590.00', '2190.00', 'A compact power bank for topping up your phone while travelling.'],
                ['studio-pocket-earbuds', 'Studio Pocket Earbuds', 'earbuds', 'earbuds', '4300.00', '3590.00', 'Small wireless earbuds with a charging case that fits easily in a pocket.'],
                ['arc-wireless-mouse', 'Arc Wireless Mouse', 'mice', 'mouse', '1990.00', '1490.00', 'A smooth wireless mouse with a compact shape and quiet buttons for everyday use.'],
                ['wireless-travel-headphones', 'Wireless Travel Headphones', 'headphones', 'headphones', null, '4990.00', 'Foldable over-ear headphones with padded ear cups for commuting and travel.'],
                ['slim-phone-x', 'Slim Phone X', 'smartphones', 'phone', '34990.00', '30990.00', 'A slim smartphone with an edge-to-edge display and a comfortable matte finish.'],
                ['studio-laptop-14', 'Studio Laptop 14″', 'laptops', 'laptop', '74990.00', '68990.00', 'A compact 14-inch laptop for study, work and daily multitasking.'],
                ['pulse-smart-watch', 'Pulse Smart Watch', 'smartwatches', 'watch', '6490.00', '5490.00', 'A rounded smartwatch with easy-to-read activity information and an adjustable silicone strap.'],
                ['wireless-studio-headphones', 'Wireless Studio Headphones', 'headphones', 'headphones', '8990.00', '7290.00', 'Comfortable over-ear headphones with wireless listening, cushioned ear cups and an adjustable headband.'],
            ];

            foreach ($catalogue as [$slug, $name, $group, $image, $oldPrice, $newPrice, $description]) {
                $subcategory = $subcategories[$group];

                Product::firstOrCreate(['slug' => $slug], [
                    'name' => $name,
                    'category_id' => $subcategory->category_id,
                    'subcategory_id' => $subcategory->id,
                    'image' => 'demo/'.$image.'.webp',
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'description' => $description,
                    'is_active' => true,
                ]);
            }
        });
    }
}
