<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductWarehouseStock;
use App\Models\Seller;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkTestDataSeeder extends Seeder
{
    protected int $categoriesCreated = 0;
    protected int $categoriesSkipped = 0;
    protected int $brandsCreated = 0;
    protected int $brandsSkipped = 0;
    protected int $productsCreated = 0;
    protected int $productsSkipped = 0;
    protected int $sellersCreated = 0;
    protected int $sellersSkipped = 0;
    protected int $imagesCreated = 0;
    protected int $attributesCreated = 0;
    protected int $attributeValuesCreated = 0;
    protected int $warehouseStockCreated = 0;

    public function run(): void
    {
        $this->createSellers();
        $this->createCategories();
        $this->createBrands();
        $this->createAttributes();
        $this->createProducts();
        $this->createWarehouseStock();

        $this->command->info('');
        $this->command->info('=== BULK TEST DATA SUMMARY ===');
        $this->command->info("Sellers: {$this->sellersCreated} created, {$this->sellersSkipped} skipped");
        $this->command->info("Categories: {$this->categoriesCreated} created, {$this->categoriesSkipped} skipped");
        $this->command->info("Brands: {$this->brandsCreated} created, {$this->brandsSkipped} skipped");
        $this->command->info("Attributes: {$this->attributesCreated} created");
        $this->command->info("Attribute Values: {$this->attributeValuesCreated} created");
        $this->command->info("Products: {$this->productsCreated} created, {$this->productsSkipped} skipped");
        $this->command->info("Product Images: {$this->imagesCreated} created");
        $this->command->info("Warehouse Stock: {$this->warehouseStockCreated} created");
    }

    protected function createSellers(): void
    {
        $this->command->info('Creating sellers...');

        $sellersData = [
            ['email' => 'seller@ecommerce.com', 'name' => 'Demo Store', 'desc' => 'Your one-stop shop for quality products.', 'city' => 'Dhaka', 'state' => 'Dhaka', 'featured' => true],
            ['email' => 'techworld@ecommerce.com', 'name' => 'TechWorld Bangladesh', 'desc' => 'Authorized distributor of top electronics brands.', 'city' => 'Dhaka', 'state' => 'Dhaka', 'featured' => true],
            ['email' => 'gadgetzone@ecommerce.com', 'name' => 'GadgetZone', 'desc' => 'Latest gadgets at competitive prices.', 'city' => 'Chittagong', 'state' => 'Chittagong', 'featured' => false],
            ['email' => 'fashionhub@ecommerce.com', 'name' => 'FashionHub', 'desc' => 'Trendy fashion for men and women.', 'city' => 'Sylhet', 'state' => 'Sylhet', 'featured' => false],
            ['email' => 'homeplus@ecommerce.com', 'name' => 'HomePlus Bangladesh', 'desc' => 'Everything for your home and kitchen.', 'city' => 'Rajshahi', 'state' => 'Rajshahi', 'featured' => false],
        ];

        foreach ($sellersData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => explode('@', $data['email'])[0],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $slug = Str::slug($data['name']);
            if (Seller::where('store_slug', $slug)->exists()) {
                $this->sellersSkipped++;
                continue;
            }

            Seller::create([
                'user_id' => $user->id,
                'store_name' => $data['name'],
                'store_slug' => $slug,
                'store_description' => $data['desc'],
                'business_address' => '123 Commerce Street',
                'business_city' => $data['city'],
                'business_state' => $data['state'],
                'business_country' => 'Bangladesh',
                'contact_email' => $data['email'],
                'status' => 'approved',
                'is_featured' => $data['featured'],
                'is_active' => true,
                'approved_at' => now(),
                'commission_rate' => 10.00,
            ]);
            $this->sellersCreated++;
        }
    }

    protected function createCategories(): void
    {
        $this->command->info('Creating categories...');

        $categories = [
            'Mobile Accessories' => ['bn' => 'মোবাইল অ্যাক্সেসরিজ', 'children' => ['Chargers & Adapters', 'Cables & Connectors', 'Screen Protectors', 'Phone Cases & Covers', 'Power Banks', 'Holders & Mounts', 'Earphones & Headsets', 'Stylus Pens']],
            'Computer Accessories' => ['bn' => 'কম্পিউটার অ্যাক্সেসরিজ', 'children' => ['Keyboards', 'Mice & Trackballs', 'Monitors', 'Webcams', 'Printers', 'Mouse Pads', 'Graphics Tablets', 'USB Hubs']],
            'Laptop Accessories' => ['bn' => 'ল্যাপটপ অ্যাক্সেসরিজ', 'children' => ['Laptop Bags & Sleeves', 'Laptop Stands', 'Cooling Pads', 'Laptop Chargers', 'Laptop Memory (RAM)', 'Laptop SSDs', 'Laptop Skins']],
            'Gaming' => ['bn' => 'গেমিং', 'children' => ['Gaming Keyboards', 'Gaming Mice', 'Gaming Headsets', 'Game Controllers', 'Gaming Chairs', 'Gaming Mouse Pads', 'Gaming Monitors']],
            'Bike Accessories' => ['bn' => 'বাইক অ্যাক্সেসরিজ', 'children' => ['Helmets', 'Bike Lights', 'Phone Holders', 'Bike Locks', 'Bike Covers', 'Gloves']],
            'Car Accessories' => ['bn' => 'কার অ্যাক্সেসরিজ', 'children' => ['Car Chargers', 'Car Phone Mounts', 'Car Dash Cameras', 'Car Air Fresheners', 'Seat Covers', 'Car Floor Mats', 'Steering Wheel Covers']],
            "Men's Fashion" => ['bn' => 'পুরুষদের ফ্যাশন', 'children' => ['T-Shirts', 'Shirts', 'Pants & Trousers', 'Jackets', 'Sneakers', 'Watches', 'Bags']],
            "Women's Fashion" => ['bn' => 'নারীদের ফ্যাশন', 'children' => ['Dresses', 'Tops & Blouses', 'Ethnic Wear', 'Footwear', 'Handbags', 'Jewelry', 'Scarves & Wraps']],
            'Electronics' => ['bn' => 'ইলেকট্রনিক্স', 'children' => ['Smartphones', 'Tablets', 'Smart Watches', 'Bluetooth Speakers', 'Headphones', 'Drones', 'Cameras']],
            'Home & Kitchen' => ['bn' => 'হোম ও কিচেন', 'children' => ['Kitchen Appliances', 'Home Decor', 'Lighting', 'Storage & Organization', 'Cleaning Supplies', 'Bedding']],
            'Sports & Outdoors' => ['bn' => 'খেলাধুলা ও আউটডোর', 'children' => ['Fitness Equipment', 'Cycling', 'Cricket', 'Football', 'Camping & Hiking']],
            'Beauty & Personal Care' => ['bn' => 'সৌন্দর্য ও ব্যক্তিগত যত্ন', 'children' => ['Skincare', 'Haircare', 'Makeup', 'Fragrances', 'Grooming']],
            'Books & Stationery' => ['bn' => 'বই ও স্টেশনারি', 'children' => ['Academic Books', 'Novels & Fiction', 'Pens & Pencils', 'Notebooks', 'Art Supplies']],
            'Toys & Baby' => ['bn' => 'খেলনা ও শিশু', 'children' => ['Educational Toys', 'Building Blocks', 'Baby Care', 'Strollers']],
            'Pet Supplies' => ['bn' => 'পোষা প্রাণীর সামগ্রী', 'children' => ['Dog Food', 'Cat Food', 'Pet Toys', 'Pet Accessories']],
        ];

        $sort = 0;
        foreach ($categories as $name => $data) {
            $parent = $this->findOrCreateCategory($name, $data['bn'], null, $sort);
            $sort++;
            $childSort = 0;
            foreach ($data['children'] as $child) {
                $this->findOrCreateCategory($child, null, $parent->id, $childSort);
                $childSort++;
            }
        }
    }

    protected function findOrCreateCategory(string $name, ?string $nameBn, ?int $parentId, int $sortOrder): Category
    {
        $slug = Str::slug($name);
        $existing = Category::where('slug', $slug)->first();
        if ($existing) {
            $this->categoriesSkipped++;
            return $existing;
        }
        $this->categoriesCreated++;
        return Category::create([
            'name' => $name,
            'name_bn' => $nameBn,
            'parent_id' => $parentId,
            'status' => 'active',
            'sort_order' => $sortOrder,
        ]);
    }

    protected function createBrands(): void
    {
        $this->command->info('Creating brands...');

        $brands = [
            ['name' => 'Baseus', 'bn' => 'বেসাস', 'web' => 'https://baseus.com'],
            ['name' => 'Anker', 'bn' => 'অ্যাঙ্কার', 'web' => 'https://anker.com'],
            ['name' => 'UGREEN', 'bn' => 'ইউগ্রিন', 'web' => 'https://ugreen.com'],
            ['name' => 'Xiaomi', 'bn' => 'শাওমি', 'web' => 'https://xiaomi.com'],
            ['name' => 'Samsung', 'bn' => 'স্যামসাং', 'web' => 'https://samsung.com'],
            ['name' => 'Logitech', 'bn' => 'লজিটেক', 'web' => 'https://logitech.com'],
            ['name' => 'HP', 'bn' => 'এইচপি', 'web' => 'https://hp.com'],
            ['name' => 'Lenovo', 'bn' => 'লেনোভো', 'web' => 'https://lenovo.com'],
            ['name' => 'A4Tech', 'bn' => 'এ৪টেক', 'web' => 'https://a4tech.com'],
            ['name' => 'Havit', 'bn' => 'হাভিট', 'web' => 'https://havit.com'],
            ['name' => 'Apple', 'bn' => 'অ্যাপল', 'web' => 'https://apple.com'],
            ['name' => 'Realme', 'bn' => 'রিয়ালমি', 'web' => 'https://realme.com'],
            ['name' => 'OnePlus', 'bn' => 'ওয়ানপ্লাস', 'web' => 'https://oneplus.com'],
            ['name' => 'Nothing', 'bn' => 'নাথিং', 'web' => 'https://nothing.tech'],
            ['name' => 'JBL', 'bn' => 'জেবিএল', 'web' => 'https://jbl.com'],
            ['name' => 'Sony', 'bn' => 'সোনি', 'web' => 'https://sony.com'],
            ['name' => 'Bose', 'bn' => 'বোজ', 'web' => 'https://bose.com'],
            ['name' => 'Razer', 'bn' => 'রেজার', 'web' => 'https://razer.com'],
            ['name' => 'SteelSeries', 'bn' => 'স্টিলসিরিজ', 'web' => 'https://steelseries.com'],
            ['name' => 'HyperX', 'bn' => 'হাইপারএক্স', 'web' => 'https://hyperx.com'],
            ['name' => 'Corsair', 'bn' => 'করসেয়ার', 'web' => 'https://corsair.com'],
            ['name' => 'ASUS', 'bn' => 'এআসাস', 'web' => 'https://asus.com'],
            ['name' => 'Dell', 'bn' => 'ডেল', 'web' => 'https://dell.com'],
            ['name' => 'Acer', 'bn' => 'এসার', 'web' => 'https://acer.com'],
            ['name' => 'MSI', 'bn' => 'এমএসআই', 'web' => 'https://msi.com'],
            ['name' => 'Nokia', 'bn' => 'নোকিয়া', 'web' => 'https://nokia.com'],
            ['name' => 'Motorola', 'bn' => 'মোটোরোলা', 'web' => 'https://motorola.com'],
            ['name' => 'Vivo', 'bn' => 'ভিভো', 'web' => 'https://vivo.com'],
            ['name' => 'Honor', 'bn' => 'অনার', 'web' => 'https://honor.com'],
            ['name' => 'Huawei', 'bn' => 'হুয়াওয়ে', 'web' => 'https://huawei.com'],
            ['name' => 'TP-Link', 'bn' => 'টিপি-লিংক', 'web' => 'https://tp-link.com'],
            ['name' => 'D-Link', 'bn' => 'ডি-লিংক', 'web' => 'https://dlink.com'],
            ['name' => 'BenQ', 'bn' => 'বেনকিউ', 'web' => 'https://benq.com'],
            ['name' => 'LG', 'bn' => 'এলজি', 'web' => 'https://lg.com'],
            ['name' => 'Panasonic', 'bn' => 'প্যানাসনিক', 'web' => 'https://panasonic.com'],
            ['name' => 'Philips', 'bn' => 'ফিলিপস', 'web' => 'https://philips.com'],
            ['name' => 'Dyson', 'bn' => 'ডাইসন', 'web' => 'https://dyson.com'],
            ['name' => 'Nikon', 'bn' => 'নিকন', 'web' => 'https://nikon.com'],
            ['name' => 'Canon', 'bn' => 'ক্যানন', 'web' => 'https://canon.com'],
            ['name' => 'GoPro', 'bn' => 'গোপ্রো', 'web' => 'https://gopro.com'],
            ['name' => 'DJI', 'bn' => 'ডিজেআই', 'web' => 'https://dji.com'],
            ['name' => 'Garmin', 'bn' => 'গারমিন', 'web' => 'https://garmin.com'],
            ['name' => 'Fossil', 'bn' => 'ফসিল', 'web' => 'https://fossil.com'],
            ['name' => 'Amazfit', 'bn' => 'অ্যামাজফিট', 'web' => 'https://amazfit.com'],
            ['name' => 'boAt', 'bn' => 'বোট', 'web' => 'https://boAt.com'],
            ['name' => 'Noise', 'bn' => 'নয়েজ', 'web' => 'https://gonoise.com'],
            ['name' => 'Fire-Boltt', 'bn' => 'ফায়ার-বোল্ট', 'web' => 'https://fireboltt.com'],
            ['name' => 'Ambrane', 'bn' => 'অ্যামব্রেন', 'web' => 'https://ambrane.com'],
            ['name' => 'Portronics', 'bn' => 'পোর্ট্রনিক্স', 'web' => 'https://portronics.com'],
            ['name' => 'Zebronics', 'bn' => 'জেব্রনিক্স', 'web' => 'https://zebronics.com'],
            ['name' => 'Intex', 'bn' => 'ইনটেক্স', 'web' => 'https://intex.com'],
            ['name' => 'Redgear', 'bn' => 'রেডগিয়ার', 'web' => 'https://redgear.com'],
            ['name' => 'Cosmic Byte', 'bn' => 'কসমিক বাইট', 'web' => 'https://cosmicbyte.com'],
            ['name' => 'Ant Esports', 'bn' => 'অ্যান্ট ইস্পোর্টস', 'web' => 'https://antesports.com'],
            ['name' => 'Deepcool', 'bn' => 'ডিপকুল', 'web' => 'https://deepcool.com'],
            ['name' => 'Cooler Master', 'bn' => 'কুলার মাস্টার', 'web' => 'https://coolermaster.com'],
            ['name' => 'Thermaltake', 'bn' => 'থার্মালটেক', 'web' => 'https://thermaltake.com'],
            ['name' => 'NZXT', 'bn' => 'এনজেডএক্সটি', 'web' => 'https://nzxt.com'],
            ['name' => 'Kingston', 'bn' => 'কিংস্টন', 'web' => 'https://kingston.com'],
            ['name' => 'SanDisk', 'bn' => 'স্যানডিস্ক', 'web' => 'https://sandisk.com'],
            ['name' => 'Crucial', 'bn' => 'ক্রুসিয়াল', 'web' => 'https://crucial.com'],
            ['name' => 'Western Digital', 'bn' => 'ওয়েস্টার্ন ডিজিটাল', 'web' => 'https://westerndigital.com'],
            ['name' => 'Seagate', 'bn' => 'সিগেট', 'web' => 'https://seagate.com'],
            ['name' => 'Gigabyte', 'bn' => 'গিগাবাইট', 'web' => 'https://gigabyte.com'],
            ['name' => 'G.Skill', 'bn' => 'জি.স্কিল', 'web' => 'https://gskill.com'],
            ['name' => 'TeamGroup', 'bn' => 'টিমগ্রুপ', 'web' => 'https://teamgroupinc.com'],
            ['name' => 'ADATA', 'bn' => 'এডাটা', 'web' => 'https://adata.com'],
            ['name' => 'PNY', 'bn' => 'পিএনওয়াই', 'web' => 'https://pny.com'],
            ['name' => 'Elgato', 'bn' => 'এলগেটো', 'web' => 'https://elgato.com'],
            ['name' => 'Blue Microphones', 'bn' => 'ব্লু মাইক্রোফোন', 'web' => 'https://bluemic.com'],
            ['name' => 'Audio-Technica', 'bn' => 'অডিও-টেকনিকা', 'web' => 'https://audio-technica.com'],
            ['name' => 'Shure', 'bn' => 'শুর', 'web' => 'https://shure.com'],
            ['name' => 'Sennheiser', 'bn' => 'জেনহাইসার', 'web' => 'https://sennheiser.com'],
            ['name' => 'Beats', 'bn' => 'বিটস', 'web' => 'https://beatsbydre.com'],
            ['name' => 'Skullcandy', 'bn' => 'স্কালক্যান্ডি', 'web' => 'https://skullcandy.com'],
            ['name' => 'Marshall', 'bn' => 'মার্শাল', 'web' => 'https://marshall.com'],
            ['name' => 'Bang & Olufsen', 'bn' => 'ব্যাঙ্গ অ্যান্ড ওলুফসেন', 'web' => 'https://bang-olufsen.com'],
            ['name' => 'Edifier', 'bn' => 'এডিফায়ার', 'web' => 'https://edifier.com'],
            ['name' => 'Creative', 'bn' => 'ক্রিয়েটিভ', 'web' => 'https://soundblaster.com'],
            ['name' => 'Harman Kardon', 'bn' => 'হারমান কার্ডন', 'web' => 'https://harmankardon.com'],
            ['name' => 'Nillkin', 'bn' => 'নিলকিন', 'web' => 'https://nillkin.com'],
            ['name' => 'Spigen', 'bn' => 'স্পাইজেন', 'web' => 'https://spigen.com'],
            ['name' => 'OtterBox', 'bn' => 'অটারবক্স', 'web' => 'https://otterbox.com'],
            ['name' => 'UAG', 'bn' => 'ইউএজি', 'web' => 'https://uag.com'],
            ['name' => 'Belkin', 'bn' => 'বেলকিন', 'web' => 'https://belkin.com'],
            ['name' => 'Rapoo', 'bn' => 'রাপু', 'web' => 'https://rapoo.com'],
            ['name' => 'Prestige', 'bn' => 'প্রেস্টিজ', 'web' => 'https://prestigecooker.com'],
            ['name' => 'Butterfly', 'bn' => 'বাটারফ্লাই', 'web' => 'https://butterflyindia.com'],
            ['name' => 'Cello', 'bn' => 'সেলো', 'web' => 'https://cello.in'],
            ['name' => 'Decathlon', 'bn' => 'ডিকাথলন', 'web' => 'https://decathlon.com'],
            ['name' => 'Wildcraft', 'bn' => 'ওয়াইল্ডক্রাফ্ট', 'web' => 'https://wildcraft.com'],
            ['name' => 'Casio', 'bn' => 'ক্যাসিও', 'web' => 'https://casio.com'],
            ['name' => 'Nike', 'bn' => 'নাইকি', 'web' => 'https://nike.com'],
            ['name' => 'Adidas', 'bn' => 'অ্যাডিডাস', 'web' => 'https://adidas.com'],
            ['name' => 'Puma', 'bn' => 'পুমা', 'web' => 'https://puma.com'],
            ['name' => 'Vega', 'bn' => 'ভেগা', 'web' => 'https://vegahelmets.com'],
            ['name' => 'KONG', 'bn' => 'কং', 'web' => 'https://kong.com'],
            ['name' => 'Pedigree', 'bn' => 'পেডিগ্রি', 'web' => 'https://pedigree.com'],
            ['name' => 'LEGO', 'bn' => 'লেগো', 'web' => 'https://lego.com'],
            ['name' => 'Cetaphil', 'bn' => 'সেটাফিল', 'web' => 'https://cetaphil.com'],
            ['name' => 'Minimalist', 'bn' => 'মিনিমালিস্ট', 'web' => 'https://minimalist.com'],
            ['name' => 'Biotique', 'bn' => 'বায়োটিক', 'web' => 'https://biotique.com'],
            ['name' => 'Lakme', 'bn' => 'লাকমে', 'web' => 'https://lakme.com'],
            ['name' => 'Fogg', 'bn' => 'ফগ', 'web' => 'https://fogg.in'],
            ['name' => 'Classmate', 'bn' => 'ক্লাসমেট', 'web' => 'https://classmate.com'],
            ['name' => 'Faber-Castell', 'bn' => 'ফেবার-ক্যাস্টেল', 'web' => 'https://faber-castell.com'],
            ['name' => 'SG', 'bn' => 'এসজি', 'web' => 'https://sgcricket.com'],
            ['name' => 'Nivia', 'bn' => 'নিভিয়া', 'web' => 'https://nivia.com'],
            ['name' => 'Yonex', 'bn' => 'যোনেক্স', 'web' => 'https://yonex.com'],
            ['name' => 'MRF', 'bn' => 'এমআরএফ', 'web' => 'https://mrf.com'],
            ['name' => 'Prestige', 'bn' => 'প্রেস্টিজ', 'web' => 'https://prestigecooker.com'],
            ['name' => 'Instant Pot', 'bn' => 'ইন্সট্যান্ট Pot', 'web' => 'https://instantpot.com'],
            ['name' => 'IKEA', 'bn' => 'আইকিয়া', 'web' => 'https://ikea.com'],
            ['name' => 'H&M', 'bn' => 'H&M', 'web' => 'https://hm.com'],
            ['name' => 'Only', 'bn' => 'ওনলি', 'web' => 'https://only.com'],
            ['name' => 'Jack & Jones', 'bn' => 'জ্যাক অ্যান্ড জোনস', 'web' => 'https://jackjones.com'],
            ['name' => 'Whiskas', 'bn' => 'হোইস্কাস', 'web' => 'https://whiskas.com'],
            ['name' => 'Meow Mix', 'bn' => 'মিও মিক্স', 'web' => 'https://meowmix.com'],
            ['name' => 'PetSafe', 'bn' => 'পেটসেফ', 'web' => 'https://petsafe.com'],
        ];

        foreach ($brands as $i => $data) {
            $slug = Str::slug($data['name']);
            if (Brand::where('slug', $slug)->exists()) {
                $this->brandsSkipped++;
                continue;
            }
            Brand::create([
                'name' => $data['name'],
                'name_bn' => $data['bn'],
                'slug' => $slug,
                'website' => $data['web'],
                'status' => 'active',
                'sort_order' => $i,
            ]);
            $this->brandsCreated++;
        }
    }

    protected function createAttributes(): void
    {
        $this->command->info('Creating attributes...');

        $attrs = [
            ['name' => 'Color', 'bn' => 'রঙ', 'type' => 'select', 'filter' => true, 'variant' => true, 'values' => [
                ['v' => 'Black', 'bn' => 'কালো', 'c' => '#000000'], ['v' => 'White', 'bn' => 'সাদা', 'c' => '#FFFFFF'],
                ['v' => 'Red', 'bn' => 'লাল', 'c' => '#FF0000'], ['v' => 'Blue', 'bn' => 'নীল', 'c' => '#0000FF'],
                ['v' => 'Green', 'bn' => 'সবুজ', 'c' => '#00FF00'], ['v' => 'Silver', 'bn' => 'রূপা', 'c' => '#C0C0C0'],
                ['v' => 'Gold', 'bn' => 'সোনালি', 'c' => '#FFD700'], ['v' => 'Pink', 'bn' => 'গোলাপি', 'c' => '#FFC0CB'],
                ['v' => 'Purple', 'bn' => 'বেগুনি', 'c' => '#800080'], ['v' => 'Gray', 'bn' => 'ধূসর', 'c' => '#808080'],
                ['v' => 'Navy Blue', 'bn' => 'নীলাভ', 'c' => '#000080'], ['v' => 'Rose Gold', 'bn' => 'রোজ গোল্ড', 'c' => '#B76E79'],
                ['v' => 'Matte Black', 'bn' => 'ম্যাট কালো', 'c' => '#28282B'], ['v' => 'Transparent', 'bn' => 'স্বচ্ছ', 'c' => null],
            ]],
            ['name' => 'Size', 'bn' => 'সাইজ', 'type' => 'select', 'filter' => true, 'variant' => true, 'values' => [
                ['v' => 'XS', 'bn' => 'XS'], ['v' => 'S', 'bn' => 'S'], ['v' => 'M', 'bn' => 'M'],
                ['v' => 'L', 'bn' => 'L'], ['v' => 'XL', 'bn' => 'XL'], ['v' => 'XXL', 'bn' => 'XXL'], ['v' => 'One Size', 'bn' => 'এক সাইজ'],
            ]],
            ['name' => 'Storage', 'bn' => 'স্টোরেজ', 'type' => 'select', 'filter' => true, 'variant' => true, 'values' => [
                ['v' => '32GB', 'bn' => '৩২ জিবি'], ['v' => '64GB', 'bn' => '৬৪ জিবি'], ['v' => '128GB', 'bn' => '১২৮ জিবি'],
                ['v' => '256GB', 'bn' => '২৫৬ জিবি'], ['v' => '512GB', 'bn' => '৫১২ জিবি'], ['v' => '1TB', 'bn' => '১ টিবি'], ['v' => '2TB', 'bn' => '২ টিবি'],
            ]],
            ['name' => 'RAM', 'bn' => 'র‍্যাম', 'type' => 'select', 'filter' => true, 'variant' => true, 'values' => [
                ['v' => '2GB', 'bn' => '২ জিবি'], ['v' => '4GB', 'bn' => '৪ জিবি'], ['v' => '6GB', 'bn' => '৬ জিবি'],
                ['v' => '8GB', 'bn' => '৮ জিবি'], ['v' => '12GB', 'bn' => '১২ জিবি'], ['v' => '16GB', 'bn' => '১৬ জিবি'], ['v' => '32GB', 'bn' => '৩২ জিবি'],
            ]],
            ['name' => 'Length', 'bn' => 'দৈর্ঘ্য', 'type' => 'select', 'filter' => true, 'variant' => true, 'values' => [
                ['v' => '1m', 'bn' => '১ মিটার'], ['v' => '1.5m', 'bn' => '১.৫ মিটার'], ['v' => '2m', 'bn' => '২ মিটার'],
                ['v' => '3m', 'bn' => '৩ মিটার'], ['v' => '5m', 'bn' => '৫ মিটার'], ['v' => '10m', 'bn' => '১০ মিটার'],
            ]],
            ['name' => 'Wattage', 'bn' => 'ওয়াট', 'type' => 'text', 'filter' => true, 'variant' => false, 'values' => [
                ['v' => '5W', 'bn' => '৫ ওয়াট'], ['v' => '10W', 'bn' => '১০ ওয়াট'], ['v' => '18W', 'bn' => '১৮ ওয়াট'],
                ['v' => '20W', 'bn' => '২০ ওয়াট'], ['v' => '25W', 'bn' => '২৫ ওয়াট'], ['v' => '33W', 'bn' => '৩৩ ওয়াট'],
                ['v' => '65W', 'bn' => '৬৫ ওয়াট'], ['v' => '100W', 'bn' => '১০০ ওয়াট'], ['v' => '120W', 'bn' => '১২০ ওয়াট'],
            ]],
            ['name' => 'Connectivity', 'bn' => 'সংযোগ', 'type' => 'select', 'filter' => true, 'variant' => false, 'values' => [
                ['v' => 'USB-C', 'bn' => 'ইউএসবি-সি'], ['v' => 'USB-A', 'bn' => 'ইউএসবি-এ'], ['v' => 'Lightning', 'bn' => 'লাইটনিং'],
                ['v' => 'Bluetooth', 'bn' => 'ব্লুটুথ'], ['v' => 'WiFi', 'bn' => 'ওয়াইফাই'], ['v' => '3.5mm Jack', 'bn' => '৩.৫ মিমি জ্যাক'],
                ['v' => 'Wireless', 'bn' => 'ওয়্যারলেস'], ['v' => 'Wired', 'bn' => 'ওয়্যারড'],
            ]],
        ];

        foreach ($attrs as $data) {
            $slug = Str::slug($data['name']);
            $attribute = Attribute::firstOrCreate(
                ['slug' => $slug],
                ['name' => $data['name'], 'name_bn' => $data['bn'], 'type' => $data['type'],
                 'is_filterable' => $data['filter'], 'is_variant' => $data['variant'], 'status' => 'active']
            );
            if ($attribute->wasRecentlyCreated) $this->attributesCreated++;

            foreach ($data['values'] as $v) {
                $av = AttributeValue::firstOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $v['v']],
                    ['value_bn' => $v['bn'] ?? null, 'slug' => Str::slug($data['name'] . '-' . $v['v']), 'color_code' => $v['c'] ?? null]
                );
                if ($av->wasRecentlyCreated) $this->attributeValuesCreated++;
            }
        }
    }

    protected function createProducts(): void
    {
        $this->command->info('Creating products...');

        $sellers = Seller::where('status', 'approved')->get();
        $allCategories = Category::where('status', 'active')->get();
        $brands = Brand::where('status', 'active')->get();

        if ($sellers->isEmpty() || $brands->isEmpty()) {
            $this->command->error('No sellers or brands found.');
            return;
        }

        $colorAttr = Attribute::where('slug', 'color')->first();
        $colorValues = $colorAttr ? $colorAttr->values : collect();
        $wattageAttr = Attribute::where('slug', 'wattage')->first();
        $wattageValues = $wattageAttr ? $wattageAttr->values : collect();
        $connAttr = Attribute::where('slug', 'connectivity')->first();
        $connValues = $connAttr ? $connAttr->values : collect();

        $allProducts = $this->getAllProducts();
        $counter = 0;

        foreach ($allProducts as $data) {
            $slug = Str::slug($data[0]);
            if (Product::where('slug', $slug)->exists() || Product::where('sku', $data[5])->exists()) {
                $this->productsSkipped++;
                continue;
            }

            $seller = $sellers[$counter % $sellers->count()];
            $category = $allCategories->where('name', $data[3])->first() ?? $allCategories->random();
            $brand = $brands->where('name', $data[4])->first() ?? $brands->random();

            $product = Product::create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $data[0],
                'name_bn' => $data[1],
                'slug' => $slug,
                'description' => "High quality {$data[0]} from {$data[4]}.",
                'sku' => $data[5],
                'price' => $data[2],
                'compare_at_price' => $data[2] * 1.2,
                'cost_price' => $data[2] * 0.6,
                'quantity' => rand(10, 200),
                'manage_stock' => true,
                'low_stock_threshold' => 5,
                'status' => 'published',
                'is_active' => true,
                'is_featured' => $data[6] ?? false,
                'weight' => rand(100, 5000) / 100,
                'type' => 'physical',
            ]);

            $this->productsCreated++;
            $counter++;

            ProductImage::create([
                'product_id' => $product->id,
                'path' => "products/{$slug}.jpg",
                'alt_text' => $data[0],
                'sort_order' => 0,
                'is_featured' => true,
            ]);
            $this->imagesCreated++;

            if ($colorValues->isNotEmpty() && in_array($category->name, ['Chargers & Adapters', 'Cables & Connectors', 'Phone Cases & Covers', 'Power Banks', 'Keyboards', 'Mice & Trackballs', 'Gaming Keyboards', 'Gaming Mice', 'Gaming Headsets'])) {
                $cv = $colorValues->random();
                ProductAttributeValue::create(['product_id' => $product->id, 'attribute_id' => $colorAttr->id, 'attribute_value_id' => $cv->id, 'value' => $cv->value]);
            }

            if ($wattageValues->isNotEmpty() && (str_contains($data[0], 'Charger') || str_contains($data[0], 'Adapter') || str_contains($data[0], 'Power Bank'))) {
                $wv = $wattageValues->random();
                ProductAttributeValue::create(['product_id' => $product->id, 'attribute_id' => $wattageAttr->id, 'attribute_value_id' => $wv->id, 'value' => $wv->value]);
            }

            if ($connValues->isNotEmpty() && (str_contains($data[0], 'Mouse') || str_contains($data[0], 'Keyboard') || str_contains($data[0], 'Headphone') || str_contains($data[0], 'Speaker') || str_contains($data[0], 'Earbuds') || str_contains($data[0], 'Earphones'))) {
                $cn = $connValues->random();
                ProductAttributeValue::create(['product_id' => $product->id, 'attribute_id' => $connAttr->id, 'attribute_value_id' => $cn->id, 'value' => $cn->value]);
            }

            if ($counter % 100 === 0) {
                $this->command->info("  Created {$counter} products...");
            }
        }
    }

    protected function getAllProducts(): array
    {
        return require __DIR__ . '/data/products.php';
    }

    protected function createWarehouseStock(): void
    {
        $this->command->info('Creating warehouse stock...');

        $wh = Warehouse::where('is_default', true)->first()
            ?? Warehouse::firstOrCreate(
                ['code' => 'WH-DEFAULT'],
                ['name' => 'Main Warehouse', 'address' => '123 Warehouse Road', 'city' => 'Dhaka', 'state' => 'Dhaka', 'country' => 'Bangladesh', 'status' => 'active', 'is_default' => true]
            );

        $products = Product::where('manage_stock', true)->get();
        $batch = [];
        foreach ($products as $p) {
            if (!ProductWarehouseStock::where('product_id', $p->id)->where('warehouse_id', $wh->id)->exists()) {
                $batch[] = ['product_id' => $p->id, 'warehouse_id' => $wh->id, 'quantity' => $p->quantity, 'reserved_quantity' => 0, 'created_at' => now(), 'updated_at' => now()];
            }
        }
        if (!empty($batch)) {
            ProductWarehouseStock::insert($batch);
            $this->warehouseStockCreated = count($batch);
        }
    }
}
