<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use App\Models\Lookbook;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class HomepageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Hero Slides
        HeroSlide::create([
            'badge' => "Autumn / Winter '26 Collection",
            'title' => 'Designed for Comfort.',
            'highlight' => 'Crafted for Excellence.',
            'description' => 'Discover clean designs, premium fabrics, and timeless styles designed to elevate your everyday wear.',
            'cta_text' => 'Explore Collection',
            'cta_link' => '#shop',
            'image_path' => '/images/hero_slide_1.png',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HeroSlide::create([
            'badge' => 'Sustainable Essentials',
            'title' => 'Timeless Silhouettes.',
            'highlight' => 'Natural Myanmar Materials.',
            'description' => 'Responsibly sourced, beautifully woven linen and organic cotton garments built to last.',
            'cta_text' => 'Shop Sustainable',
            'cta_link' => '#shop',
            'image_path' => '/images/hero_slide_2.png',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        HeroSlide::create([
            'badge' => 'Exclusive Studio Drops',
            'title' => 'Limited Run Apparel.',
            'highlight' => 'Tailored to Perfection.',
            'description' => 'Bespoke-quality cuts and contemporary outerwear tailored in limited quantities.',
            'cta_text' => 'Discover Studio',
            'cta_link' => '#shop',
            'image_path' => '/images/hero_slide_3.png',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // 2. Seed Lookbooks
        Lookbook::create([
            'badge' => 'Active Streetwear',
            'title' => 'The Transit Collection',
            'description' => 'Relaxed silhouettes, heavyweight fleece, and utility bags designed for city movements.',
            'cta_text' => 'Explore Lookbook',
            'cta_link' => '#shop',
            'image_path' => '/images/lookbook_streetwear.png',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Lookbook::create([
            'badge' => 'Minimalist tailoring',
            'title' => 'Studio Linen & Basics',
            'description' => 'Soft textures, lightweight linen fabrics, and neutral tones for calm summer luxury.',
            'cta_text' => 'Explore Lookbook',
            'cta_link' => '#shop',
            'image_path' => '/images/lookbook_linen.png',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // 3. Seed Brand Story Settings
        Setting::set('brand_story_title', 'Crafting a Dialogue Between Material & Form.');
        Setting::set('brand_story_text_1', 'At ESTY, we believe garments should not simply be worn, but experienced. We combine bespoke-level craftsmanship with raw Myanmar organic cottons and carefully sourced fabrics.');
        Setting::set('brand_story_text_2', 'Every buttonhole, every shoulder drape, and every single stitch is carefully planned and executed in our local garment studio. By keeping production limited, we ensure slow, mindful consumption and outstanding quality that endures.');
        Setting::set('brand_story_stat_1_val', '100%');
        Setting::set('brand_story_stat_1_lbl', 'Local Sourcing');
        Setting::set('brand_story_stat_2_val', 'Limited');
        Setting::set('brand_story_stat_2_lbl', 'Studio Run');
        Setting::set('brand_story_image', '/images/brand_story.png');
    }
}
