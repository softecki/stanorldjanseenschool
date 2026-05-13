<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Shared JSON meta for public SPA pages (branding + page title).
 */
class PublicSiteMeta
{
    public static function page(string $pageTitle): array
    {
        $about = setting('school_about');
        $tagline = 'Excellence in education & character.';
        if (is_string($about) && trim(strip_tags($about)) !== '') {
            $tagline = Str::limit(trim(strip_tags($about)), 160);
        }

        return [
            'title' => $pageTitle,
            'school' => [
                'name' => (string) (setting('application_name') ?: config('app.name', 'EduSoft')),
                'tagline' => $tagline,
                'phone' => (string) setting('phone', ''),
                'email' => (string) setting('email', ''),
                'address' => (string) setting('address', ''),
            ],
        ];
    }
}
