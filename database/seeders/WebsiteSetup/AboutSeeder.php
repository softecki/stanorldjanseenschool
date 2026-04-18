<?php

namespace Database\Seeders\WebsiteSetup;

use App\Models\AboutTranslate;
use App\Models\Upload;
use App\Models\WebsiteSetup\About;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = [
            'Discover Our Campus',
            'Academic Excellence',
            'Successful Alumni Network',
        ];
        $descriptions = [
            'Explore our state-of-the-art facilities designed to inspire learning and creativity. Our modern campus features well-equipped classrooms, advanced science and computer laboratories, a comprehensive library, spacious sports facilities, and dedicated spaces for arts and extracurricular activities. Every corner of Nalopa School is thoughtfully designed to create an environment that nurtures curiosity, encourages exploration, and supports holistic student development. Join us for a campus tour and experience firsthand the vibrant, welcoming atmosphere that makes our school a second home for students.',
            'At Nalopa School, academic excellence is at the heart of everything we do. Our rigorous curriculum, delivered by highly qualified and passionate educators, ensures that every student receives a world-class education. We combine traditional teaching methods with innovative approaches, including technology-integrated learning, project-based activities, and personalized attention to help each student reach their full potential. Our comprehensive academic programs prepare students not just for examinations, but for life—developing critical thinking, problem-solving skills, and a genuine love for learning that extends beyond the classroom.',
            'Our alumni are our pride and testament to the quality education at Nalopa School. Graduates of our institution have gone on to achieve remarkable success in diverse fields including medicine, engineering, business, arts, public service, and academia. They serve as inspiring role models and mentors to current students, demonstrating the lasting impact of the values, knowledge, and skills acquired during their time at Nalopa School. Our strong alumni network remains actively engaged with the school community, contributing to its growth and continuing to embody our motto that "Education is Life" through their meaningful contributions to society.',
        ];
        $bn_names = [
            'আমাদের ক্যাম্পাস আবিষ্কার করুন',
            'একাডেমিক শ্রেষ্ঠত্ব',
            'সফল প্রাক্তন ছাত্র নেটওয়ার্ক',
        ];
        $bn_descriptions = [
            'শেখার এবং সৃজনশীলতাকে অনুপ্রাণিত করার জন্য ডিজাইন করা আমাদের অত্যাধুনিক সুবিধাগুলি অন্বেষণ করুন। আমাদের আধুনিক ক্যাম্পাসে সুসজ্জিত শ্রেণীকক্ষ, উন্নত বিজ্ঞান ও কম্পিউটার ল্যাবরেটরি, একটি ব্যাপক লাইব্রেরি, প্রশস্ত ক্রীড়া সুবিধা এবং শিল্প ও সহপাঠ্যক্রমিক কার্যক্রমের জন্য নিবেদিত স্থান রয়েছে। নালোপা স্কুলের প্রতিটি কোণ চিন্তাশীলভাবে ডিজাইন করা হয়েছে এমন একটি পরিবেশ তৈরি করতে যা কৌতূহল লালন করে, অন্বেষণকে উৎসাহিত করে এবং সামগ্রিক শিক্ষার্থী উন্নয়নকে সমর্থন করে।',
            'নালোপা স্কুলে, একাডেমিক শ্রেষ্ঠত্ব আমাদের সবকিছুর কেন্দ্রে রয়েছে। উচ্চ যোগ্যতাসম্পন্ন এবং উত্সাহী শিক্ষাবিদদের দ্বারা সরবরাহিত আমাদের কঠোর পাঠ্যক্রম নিশ্চিত করে যে প্রতিটি শিক্ষার্থী বিশ্বমানের শিক্ষা পায়। আমরা ঐতিহ্যগত শিক্ষণ পদ্ধতিগুলিকে উদ্ভাবনী পদ্ধতির সাথে একত্রিত করি, যার মধ্যে রয়েছে প্রযুক্তি-সমন্বিত শিক্ষা, প্রকল্প-ভিত্তিক কার্যক্রম এবং ব্যক্তিগত মনোযোগ যা প্রতিটি শিক্ষার্থীকে তাদের পূর্ণ সম্ভাবনায় পৌঁছাতে সহায়তা করে।',
            'আমাদের প্রাক্তন ছাত্ররা আমাদের গর্ব এবং নালোপা স্কুলে মানসম্পন্ন শিক্ষার প্রমাণ। আমাদের প্রতিষ্ঠানের স্নাতকরা চিকিৎসা, প্রকৌশল, ব্যবসা, শিল্পকলা, সরকারি সেবা এবং একাডেমিয়া সহ বিভিন্ন ক্ষেত্রে উল্লেখযোগ্য সাফল্য অর্জন করেছে। তারা বর্তমান শিক্ষার্থীদের জন্য অনুপ্রেরণাদায়ক রোল মডেল এবং পরামর্শদাতা হিসাবে কাজ করে, নালোপা স্কুলে তাদের সময়ে অর্জিত মূল্যবোধ, জ্ঞান এবং দক্ষতার দীর্ঘস্থায়ী প্রভাব প্রদর্শন করে।',
        ];

        $icons = [
            'frontend/img/about-gallery/icon_1.webp',
            'frontend/img/about-gallery/icon_2.webp',
            'frontend/img/about-gallery/icon_3.webp',
        ];
        $images = [
            'frontend/img/about-gallery/1.webp',
            'frontend/img/about-gallery/2.webp',
            'frontend/img/about-gallery/3.webp',
        ];
        foreach ($names as $key=>$item) {
            $upload       = new Upload();
            $upload->path = $images[$key];
            $upload->save();

            $icon       = new Upload();
            $icon->path = $icons[$key];
            $icon->save();

            $row                 = new About();
            $row->name           = $item;
            $row->description    = $descriptions[$key];
            $row->upload_id      = $upload->id;
            $row->icon_upload_id = $icon->id;
            $row->serial         = $key;
            $row->save();
        }


        foreach ($names as $key => $en_page) {
            $en = new AboutTranslate();
            $en->about_id = $key+1;
            $en->locale = 'en';
            $en->name = $en_page;
            $en->description = $descriptions[$key];
            $en->save();
        }

        foreach ($bn_names as $key => $bn_page) {
            $bn = new AboutTranslate();
            $bn->about_id = $key+1;
            $bn->locale = 'bn';
            $bn->name = $bn_page;
            $bn->description = $bn_descriptions[$key];
            $bn->save();
        }

    }
}
