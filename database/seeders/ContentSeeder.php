<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // === SITE ===
            ['key' => 'site.name', 'value' => 'Idea Consultancy', 'group' => 'site', 'type' => 'string', 'description' => 'Business name shown in header/footer'],
            ['key' => 'site.logo', 'value' => '', 'group' => 'site', 'type' => 'image', 'description' => 'Site logo (upload via Image Manager or file input)'],
            ['key' => 'site.notice', 'value' => '🎓 New intake open for Germany Winter 2026! Apply now for Fall semester across 50+ partner universities.', 'group' => 'site', 'type' => 'string', 'description' => 'Scrolling marquee notice in header'],

            // === HERO ===
            ['key' => 'content.hero_title', 'value' => 'Your Gateway to <span class="highlight">Global Education</span>', 'group' => 'content', 'type' => 'string', 'description' => 'Hero heading (HTML allowed)'],
            ['key' => 'content.hero_subtitle', 'value' => 'Manage students, track applications, and connect with top universities across Germany, UAE, UK and beyond — all from one powerful platform.', 'group' => 'content', 'type' => 'text', 'description' => 'Hero subtitle text'],

            // === FEATURES (Why Choose Us) ===
            ['key' => 'content.features', 'value' => json_encode([
                ['icon' => 'fa-user-graduate', 'title' => 'Student Management', 'text' => 'Track and manage student profiles, documents, and applications all in one place.'],
                ['icon' => 'fa-university', 'title' => 'University Search', 'text' => 'Browse 180+ partner universities with detailed course listings and intake info.'],
                ['icon' => 'fa-edit', 'title' => 'Application Tracking', 'text' => 'Submit and monitor applications with real-time status updates throughout.'],
                ['icon' => 'fa-chart-line', 'title' => 'Analytics & Reports', 'text' => 'Gain insights into your performance with intuitive dashboards and reports.'],
            ]), 'group' => 'content', 'type' => 'json', 'description' => 'Feature cards (JSON: icon, title, text)'],

            // === BANNERS ===
            ['key' => 'content.banners', 'value' => json_encode([
                ['image' => 'images/banner-3.png', 'alt' => 'Study Abroad Banner'],
            ]), 'group' => 'content', 'type' => 'json', 'description' => 'Banner images shown after features'],

            // === EVENTS ===
            ['key' => 'content.events', 'value' => json_encode([
                ['event_type' => 'Training', 'title' => 'Student Management Training', 'image' => '', 'date' => 'May 15, 2026', 'description' => 'Learn how to manage student profiles and applications efficiently.', 'link' => '#'],
                ['event_type' => 'Webinar', 'title' => 'University Applications Webinar', 'image' => '', 'date' => 'May 25, 2026', 'description' => 'Deep dive into the university application process and requirements.', 'link' => '#'],
                ['event_type' => 'Seminar', 'title' => 'Visa Process Guidance — Germany', 'image' => '', 'date' => 'June 2, 2026', 'description' => 'Step-by-step guidance on German student visa applications.', 'link' => '#'],
            ]), 'group' => 'content', 'type' => 'json', 'description' => 'Events (JSON: event_type, title, image, date, description, link)'],

            // === COUNTRIES ===
            ['key' => 'content.countries_title', 'value' => 'Countries We Work With', 'group' => 'content', 'type' => 'string', 'description' => 'Countries section heading'],
            ['key' => 'content.countries', 'value' => json_encode([
                ['name' => 'Germany', 'image' => 'https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ['name' => 'USA', 'image' => 'https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ['name' => 'UK', 'image' => 'https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=900'],
                ['name' => 'Australia', 'image' => 'https://images.pexels.com/photos/1878293/pexels-photo-1878293.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ['name' => 'Dubai', 'image' => 'https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600'],
                ['name' => 'Malta', 'image' => 'https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600'],
            ]), 'group' => 'content', 'type' => 'json', 'description' => 'Countries grid (JSON: name, image)'],

            // === CTA ===
            ['key' => 'content.cta_title', 'value' => 'Partner With Us Today!', 'group' => 'content', 'type' => 'string', 'description' => 'CTA section heading'],
            ['key' => 'content.cta_description', 'value' => 'Join our growing network of agents across Nepal and place your students in top universities worldwide. Access all tools — free.', 'group' => 'content', 'type' => 'text', 'description' => 'CTA section description'],
            ['key' => 'content.cta_button_text', 'value' => 'Become a Partner', 'group' => 'content', 'type' => 'string', 'description' => 'CTA button text'],
            ['key' => 'content.cta_button_link', 'value' => '/register', 'group' => 'content', 'type' => 'string', 'description' => 'CTA button link'],
            ['key' => 'content.cta_image', 'value' => 'images/banner-2.png', 'group' => 'content', 'type' => 'image', 'description' => 'CTA section image (upload via Image Manager)'],

            // === FOOTER ===
            ['key' => 'content.footer_social_title', 'value' => 'Follow Us', 'group' => 'content', 'type' => 'string', 'description' => 'Footer social section title'],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(
                ['key' => $s['key']],
                $s
            );
        }

        // Delete old keys that are replaced
        Setting::whereIn('key', ['content.stat_universities', 'content.stat_countries', 'content.stat_students'])->delete();
    }
}
