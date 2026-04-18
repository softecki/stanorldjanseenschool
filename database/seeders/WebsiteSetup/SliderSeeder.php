<?php

namespace Database\Seeders\WebsiteSetup;

use App\Models\Slider;
use App\Models\SliderTranslate;
use App\Models\Upload;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = [
            'Where Dreams Take Flight',
            'Empowering Tomorrow\'s Leaders Today',
            'Excellence in Education Since Day One',
        ];
        $descriptions = [
            'At Nalopa School, we believe every child has unlimited potential waiting to be discovered. Our nurturing environment, experienced faculty, and innovative curriculum work together to help students develop academically, socially, and emotionally—preparing them to soar toward their dreams.',
            'Join a community dedicated to developing confident, compassionate, and capable young minds. Through personalized attention, character-building programs, and cutting-edge learning experiences, we empower students to become the leaders and changemakers our world needs.',
            'Experience education that goes beyond textbooks. At Nalopa School, academic excellence meets character development in a vibrant learning environment where every student matters, every talent is nurtured, and every achievement is celebrated.',
        ];
        $images = [
            'frontend/img/sliders/03.webp',
            'frontend/img/sliders/02.webp',
            'frontend/img/sliders/01.webp',
        ];
        foreach ($names as $key=>$item) {
            $upload = new Upload();
            $upload->path = $images[$key];
            $upload->save();

            $row = new Slider();
            $row->name = $item;
            $row->description = $descriptions[$key];
            $row->upload_id = $upload->id;
            $row->serial = $key;
            $row->save();
        }

        $en_name = [
            'Where Dreams Take Flight',
            'Empowering Tomorrow\'s Leaders Today',
            'Excellence in Education Since Day One',
        ];
        $en_descriptions = [
            'At Nalopa School, we believe every child has unlimited potential waiting to be discovered. Our nurturing environment, experienced faculty, and innovative curriculum work together to help students develop academically, socially, and emotionally—preparing them to soar toward their dreams.',
            'Join a community dedicated to developing confident, compassionate, and capable young minds. Through personalized attention, character-building programs, and cutting-edge learning experiences, we empower students to become the leaders and changemakers our world needs.',
            'Experience education that goes beyond textbooks. At Nalopa School, academic excellence meets character development in a vibrant learning environment where every student matters, every talent is nurtured, and every achievement is celebrated.',
        ];

        $bn_name = [
            'যেখানে স্বপ্ন ডানা মেলে',
            'আগামীর নেতৃত্ব আজ থেকেই গড়ি',
            'প্রথম দিন থেকেই শিক্ষায় শ্রেষ্ঠত্ব',
        ];
        $bn_descriptions = [
            'নালোপা স্কুলে, আমরা বিশ্বাস করি প্রতিটি শিশুর মধ্যে সীমাহীন সম্ভাবনা লুকিয়ে আছে যা আবিষ্কারের অপেক্ষায়। আমাদের লালনশীল পরিবেশ, অভিজ্ঞ শিক্ষকমণ্ডলী এবং উদ্ভাবনী পাঠ্যক্রম একসাথে কাজ করে শিক্ষার্থীদের একাডেমিক, সামাজিক এবং আবেগিকভাবে বিকশিত হতে সাহায্য করতে—তাদের স্বপ্নের দিকে উড়ে যাওয়ার জন্য প্রস্তুত করতে।',
            'আত্মবিশ্বাসী, সহানুভূতিশীল এবং সক্ষম তরুণ মনকে গড়ে তোলার জন্য নিবেদিত একটি সম্প্রদায়ে যোগ দিন। ব্যক্তিগত মনোযোগ, চরিত্র-নির্মাণ কর্মসূচি এবং অত্যাধুনিক শেখার অভিজ্ঞতার মাধ্যমে, আমরা শিক্ষার্থীদের নেতা এবং পরিবর্তনকারী হতে ক্ষমতায়ন করি যা আমাদের বিশ্বের প্রয়োজন।',
            'পাঠ্যপুস্তকের বাইরে যাওয়া শিক্ষার অভিজ্ঞতা নিন। নালোপা স্কুলে, একাডেমিক শ্রেষ্ঠত্ব একটি প্রাণবন্ত শিক্ষার পরিবেশে চরিত্র উন্নয়নের সাথে মিলিত হয় যেখানে প্রতিটি শিক্ষার্থী গুরুত্বপূর্ণ, প্রতিটি প্রতিভা লালিত হয় এবং প্রতিটি অর্জন উদযাপিত হয়।',
        ];

        foreach ($en_name as $key=>$item) {
            $row = new SliderTranslate();
            $row->slider_id = $key+1;
            $row->name = $item;
            $row->locale = 'en';
            $row->description = $en_descriptions[$key];
            $row->save();
        }

        foreach ($bn_name as $key=>$item) {
            $row = new SliderTranslate();
            $row->slider_id = $key+1;
            $row->name = $item;
            $row->locale = 'bn';
            $row->description = $bn_descriptions[$key];
            $row->save();
        }


    }
}
