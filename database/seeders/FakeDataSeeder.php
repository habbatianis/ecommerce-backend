<?php

namespace Database\Seeders;

use App\Helpers\S3StorageHelper;
use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Blog;
use App\Models\BlogTranslation;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\DeliveryPrice;
use App\Models\Gallery;
use App\Models\Language;
use App\Models\Region;
use App\Models\RegionTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\ShopTranslation;
use App\Models\ShopTag;
use App\Models\Stock;
use App\Models\User;
use App\Services\UserServices\UserWalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class FakeDataSeeder extends Seeder
{
    private string $locale = 'en';

    public function run(): void
    {
        $this->locale = data_get(Language::languagesList()->first(), 'locale', 'en');

        $this->command->info('Seeding settings...');
        $this->seedSettings();

        $this->ensureStorageReady();

        $this->command->info('Seeding countries & delivery prices...');
        $this->seedCountries();

        $this->command->info('Seeding categories...');
        $this->seedCategories();

        $this->command->info('Seeding brands...');
        $this->seedBrands();

        $this->command->info('Seeding users...');
        $this->seedUsers();

        $this->command->info('Seeding shops...');
        $this->seedShops();

        $this->command->info('Seeding products...');
        $this->seedProducts();

        $this->command->info('Seeding banners...');
        $this->seedBanners();

        $this->command->info('Seeding blogs...');
        $this->seedBlogs();

        $this->command->info('Done! Database filled with fake data.');
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'title',                  'value' => 'Uzmart Marketplace'],
            ['key' => 'description',            'value' => 'The best multi-vendor ecommerce marketplace'],
            ['key' => 'logo',                   'value' => ''],
            ['key' => 'favicon',                'value' => ''],
            ['key' => 'active',                 'value' => '1'],
            ['key' => 'is_demo',                'value' => '1'],
            ['key' => 'aws',                    'value' => '1'],
            ['key' => 'ui_type',                'value' => '1'],
            ['key' => 'currency',               'value' => 'USD'],
            ['key' => 'currency_symbol',        'value' => '$'],
            ['key' => 'like',                   'value' => '1'],
            ['key' => 'order_auto_approved',    'value' => '1'],
            ['key' => 'delivery_fee',           'value' => '5'],
            ['key' => 'min_amount',             'value' => '1'],
            ['key' => 'tax',                    'value' => '12'],
            ['key' => 'verify_gift',            'value' => '0'],
            ['key' => 'bonus_expired_at',       'value' => '0'],
            ['key' => 'after_payment',          'value' => '0'],
            ['key' => 'seller_fee',             'value' => '10'],
            ['key' => 'referral_active',        'value' => '0'],
            ['key' => 'blog',                   'value' => '1'],
            ['key' => 'recaptcha',              'value' => '0'],
            ['key' => 'by_subscription',        'value' => '0'],
            ['key' => 'footer_text',            'value' => '© 2024 Uzmart Marketplace. All rights reserved.'],
        ];

        foreach ($settings as $item) {
            Settings::updateOrCreate(['key' => $item['key']], ['value' => $item['value']]);
        }

        $logo = $this->downloadImageToStorage($this->imageUrl('uzmart-logo', 400, 400), Gallery::OTHER);
        $favicon = $this->downloadImageToStorage($this->imageUrl('uzmart-favicon', 128, 128), Gallery::OTHER);

        if ($logo) {
            Settings::updateOrCreate(['key' => 'logo'], ['value' => $logo]);
        }

        if ($favicon) {
            Settings::updateOrCreate(['key' => 'favicon'], ['value' => $favicon]);
        }
    }

    private function seedCategories(): void
    {
        $mainCategories = [
            ['name' => 'Electronics',       'keywords' => 'electronics,gadgets,tech'],
            ['name' => 'Fashion',           'keywords' => 'clothing,fashion,apparel'],
            ['name' => 'Home & Garden',     'keywords' => 'home,garden,furniture'],
            ['name' => 'Sports & Outdoors', 'keywords' => 'sports,outdoors,fitness'],
            ['name' => 'Beauty & Health',   'keywords' => 'beauty,health,cosmetics'],
            ['name' => 'Food & Groceries',  'keywords' => 'food,grocery,supermarket'],
            ['name' => 'Books & Media',     'keywords' => 'books,media,education'],
            ['name' => 'Toys & Games',      'keywords' => 'toys,games,children'],
            ['name' => 'Automotive',        'keywords' => 'cars,automotive,parts'],
            ['name' => 'Pet Supplies',      'keywords' => 'pets,animals,supplies'],
        ];

        $subCategories = [
            'Electronics'       => ['Smartphones', 'Laptops', 'Headphones', 'Cameras', 'Tablets'],
            'Fashion'           => ["Men's Clothing", "Women's Clothing", 'Shoes', 'Accessories', 'Bags'],
            'Home & Garden'     => ['Furniture', 'Kitchen', 'Bedding', 'Garden Tools', 'Lighting'],
            'Sports & Outdoors' => ['Running', 'Cycling', 'Swimming', 'Gym Equipment', 'Hiking'],
            'Beauty & Health'   => ['Skincare', 'Makeup', 'Hair Care', 'Vitamins', 'Perfume'],
            'Food & Groceries'  => ['Fruits & Vegetables', 'Dairy', 'Bakery', 'Beverages', 'Snacks'],
            'Books & Media'     => ['Fiction', 'Non-Fiction', 'Children Books', 'Music', 'Movies'],
            'Toys & Games'      => ['Action Figures', 'Board Games', 'Puzzles', 'Outdoor Toys', 'Educational'],
            'Automotive'        => ['Car Accessories', 'Tires', 'Engine Parts', 'Car Care', 'GPS'],
            'Pet Supplies'      => ['Dog Food', 'Cat Food', 'Pet Toys', 'Pet Care', 'Aquarium'],
        ];

        foreach ($mainCategories as $mainCat) {
            if (Category::whereHas('translations', fn($q) => $q->where('title', $mainCat['name']))->exists()) {
                continue;
            }

            $category = Category::create([
                'keywords' => $mainCat['keywords'],
                'type'     => Category::MAIN,
                'active'   => true,
                'status'   => 'active',
                'slug'     => Str::slug($mainCat['name']),
            ]);

            CategoryTranslation::create([
                'category_id' => $category->id,
                'locale'      => $this->locale,
                'title'       => $mainCat['name'],
            ]);

            $this->attachModelImages(
                $category,
                $this->imageUrl('category-' . Str::slug($mainCat['name']), 600, 600),
                Gallery::CATEGORIES
            );

            foreach ($subCategories[$mainCat['name']] as $subName) {
                $sub = Category::create([
                    'keywords'  => Str::slug($subName),
                    'type'      => Category::SUB_MAIN,
                    'parent_id' => $category->id,
                    'active'    => true,
                    'status'    => 'active',
                    'slug'      => Str::slug($subName . '-' . rand(100, 999)),
                ]);

                CategoryTranslation::create([
                    'category_id' => $sub->id,
                    'locale'      => $this->locale,
                    'title'       => $subName,
                ]);

                $this->attachModelImages(
                    $sub,
                    $this->imageUrl('subcategory-' . Str::slug($subName), 500, 500),
                    Gallery::CATEGORIES
                );
            }
        }

        Category::whereDoesntHave('galleries')->each(function (Category $category, int $index) {
            $title = $category->translation?->title ?? 'category-' . $category->id;
            $this->attachModelImages(
                $category,
                $this->imageUrl('category-existing-' . Str::slug($title) . '-' . $index, 600, 600),
                Gallery::CATEGORIES
            );
        });
    }

    private function seedBrands(): void
    {
        $brands = [
            'Samsung', 'Apple', 'Nike', 'Adidas', 'Sony',
            'LG', 'Dell', 'HP', 'Lenovo', 'Asus',
            'Gucci', 'Zara', 'H&M', 'IKEA', 'Philips',
            'Puma', 'Reebok', "Levi's", 'Calvin Klein', 'Armani',
        ];

        foreach ($brands as $brandName) {
            $brand = Brand::where('title', $brandName)->first();

            if (!$brand) {
                $brand = Brand::create([
                    'title'  => $brandName,
                    'slug'   => Str::slug($brandName),
                    'active' => true,
                ]);
            }

            if (!$brand->galleries()->exists()) {
                $this->attachModelImages(
                    $brand,
                    $this->imageUrl('brand-' . Str::slug($brandName), 400, 400),
                    Gallery::BRANDS
                );
            }
        }
    }

    private function seedUsers(): void
    {
        $firstNames = ['Alice', 'Bob', 'Carol', 'David', 'Emma', 'Frank', 'Grace', 'Henry', 'Ivy', 'Jack',
                       'Kate', 'Liam', 'Mia', 'Noah', 'Olivia', 'Peter', 'Quinn', 'Rachel', 'Sam', 'Tina',
                       'Uma', 'Victor', 'Wendy', 'Xander', 'Yara', 'Zoe', 'Aaron', 'Beth', 'Chris', 'Diana'];
        $lastNames  = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
                       'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris',
                       'Martin', 'Thompson', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King',
                       'Wright', 'Scott', 'Green', 'Baker', 'Adams'];

        for ($i = 0; $i < 30; $i++) {
            try {
                $first = $firstNames[$i];
                $last  = $lastNames[$i];
                $email = strtolower($first . '.' . $last . rand(1, 99) . '@example.com');

                if (User::where('email', $email)->exists()) {
                    continue;
                }

                $user = User::create([
                    'uuid'               => Str::uuid(),
                    'firstname'          => $first,
                    'lastname'           => $last,
                    'email'              => $email,
                    'phone'              => '+1' . rand(2000000000, 9999999999),
                    'birthday'           => now()->subYears(rand(18, 50))->format('Y-m-d'),
                    'gender'             => $i % 2 === 0 ? 'male' : 'female',
                    'email_verified_at'  => now(),
                    'active'             => true,
                    'password'           => bcrypt('password'),
                ]);

                $user->syncRoles('user');
                (new UserWalletService)->create($user);
            } catch (Throwable $e) {
                // Skip duplicate
            }
        }
    }

    private function seedShops(): void
    {
        $shopData = [
            ['name' => 'TechZone Store',    'desc' => 'Your one-stop shop for all electronics and gadgets.'],
            ['name' => 'FashionHub',        'desc' => 'Trendy clothing and accessories for everyone.'],
            ['name' => 'HomeNest',          'desc' => 'Everything you need for your home and garden.'],
            ['name' => 'SportsPeak',        'desc' => 'Premium sports gear and outdoor equipment.'],
            ['name' => 'BeautyGlow',        'desc' => 'Top beauty and health products at great prices.'],
        ];

        $sellerIds = User::role('seller')->pluck('id')->toArray();

        if (empty($sellerIds)) {
            return;
        }

        foreach ($shopData as $index => $data) {
            if (Shop::whereHas('translations', fn($q) => $q->where('title', $data['name']))->exists()) {
                continue;
            }

            try {
                $sellerId = $sellerIds[$index % count($sellerIds)];

                $shop = Shop::create([
                    'uuid'           => Str::uuid(),
                    'user_id'        => $sellerId,
                    'slug'           => Str::slug($data['name']),
                    'tax'            => rand(5, 15),
                    'percentage'     => rand(5, 20),
                    'lat_long'       => ['latitude' => rand(-90, 90) . '.0', 'longitude' => rand(-180, 180) . '.0'],
                    'phone'          => '+1' . rand(2000000000, 9999999999),
                    'open'           => true,
                    'visibility'     => true,
                    'min_amount'     => rand(5, 50),
                    'status'         => 'approved',
                    'status_note'    => 'approved',
                    'delivery_time'  => ['from' => '15', 'to' => '60', 'type' => 'minute'],
                    'type'           => 1,
                ]);

                ShopTranslation::create([
                    'shop_id'     => $shop->id,
                    'locale'      => $this->locale,
                    'title'       => $data['name'],
                    'description' => $data['desc'],
                    'address'     => rand(100, 999) . ' Main Street, City',
                ]);

                $shop->tags()->sync(ShopTag::inRandomOrder()->limit(3)->pluck('id')->toArray());

                $this->attachShopImages($shop, $data['name'], $index);
            } catch (Throwable $e) {
                // Skip errors
            }
        }

        Shop::whereDoesntHave('galleries')->each(function (Shop $shop, int $index) {
            $title = $shop->translation?->title ?? 'shop-' . $shop->id;
            $this->attachShopImages($shop, $title, $index);
        });
    }

    private function seedProducts(): void
    {
        $productData = [
            // Electronics
            ['name' => 'iPhone 15 Pro Max',         'desc' => 'Latest Apple flagship smartphone with titanium design.',         'price' => 1199, 'cat' => 'Smartphones'],
            ['name' => 'Samsung Galaxy S24 Ultra',  'desc' => 'Samsung flagship with S Pen and 200MP camera.',                  'price' => 1099, 'cat' => 'Smartphones'],
            ['name' => 'MacBook Pro 14"',            'desc' => 'Apple M3 chip laptop with stunning Retina display.',             'price' => 1999, 'cat' => 'Laptops'],
            ['name' => 'Dell XPS 15',               'desc' => 'Premium Windows laptop with OLED display.',                      'price' => 1599, 'cat' => 'Laptops'],
            ['name' => 'Sony WH-1000XM5',           'desc' => 'Industry-leading noise canceling wireless headphones.',          'price' => 349,  'cat' => 'Headphones'],
            ['name' => 'AirPods Pro 2',             'desc' => 'Apple earbuds with active noise cancellation.',                  'price' => 249,  'cat' => 'Headphones'],
            ['name' => 'Sony A7 IV Camera',         'desc' => 'Full-frame mirrorless camera for professionals.',                'price' => 2499, 'cat' => 'Cameras'],
            ['name' => 'iPad Air 5th Gen',          'desc' => 'Powerful and thin tablet with M1 chip.',                        'price' => 599,  'cat' => 'Tablets'],
            // Fashion
            ['name' => 'Nike Air Max 270',          'desc' => 'Stylish and comfortable running shoes.',                        'price' => 150,  'cat' => 'Shoes'],
            ['name' => 'Adidas Ultraboost 23',      'desc' => 'High-performance running shoes with boost cushioning.',         'price' => 190,  'cat' => 'Shoes'],
            ['name' => 'Levi 501 Original Jeans',   'desc' => 'Classic straight leg jeans in original fit.',                   'price' => 69,   'cat' => "Men's Clothing"],
            ['name' => 'Gucci GG Canvas Bag',       'desc' => 'Luxury handbag with iconic GG canvas pattern.',                 'price' => 1250, 'cat' => 'Bags'],
            ['name' => "Women's Floral Dress",      'desc' => 'Elegant floral summer dress perfect for any occasion.',         'price' => 65,   'cat' => "Women's Clothing"],
            ['name' => 'Silk Scarf',                'desc' => 'Premium silk scarf with beautiful patterns.',                   'price' => 45,   'cat' => 'Accessories'],
            // Home & Garden
            ['name' => 'IKEA KALLAX Shelf',         'desc' => 'Versatile shelf unit perfect for books and storage.',           'price' => 89,   'cat' => 'Furniture'],
            ['name' => 'Instant Pot Duo 7-in-1',    'desc' => 'Electric pressure cooker with 7 functions.',                   'price' => 99,   'cat' => 'Kitchen'],
            ['name' => 'Philips Hue Starter Kit',   'desc' => 'Smart LED lighting system with app control.',                   'price' => 199,  'cat' => 'Lighting'],
            ['name' => 'Egyptian Cotton Bedding Set','desc' => '400 thread count luxury bedding set.',                         'price' => 79,   'cat' => 'Bedding'],
            ['name' => 'Garden Hose 50ft',          'desc' => 'Heavy-duty expandable garden hose with spray nozzle.',         'price' => 35,   'cat' => 'Garden Tools'],
            // Sports
            ['name' => 'Yoga Mat Premium',          'desc' => 'Non-slip extra thick yoga and exercise mat.',                   'price' => 45,   'cat' => 'Gym Equipment'],
            ['name' => 'Resistance Bands Set',      'desc' => 'Set of 5 resistance bands for home workout.',                  'price' => 25,   'cat' => 'Gym Equipment'],
            ['name' => 'Running Watch GPS',         'desc' => 'Advanced GPS running watch with heart rate monitor.',          'price' => 299,  'cat' => 'Running'],
            ['name' => 'Mountain Bike 21-Speed',    'desc' => 'Durable mountain bike suitable for all terrains.',             'price' => 449,  'cat' => 'Cycling'],
            ['name' => 'Hiking Boots Waterproof',   'desc' => 'Lightweight waterproof hiking boots with ankle support.',      'price' => 119,  'cat' => 'Hiking'],
            // Beauty
            ['name' => 'The Ordinary Niacinamide',  'desc' => 'Niacinamide 10% + Zinc 1% serum for blemishes.',              'price' => 12,   'cat' => 'Skincare'],
            ['name' => 'Dyson Airwrap Styler',      'desc' => 'Complete hair styling tool with multiple attachments.',        'price' => 599,  'cat' => 'Hair Care'],
            ['name' => 'Chanel No. 5 Perfume',      'desc' => 'Iconic Chanel perfume for women, 100ml.',                     'price' => 135,  'cat' => 'Perfume'],
            ['name' => 'MAC Ruby Woo Lipstick',     'desc' => 'Classic matte red lipstick by MAC Cosmetics.',                'price' => 22,   'cat' => 'Makeup'],
            // Food
            ['name' => 'Organic Green Tea 100g',    'desc' => 'Premium Japanese organic green tea leaves.',                   'price' => 18,   'cat' => 'Beverages'],
            ['name' => 'Artisan Sourdough Bread',   'desc' => 'Freshly baked traditional sourdough bread loaf.',             'price' => 8,    'cat' => 'Bakery'],
            // Books
            ['name' => 'Atomic Habits',             'desc' => 'Tiny Changes, Remarkable Results by James Clear.',             'price' => 18,   'cat' => 'Non-Fiction'],
            ['name' => 'The Midnight Library',      'desc' => 'A novel by Matt Haig about life and choices.',                 'price' => 15,   'cat' => 'Fiction'],
            // Toys
            ['name' => 'LEGO Technic Race Car',     'desc' => 'Advanced LEGO building set with 1,580 pieces.',               'price' => 89,   'cat' => 'Educational'],
            ['name' => 'Monopoly Classic Board Game','desc' => 'The classic real estate trading board game.',                 'price' => 29,   'cat' => 'Board Games'],
            // Automotive
            ['name' => 'Car Dash Camera 4K',        'desc' => '4K ultra HD dashboard camera with night vision.',             'price' => 89,   'cat' => 'Car Accessories'],
            ['name' => 'Car Vacuum Cleaner',        'desc' => 'Portable handheld car vacuum cleaner 120W.',                  'price' => 39,   'cat' => 'Car Care'],
            // Pets
            ['name' => "Royal Canin Dog Food 15kg", 'desc' => 'Premium dry dog food for adult dogs.',                        'price' => 65,   'cat' => 'Dog Food'],
            ['name' => 'Interactive Cat Toy',       'desc' => 'Electronic interactive feather toy for cats.',                'price' => 22,   'cat' => 'Pet Toys'],
        ];

        $shops = Shop::where('status', 'approved')->get();

        if ($shops->isEmpty()) {
            $this->command->warn('No approved shops found, skipping products.');
            return;
        }

        foreach ($productData as $index => $data) {
            try {
                if (ProductTranslation::where('title', $data['name'])->exists()) {
                    continue;
                }

                $category = CategoryTranslation::where('title', $data['cat'])
                    ->first()
                    ?->category;

                $brand    = Brand::inRandomOrder()->first();
                $shop     = $shops[$index % $shops->count()];
                $price    = $data['price'];

                $product = Product::create([
                    'shop_id'     => $shop->id,
                    'category_id' => $category?->id,
                    'brand_id'    => $brand?->id,
                    'slug'        => Str::slug($data['name'] . '-' . rand(1000, 9999)),
                    'tax'         => rand(5, 15),
                    'active'      => true,
                    'status'      => Product::PUBLISHED,
                    'visibility'  => true,
                    'min_qty'     => 1,
                    'max_qty'     => 100,
                    'min_price'   => $price,
                    'max_price'   => $price,
                ]);

                ProductTranslation::create([
                    'product_id'  => $product->id,
                    'locale'      => $this->locale,
                    'title'       => $data['name'],
                    'description' => $data['desc'],
                ]);

                Stock::create([
                    'product_id' => $product->id,
                    'price'      => $price,
                    'quantity'   => rand(20, 500),
                    'sku'        => strtoupper(Str::random(8)),
                ]);

                $this->attachModelImages(
                    $product,
                    [
                        $this->imageUrl('product-' . Str::slug($data['name']) . '-1', 800, 800),
                        $this->imageUrl('product-' . Str::slug($data['name']) . '-2', 800, 800),
                    ],
                    Gallery::PRODUCTS
                );
            } catch (Throwable $e) {
                $this->command->warn("Skipped product '{$data['name']}': " . $e->getMessage());
            }
        }

        Product::whereDoesntHave('galleries')->each(function (Product $product, int $index) {
            $title = $product->translation?->title ?? 'product-' . $product->id;
            $this->attachModelImages(
                $product,
                $this->imageUrl('product-existing-' . Str::slug($title) . '-' . $index, 800, 800),
                Gallery::PRODUCTS
            );
        });
    }

    private function seedBanners(): void
    {
        $banners = [
            ['title' => 'Summer Sale - Up to 50% Off',       'desc' => 'Huge discounts on thousands of products this summer.',        'btn' => 'Shop Now'],
            ['title' => 'New Electronics Arrivals',          'desc' => 'Discover the latest gadgets and tech products.',              'btn' => 'Explore'],
            ['title' => 'Fashion Week Collection',           'desc' => 'Fresh styles from top brands — shop the new collection.',     'btn' => 'View Collection'],
            ['title' => 'Free Delivery on Orders Over $50',  'desc' => 'Shop more and save on delivery fees.',                        'btn' => 'Shop Now'],
            ['title' => 'Top Brands, Best Prices',           'desc' => 'Nike, Adidas, Apple, Samsung and hundreds more.',             'btn' => 'Browse Brands'],
            ['title' => 'Flash Sale - 24 Hours Only',        'desc' => 'Limited time deals you cannot miss. Grab them while they last.','btn' => 'See Deals'],
        ];

        foreach ($banners as $data) {
            if (BannerTranslation::where('title', $data['title'])->exists()) {
                continue;
            }

            try {
                $banner = Banner::create([
                    'url'       => '#',
                    'active'    => true,
                    'type'      => Banner::BANNER,
                    'clickable' => true,
                    'input'     => 0,
                ]);

                BannerTranslation::create([
                    'banner_id'   => $banner->id,
                    'locale'      => $this->locale,
                    'title'       => $data['title'],
                    'description' => $data['desc'],
                    'button_text' => $data['btn'],
                ]);

                $this->attachModelImages(
                    $banner,
                    $this->imageUrl('banner-' . Str::slug($data['title']), 1400, 500),
                    Gallery::BANNERS
                );
            } catch (Throwable $e) {
                // Skip
            }
        }

        Banner::whereDoesntHave('galleries')->each(function (Banner $banner, int $index) {
            $title = $banner->translation?->title ?? 'banner-' . $banner->id;
            $this->attachModelImages(
                $banner,
                $this->imageUrl('banner-existing-' . Str::slug($title) . '-' . $index, 1400, 500),
                Gallery::BANNERS
            );
        });
    }

    private function seedBlogs(): void
    {
        $adminUser = User::role('admin')->first();

        if (!$adminUser) {
            return;
        }

        $blogs = [
            [
                'title'      => 'Top 10 Gadgets to Buy in 2024',
                'short_desc' => 'A curated list of the best tech gadgets this year.',
                'desc'       => 'From smartphones to smart home devices, here are the top gadgets you need to check out in 2024. We have tested and reviewed each product to bring you only the best recommendations for tech enthusiasts and everyday users alike.',
                'type'       => 1,
            ],
            [
                'title'      => 'How to Choose the Right Running Shoes',
                'short_desc' => 'Expert tips on picking the perfect pair for your feet.',
                'desc'       => 'Choosing the right running shoes is crucial for performance and injury prevention. In this guide, we walk you through the key factors including arch support, cushioning, breathability, and fit to help you make the best decision.',
                'type'       => 1,
            ],
            [
                'title'      => 'Skincare Routine for Beginners',
                'short_desc' => 'Build an effective skincare routine from scratch.',
                'desc'       => 'Starting a skincare routine does not have to be complicated. This beginner-friendly guide covers the essential steps: cleansing, toning, moisturizing, and SPF protection, with product recommendations for every skin type.',
                'type'       => 1,
            ],
            [
                'title'      => 'Smart Home Devices That Save You Money',
                'short_desc' => 'Invest in smart home tech and reduce your utility bills.',
                'desc'       => 'Smart thermostats, LED lighting systems, and energy monitors can dramatically cut your electricity bills. Discover which smart home devices offer the best return on investment and how to set them up in your home.',
                'type'       => 1,
            ],
            [
                'title'      => '5 Easy Home Décor Ideas on a Budget',
                'short_desc' => 'Transform your home without spending a fortune.',
                'desc'       => 'You do not need a big budget to make your home look amazing. From DIY wall art to clever storage solutions and affordable accent furniture, these five ideas will help you create a stylish living space on a shoestring.',
                'type'       => 1,
            ],
            [
                'title'      => 'Healthy Meal Prep Guide for Busy People',
                'short_desc' => 'Save time and eat healthier with weekly meal prep.',
                'desc'       => 'Meal prepping is one of the best habits you can build for a healthier lifestyle. This practical guide covers how to plan your weekly menu, what containers to use, and the most nutritious recipes you can batch-cook in under two hours.',
                'type'       => 1,
            ],
            [
                'title'      => "Kids' Educational Toys That Are Actually Fun",
                'short_desc' => 'Learning through play — the best picks for every age.',
                'desc'       => 'The best educational toys blend learning with fun so children do not even realize they are studying. We review STEM kits, creative building sets, language learning games, and science experiments for kids aged 3 to 14.',
                'type'       => 1,
            ],
            [
                'title'      => 'Your Guide to Buying a Used Car',
                'short_desc' => 'Essential tips before you purchase a second-hand vehicle.',
                'desc'       => 'Buying a used car can save you thousands, but only if you know what to look for. This guide covers how to inspect the vehicle, check service history, negotiate the price, and avoid common scams in the second-hand car market.',
                'type'       => 1,
            ],
        ];

        foreach ($blogs as $data) {
            if (BlogTranslation::where('title', $data['title'])->exists()) {
                continue;
            }

            try {
                $blog = Blog::create([
                    'uuid'         => Str::uuid(),
                    'user_id'      => $adminUser->id,
                    'type'         => $data['type'],
                    'active'       => true,
                    'published_at' => now()->subDays(rand(1, 60))->format('Y-m-d H:i:s'),
                ]);

                BlogTranslation::create([
                    'blog_id'    => $blog->id,
                    'locale'     => $this->locale,
                    'title'      => $data['title'],
                    'short_desc' => $data['short_desc'],
                    'description'=> $data['desc'],
                ]);

                $this->attachModelImages(
                    $blog,
                    $this->imageUrl('blog-' . Str::slug($data['title']), 1200, 630),
                    Gallery::BLOGS
                );
            } catch (Throwable $e) {
                // Skip
            }
        }

        Blog::whereDoesntHave('galleries')->each(function (Blog $blog, int $index) {
            $title = $blog->translation?->title ?? 'blog-' . $blog->id;
            $this->attachModelImages(
                $blog,
                $this->imageUrl('blog-existing-' . Str::slug($title) . '-' . $index, 1200, 630),
                Gallery::BLOGS
            );
        });
    }

    private function seedCountries(): void
    {
        $regions = [
            'North America' => [
                ['name' => 'United States', 'code' => 'us', 'cities' => ['New York', 'Los Angeles', 'Chicago']],
                ['name' => 'Canada',        'code' => 'ca', 'cities' => ['Toronto', 'Vancouver']],
                ['name' => 'Mexico',        'code' => 'mx', 'cities' => ['Mexico City', 'Guadalajara']],
            ],
            'Europe' => [
                ['name' => 'United Kingdom', 'code' => 'gb', 'cities' => ['London', 'Manchester']],
                ['name' => 'Germany',        'code' => 'de', 'cities' => ['Berlin', 'Munich']],
                ['name' => 'France',         'code' => 'fr', 'cities' => ['Paris', 'Lyon']],
                ['name' => 'Spain',          'code' => 'es', 'cities' => ['Madrid', 'Barcelona']],
                ['name' => 'Italy',          'code' => 'it', 'cities' => ['Rome', 'Milan']],
            ],
            'Asia' => [
                ['name' => 'United Arab Emirates', 'code' => 'ae', 'cities' => ['Dubai', 'Abu Dhabi']],
                ['name' => 'Saudi Arabia',         'code' => 'sa', 'cities' => ['Riyadh', 'Jeddah']],
                ['name' => 'Turkey',               'code' => 'tr', 'cities' => ['Istanbul', 'Ankara']],
                ['name' => 'India',                'code' => 'in', 'cities' => ['Mumbai', 'Delhi']],
                ['name' => 'Japan',                'code' => 'jp', 'cities' => ['Tokyo', 'Osaka']],
            ],
            'Africa' => [
                ['name' => 'Morocco',      'code' => 'ma', 'cities' => ['Casablanca', 'Rabat']],
                ['name' => 'Egypt',        'code' => 'eg', 'cities' => ['Cairo', 'Alexandria']],
                ['name' => 'South Africa', 'code' => 'za', 'cities' => ['Johannesburg', 'Cape Town']],
                ['name' => 'Nigeria',      'code' => 'ng', 'cities' => ['Lagos', 'Abuja']],
            ],
            'Oceania' => [
                ['name' => 'Australia',   'code' => 'au', 'cities' => ['Sydney', 'Melbourne']],
                ['name' => 'New Zealand', 'code' => 'nz', 'cities' => ['Auckland', 'Wellington']],
            ],
        ];

        foreach ($regions as $regionName => $countries) {
            $region = Region::whereHas('translation', fn($q) => $q->where('title', $regionName)->where('locale', $this->locale))->first();

            if (!$region) {
                $region = Region::create(['active' => true]);
                RegionTranslation::create([
                    'region_id' => $region->id,
                    'locale'    => $this->locale,
                    'title'     => $regionName,
                ]);
            }

            foreach ($countries as $countryData) {
                $country = Country::where('code', $countryData['code'])->first();

                if (!$country) {
                    $flagUrl = "https://flagcdn.com/h120/{$countryData['code']}.png";
                    $flagImage = $this->downloadImageToStorage($flagUrl, Gallery::OTHER);

                    $country = Country::create([
                        'region_id' => $region->id,
                        'code'      => $countryData['code'],
                        'active'    => true,
                        'img'       => $flagImage ?? $flagUrl,
                    ]);

                    CountryTranslation::create([
                        'country_id' => $country->id,
                        'locale'     => $this->locale,
                        'title'      => $countryData['name'],
                    ]);
                }

                if (!$country->deliveryPrice()->exists()) {
                    DeliveryPrice::create([
                        'price'      => rand(3, 15),
                        'region_id'  => $region->id,
                        'country_id' => $country->id,
                    ]);
                }

                foreach ($countryData['cities'] as $cityName) {
                    $city = City::whereHas('translation', fn($q) => $q->where('title', $cityName)->where('locale', $this->locale))
                        ->where('country_id', $country->id)
                        ->first();

                    if (!$city) {
                        $city = City::create([
                            'region_id'  => $region->id,
                            'country_id' => $country->id,
                            'active'     => true,
                        ]);

                        CityTranslation::create([
                            'city_id' => $city->id,
                            'locale'  => $this->locale,
                            'title'   => $cityName,
                        ]);
                    }

                    if (!$city->deliveryPrice()->exists()) {
                        DeliveryPrice::create([
                            'price'      => rand(2, 10),
                            'region_id'  => $region->id,
                            'country_id' => $country->id,
                            'city_id'    => $city->id,
                        ]);
                    }
                }
            }
        }
    }

    private function imageUrl(string $seed, int $width = 800, int $height = 800): string
    {
        return "https://picsum.photos/seed/{$seed}/{$width}/{$height}.jpg";
    }

    private function ensureStorageReady(): void
    {
        if (!$this->usesS3()) {
            return;
        }

        try {
            $result = S3StorageHelper::preparePublicBucket();
            $this->command->info("S3 bucket \"{$result['bucket']}\" is ready for public read.");
        } catch (Throwable $e) {
            $this->command->warn('S3 storage is not ready: ' . $e->getMessage());
        }
    }

    private function usesS3(): bool
    {
        $awsSetting = Settings::where('key', 'aws')->value('value');

        if ($awsSetting !== null) {
            return (bool) $awsSetting;
        }

        return config('filesystems.default') === 's3'
            || env('FILESYSTEM_DISK') === 's3';
    }

    private function downloadImageToStorage(string $url, string $galleryType): ?string
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'     => 20,
                    'user_agent'  => 'UzmartFakeDataSeeder/1.0',
                    'follow_location' => 1,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $contents = @file_get_contents($url, false, $context);

            if ($contents === false || $contents === '') {
                $this->command->warn("Could not download image: {$url}");
                return null;
            }

            $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg');
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $extension : 'jpg';

            $fileName = 'seed-' . time() . '-' . mt_rand(1000, 9999) . '.' . $extension;
            $storagePath = "public/images/{$galleryType}/{$fileName}";
            $disk = $this->usesS3() ? 's3' : 'local';

            $stored = Storage::disk($disk)->put($storagePath, $contents, 'public');

            if (!$stored) {
                return null;
            }

            if ($this->usesS3()) {
                return rtrim((string) config('app.img_host'), '/') . '/' . ltrim($storagePath, '/');
            }

            return rtrim((string) config('app.img_host'), '/') . '/' . str_replace('public/', 'storage/', $storagePath);
        } catch (Throwable $e) {
            $this->command->warn("Image download failed ({$url}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param Model $model
     * @param string|string[] $urls
     */
    private function attachModelImages(Model $model, string|array $urls, string $galleryType, bool $setImg = true): void
    {
        if (method_exists($model, 'galleries') && $model->galleries()->exists()) {
            return;
        }

        $urls = is_array($urls) ? $urls : [$urls];
        $storedUrls = [];

        foreach ($urls as $url) {
            $storedUrl = $this->downloadImageToStorage($url, $galleryType);

            if ($storedUrl) {
                $storedUrls[] = $storedUrl;
            }
        }

        if ($storedUrls === []) {
            return;
        }

        if ($setImg) {
            $model->update(['img' => $storedUrls[0]]);
        }

        if (method_exists($model, 'uploads')) {
            $model->uploads($storedUrls);
        }
    }

    private function attachShopImages(Shop $shop, string $name, int $index): void
    {
        if ($shop->galleries()->exists()) {
            return;
        }

        $seed = Str::slug($name) . '-' . $index;
        $logo = $this->downloadImageToStorage(
            $this->imageUrl('shop-logo-' . $seed, 400, 400),
            Gallery::SHOPS_LOGO
        );
        $background = $this->downloadImageToStorage(
            $this->imageUrl('shop-bg-' . $seed, 1200, 400),
            Gallery::SHOPS_BACKGROUND
        );

        $images = array_values(array_filter([$logo, $background]));

        if ($images === []) {
            return;
        }

        $shop->update([
            'logo_img'       => $logo ?? $images[0],
            'background_img' => $background ?? ($images[1] ?? $images[0]),
        ]);

        $shop->uploads($images);
    }
}
