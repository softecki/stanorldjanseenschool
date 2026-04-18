<?php

namespace Database\Seeders\WebsiteSetup;

use App\Models\SectionTranslate;
use App\Models\Upload;
use App\Models\WebsiteSetup\PageSections;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $images = [
            'frontend/img/accreditation/accreditation.webp',
            'frontend/img/banner/cta_bg.webp',
            'frontend/img/explore/1.webp',
            'frontend/img/icon/1.svg',
            'frontend/img/icon/2.svg',
            'frontend/img/icon/3.svg',
        ];

        $uploads = [];
        foreach ($images as $key => $value) {
            $row = new Upload();
            $row->path = $value;
            $row->save();

            $uploads[] = $row->id;
        }


        $data = [
            [
                'key'         => 'social_links',
                'name'        => '',
                'description' => '',
                'upload_id'   => null,
                'data'        => [
                    [
                        'name' => 'Facebook',
                        'icon' => 'fab fa-facebook-f',
                        'link' => 'http://www.facebook.com'
                    ],
                    [
                        'name' => 'Twitter',
                        'icon' => 'fab fa-twitter',
                        'link' => 'http://www.twitter.com'
                    ],
                    [
                        'name' => 'Pinterest',
                        'icon' => 'fab fa-pinterest-p',
                        'link' => 'http://www.pinterest.com',
                    ],
                    [
                        'name' => 'Instagram',
                        'icon' => 'fab fa-instagram',
                        'link' => 'http://www.instagram.com',
                    ]
                ],
            ],
            [
                'key'         => 'statement',
                'name'        => 'Our Vision & Mission',
                'description' => 'The Foundation of Excellence at Nalopa School',
                'upload_id'   => $uploads[0],
                'data'        => [
                    [
                        'title'       => 'Our Vision',
                        'description' => 'To be a leading educational institution that inspires excellence, cultivates innovation, and transforms lives. We envision creating future-ready global citizens who are academically accomplished, morally grounded, and socially responsible—equipped with the knowledge, skills, and values to make meaningful contributions to society.',
                    ],
                    [
                        'title'       => 'Our Mission',
                        'description' => 'To provide exceptional, holistic education that empowers every student to discover their unique potential. Through innovative teaching, personalized attention, and a values-driven curriculum, we nurture academic excellence, critical thinking, leadership, creativity, and character—preparing students not just for exams, but for life.',
                    ],
                ],
            ],
            [
                'key'         => 'study_at',
                'name'        => 'Why Choose Nalopa School',
                'description' => 'Experience an educational journey that transforms lives and shapes futures through excellence, innovation, and unwavering commitment to student success',
                'upload_id'   => $uploads[1],
                'data'        => [
                    [
                        'icon'        => $uploads[3],
                        'title'       => 'Academic Excellence',
                        'description' => 'Our rigorous, comprehensive curriculum delivered by highly qualified educators ensures outstanding academic performance. We combine traditional wisdom with modern pedagogy to develop critical thinking, creativity, and a lifelong love for learning.',
                    ],
                    [
                        'icon'        => $uploads[4],
                        'title'       => 'Holistic Development',
                        'description' => 'Beyond academics, we focus on developing well-rounded individuals through sports, arts, leadership programs, and character-building activities. Every student discovers and nurtures their unique talents and passions.',
                    ],
                    [
                        'icon'        => $uploads[5],
                        'title'       => 'Modern Facilities',
                        'description' => 'State-of-the-art infrastructure including smart classrooms, advanced laboratories, comprehensive library, sports facilities, and technology-enabled learning spaces that create an inspiring environment for growth and exploration.',
                    ],
                ],
            ],
            [
                'key'         => 'explore',
                'name'        => 'Explore Nalopa School',
                'description' => 'Discover a world of opportunities where academic excellence meets character development, creating an inspiring environment for every student to thrive',
                'upload_id'   => $uploads[2],
                'data'        => [
                    [
                        'tab' => 'Campus Life',
                        'title' => 'Vibrant Campus Life',
                        'description' => 'Experience a dynamic, inclusive community where students thrive academically, socially, and personally. Our campus buzzes with energy through diverse clubs, cultural events, leadership opportunities, and collaborative projects. Students build lasting friendships, develop social skills, and create cherished memories while learning important values of teamwork, respect, and responsibility in a safe, nurturing environment.'
                    ],
                    [
                        'tab' => 'Academics',
                        'title' => 'Academic Excellence',
                        'description' => 'Our comprehensive, future-focused curriculum combines academic rigor with practical application. Expert teachers employ innovative teaching methods, technology integration, and personalized learning approaches to ensure every student excels. From foundational subjects to specialized programs, we prepare students for higher education and beyond through critical thinking, problem-solving, and continuous assessment that identifies and nurtures each student\'s strengths.'
                    ],
                    [
                        'tab' => 'Sports & Arts',
                        'title' => 'Sports & Creative Arts',
                        'description' => 'Physical fitness and creative expression are integral to holistic development. Our extensive sports programs include football, cricket, basketball, athletics, and more—building teamwork, discipline, and resilience. The arts program nurtures creativity through music, dance, drama, and visual arts. Students discover hidden talents, build confidence, and learn that success comes in many forms beyond traditional academics.'
                    ],
                    [
                        'tab' => 'Facilities',
                        'title' => 'World-Class Facilities',
                        'description' => 'Learn in an environment designed for excellence. Our modern campus features air-conditioned smart classrooms with digital boards, fully-equipped science and computer labs, a vast library with thousands of books, dedicated art and music rooms, indoor and outdoor sports facilities, and secure transport services. Every facility is maintained to the highest standards, creating an inspiring space for learning and growth.'
                    ],
                ],
            ],
            [
                'key'         => 'why_choose_us',
                'name'        => 'Excellence In Teaching And Learning',
                'description' => 'At Nalopa School, excellence is not just a goal—it\'s our daily commitment. Through dedicated educators, proven methodologies, and unwavering support, we create an environment where every student can achieve their fullest potential and develop a genuine passion for lifelong learning.',
                'upload_id'   => null,
                'data'        => [
                    'Expert Faculty: Highly qualified, passionate teachers who inspire and mentor',
                    'Proven Track Record: Consistent excellent results in academics and competitions',
                    'Personalized Attention: Small class sizes ensuring individual focus for every student',
                    'Modern Teaching Methods: Technology-integrated, interactive learning approaches',
                    'Character Development: Focus on values, ethics, leadership, and social responsibility',
                    'Safe Environment: Secure campus with caring staff and comprehensive student welfare'
                ],
            ],
            [
                'key'         => 'academic_curriculum',
                'name'        => 'Comprehensive Academic Programs',
                'description' => 'Nalopa School offers a diverse, rigorous curriculum designed to meet the evolving needs of modern education. Our programs blend traditional academic excellence with contemporary skills, preparing students for success in an ever-changing world while nurturing critical thinking, creativity, and character.',
                'upload_id'   => null,
                'data'        => [
                    'English Medium Program',
                    'National Curriculum',
                    'Science & Technology Focus',
                    'Mathematics Excellence',
                    'Language & Literature',
                    'Social Sciences & Humanities',
                ],
            ],
            [
                'key'         => 'coming_up',
                'name'        => 'Upcoming Events & Activities',
                'description' => 'Stay connected with the vibrant life of Nalopa School through our exciting calendar of events. From academic competitions and cultural celebrations to sports meets and parent-teacher conferences, there\'s always something happening at our dynamic campus community.',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'news',
                'name'        => 'Latest News & Achievements',
                'description' => 'Discover the latest happenings at Nalopa School—from student achievements and academic milestones to community initiatives and school events. Stay informed about the exciting developments and success stories that make our school community proud.',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'our_gallery',
                'name'        => 'Moments of Excellence',
                'description' => 'Experience Nalopa School through our photo gallery showcasing the vibrant moments that define our community—from classroom learning and sports triumphs to cultural celebrations and special events. Every picture tells a story of growth, joy, and achievement.',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'contact_information',
                'name'        => 'Find Our <br> Contact Information',
                'description' => '',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'department_contact_information',
                'name'        => 'Contact By Department',
                'description' => 'Need assistance? Connect with the right department at Nalopa School. Our dedicated team is here to answer your questions, provide information, and support your journey with us. Reach out to admissions, academics, administration, or any specific department for personalized assistance.',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'our_teachers',
                'name'        => 'Meet Our Exceptional Educators',
                'description' => 'Behind every successful student is an inspiring teacher. Our dedicated faculty members are not just educators—they are mentors, role models, and lifelong learners themselves. With diverse expertise, unwavering passion, and genuine care, they create transformative learning experiences that shape young minds and build bright futures.',
                'upload_id'   => null,
                'data'        => [],
            ],
        ];

        $bn_data = [
            [
                'key'         => 'social_links',
                'name'        => '',
                'description' => '',
                'upload_id'   => null,
                'data'        => [
                    [
                        'name' => 'ফেসবুক',
                        'icon' => 'fab fa-facebook-f',
                        'link' => 'http://www.facebook.com'
                    ],
                    [
                        'name' => 'টুইটার',
                        'icon' => 'fab fa-twitter',
                        'link' => 'http://www.twitter.com'
                    ],
                    [
                        'name' => 'Pinterest',
                        'icon' => 'fab fa-pinterest-p',
                        'link' => 'http://www.pinterest.com',
                    ],
                    [
                        'name' => 'ইনস্টাগ্রাম',
                        'icon' => 'fab fa-instagram',
                        'link' => 'http://www.instagram.com',
                    ]
                ],
            ],
            [
                'key'         => 'statement',
                'name'        => 'আমাদের দৃষ্টিভঙ্গি এবং লক্ষ্য',
                'description' => 'নালোপা স্কুলে শ্রেষ্ঠত্বের ভিত্তি',
                'upload_id'   => $uploads[0],
                'data'        => [
                    [
                        'title'       => 'আমাদের দৃষ্টিভঙ্গি',
                        'description' => 'একটি শীর্ষস্থানীয় শিক্ষা প্রতিষ্ঠান হিসেবে গড়ে ওঠা যা শ্রেষ্ঠত্বকে অনুপ্রাণিত করে, উদ্ভাবন লালন করে এবং জীবন রূপান্তরিত করে। আমরা এমন ভবিষ্যত-প্রস্তুত বৈশ্বিক নাগরিক তৈরির স্বপ্ন দেখি যারা একাডেমিকভাবে সফল, নৈতিকভাবে দৃঢ় এবং সামাজিকভাবে দায়বদ্ধ—সমাজে অর্থপূর্ণ অবদান রাখার জ্ঞান, দক্ষতা এবং মূল্যবোধে সুসজ্জিত।',
                    ],
                    [
                        'title'       => 'আমাদের লক্ষ্য',
                        'description' => 'ব্যতিক্রমী, সামগ্রিক শিক্ষা প্রদান করা যা প্রতিটি শিক্ষার্থীকে তাদের অনন্য সম্ভাবনা আবিষ্কার করতে ক্ষমতায়ন করে। উদ্ভাবনী শিক্ষণ, ব্যক্তিগত মনোযোগ এবং মূল্যবোধ-চালিত পাঠ্যক্রমের মাধ্যমে, আমরা একাডেমিক শ্রেষ্ঠত্ব, সমালোচনামূলক চিন্তাভাবনা, নেতৃত্ব, সৃজনশীলতা এবং চরিত্র লালন করি—শিক্ষার্থীদের শুধু পরীক্ষার জন্য নয়, জীবনের জন্য প্রস্তুত করি।',
                    ],
                ],
            ],
            [
                'key'         => 'study_at',
                'name'        => 'কেন নালোপা স্কুল বেছে নেবেন',
                'description' => 'একটি শিক্ষা যাত্রার অভিজ্ঞতা নিন যা জীবন রূপান্তরিত করে এবং শ্রেষ্ঠত্ব, উদ্ভাবন এবং শিক্ষার্থী সাফল্যের প্রতি অটুট প্রতিশ্রুতির মাধ্যমে ভবিষ্যত গঠন করে',
                'upload_id'   => $uploads[1],
                'data'        => [
                    [
                        'icon'        => $uploads[3],
                        'title'       => 'একাডেমিক শ্রেষ্ঠত্ব',
                        'description' => 'উচ্চ যোগ্যতাসম্পন্ন শিক্ষাবিদদের দ্বারা প্রদত্ত আমাদের কঠোর, ব্যাপক পাঠ্যক্রম অসামান্য একাডেমিক কর্মক্ষমতা নিশ্চিত করে। আমরা সমালোচনামূলক চিন্তাভাবনা, সৃজনশীলতা এবং শেখার প্রতি আজীবন ভালোবাসা বিকাশের জন্য ঐতিহ্যগত জ্ঞানকে আধুনিক শিক্ষাবিজ্ঞানের সাথে একত্রিত করি।',
                    ],
                    [
                        'icon'        => $uploads[4],
                        'title'       => 'সামগ্রিক উন্নয়ন',
                        'description' => 'একাডেমিকের বাইরে, আমরা খেলাধুলা, শিল্পকলা, নেতৃত্ব কর্মসূচি এবং চরিত্র-নির্মাণ কার্যক্রমের মাধ্যমে সুসংগত ব্যক্তি তৈরিতে মনোনিবেশ করি। প্রতিটি শিক্ষার্থী তাদের অনন্য প্রতিভা এবং আবেগ আবিষ্কার এবং লালন করে।',
                    ],
                    [
                        'icon'        => $uploads[5],
                        'title'       => 'আধুনিক সুবিধা',
                        'description' => 'স্মার্ট শ্রেণীকক্ষ, উন্নত পরীক্ষাগার, ব্যাপক গ্রন্থাগার, ক্রীড়া সুবিধা এবং প্রযুক্তি-সক্ষম শিক্ষার স্থান সহ অত্যাধুনিক অবকাঠামো যা বৃদ্ধি এবং অন্বেষণের জন্য একটি অনুপ্রেরণাদায়ক পরিবেশ তৈরি করে।',
                    ],
                ],
            ],
            [
                'key'         => 'explore',
                'name'        => 'নালোপা স্কুল অন্বেষণ করুন',
                'description' => 'সুযোগের একটি বিশ্ব আবিষ্কার করুন যেখানে একাডেমিক শ্রেষ্ঠত্ব চরিত্র উন্নয়নের সাথে মিলিত হয়, প্রতিটি শিক্ষার্থীর উন্নতির জন্য একটি অনুপ্রেরণাদায়ক পরিবেশ তৈরি করে',
                'upload_id'   => $uploads[2],
                'data'        => [
                    [
                        'tab' => 'ক্যাম্পাস জীবন',
                        'title' => 'প্রাণবন্ত ক্যাম্পাস জীবন',
                        'description' => 'একটি গতিশীল, অন্তর্ভুক্তিমূলক সম্প্রদায়ের অভিজ্ঞতা নিন যেখানে শিক্ষার্থীরা একাডেমিক, সামাজিক এবং ব্যক্তিগতভাবে সমৃদ্ধ হয়। বিভিন্ন ক্লাব, সাংস্কৃতিক অনুষ্ঠান, নেতৃত্বের সুযোগ এবং সহযোগিতামূলক প্রকল্পের মাধ্যমে আমাদের ক্যাম্পাস শক্তিতে গুঞ্জন করে। শিক্ষার্থীরা দীর্ঘস্থায়ী বন্ধুত্ব গড়ে, সামাজিক দক্ষতা বিকাশ করে এবং একটি নিরাপদ, লালনশীল পরিবেশে দলগত কাজ, সম্মান এবং দায়িত্বের গুরুত্বপূর্ণ মূল্যবোধ শেখার সময় মূল্যবান স্মৃতি তৈরি করে।'
                    ],
                    [
                        'tab' => 'একাডেমিক',
                        'title' => 'একাডেমিক শ্রেষ্ঠত্ব',
                        'description' => 'আমাদের ব্যাপক, ভবিষ্যত-কেন্দ্রিক পাঠ্যক্রম বাস্তব প্রয়োগের সাথে একাডেমিক কঠোরতা একত্রিত করে। বিশেষজ্ঞ শিক্ষকরা প্রতিটি শিক্ষার্থী যাতে শ্রেষ্ঠত্ব অর্জন করে তা নিশ্চিত করতে উদ্ভাবনী শিক্ষণ পদ্ধতি, প্রযুক্তি একীকরণ এবং ব্যক্তিগত শেখার পদ্ধতি নিযুক্ত করেন। মৌলিক বিষয় থেকে বিশেষায়িত কর্মসূচি পর্যন্ত, আমরা সমালোচনামূলক চিন্তাভাবনা, সমস্যা সমাধান এবং ক্রমাগত মূল্যায়নের মাধ্যমে শিক্ষার্থীদের উচ্চ শিক্ষা এবং তার বাইরের জন্য প্রস্তুত করি যা প্রতিটি শিক্ষার্থীর শক্তি চিহ্নিত এবং লালন করে।'
                    ],
                    [
                        'tab' => 'খেলাধুলা এবং শিল্প',
                        'title' => 'খেলাধুলা এবং সৃজনশীল শিল্পকলা',
                        'description' => 'শারীরিক সুস্থতা এবং সৃজনশীল অভিব্যক্তি সামগ্রিক উন্নয়নের অবিচ্ছেদ্য অংশ। আমাদের বিস্তৃত ক্রীড়া কর্মসূচিতে ফুটবল, ক্রিকেট, বাস্কেটবল, অ্যাথলেটিক্স এবং আরও অনেক কিছু অন্তর্ভুক্ত রয়েছে—যা দলগত কাজ, শৃঙ্খলা এবং স্থিতিস্থাপকতা তৈরি করে। শিল্প কর্মসূচি সঙ্গীত, নৃত্য, নাটক এবং চাক্ষুষ শিল্পের মাধ্যমে সৃজনশীলতা লালন করে। শিক্ষার্থীরা লুকানো প্রতিভা আবিষ্কার করে, আত্মবিশ্বাস তৈরি করে এবং শিখে যে ঐতিহ্যবাহী একাডেমিকের বাইরে সাফল্য অনেক রূপে আসে।'
                    ],
                    [
                        'tab' => 'সুবিধাসমূহ',
                        'title' => 'বিশ্বমানের সুবিধা',
                        'description' => 'শ্রেষ্ঠত্বের জন্য ডিজাইন করা একটি পরিবেশে শিখুন। আমাদের আধুনিক ক্যাম্পাসে ডিজিটাল বোর্ড সহ এয়ার-কন্ডিশনড স্মার্ট ক্লাসরুম, সম্পূর্ণ সজ্জিত বিজ্ঞান এবং কম্পিউটার ল্যাব, হাজার হাজার বই সহ একটি বিশাল লাইব্রেরি, নিবেদিত শিল্প এবং সঙ্গীত কক্ষ, অন্দর এবং বহিরঙ্গন খেলাধুলা সুবিধা এবং নিরাপদ পরিবহন পরিষেবা রয়েছে। প্রতিটি সুবিধা সর্বোচ্চ মানের জন্য রক্ষণাবেক্ষণ করা হয়, যা শেখার এবং বৃদ্ধির জন্য একটি অনুপ্রেরণাদায়ক স্থান তৈরি করে।'
                    ],
                ],
            ],
            [
                'key'         => 'why_choose_us',
                'name'        => 'শিক্ষাদান এবং শেখার ক্ষেত্রে শ্রেষ্ঠত্ব',
                'description' => 'নালোপা স্কুলে, শ্রেষ্ঠত্ব কেবল একটি লক্ষ্য নয়—এটি আমাদের দৈনন্দিন প্রতিশ্রুতি। নিবেদিত শিক্ষাবিদ, প্রমাণিত পদ্ধতি এবং অটুট সমর্থনের মাধ্যমে, আমরা এমন একটি পরিবেশ তৈরি করি যেখানে প্রতিটি শিক্ষার্থী তাদের সম্পূর্ণ সম্ভাবনা অর্জন করতে পারে এবং আজীবন শেখার জন্য একটি প্রকৃত আবেগ বিকাশ করতে পারে।',
                'upload_id'   => null,
                'data'        => [
                    'বিশেষজ্ঞ শিক্ষকমণ্ডলী: উচ্চ যোগ্য, উত্সাহী শিক্ষক যারা অনুপ্রাণিত এবং পরামর্শ দেন',
                    'প্রমাণিত ট্র্যাক রেকর্ড: একাডেমিক এবং প্রতিযোগিতায় ধারাবাহিক উৎকৃষ্ট ফলাফল',
                    'ব্যক্তিগত মনোযোগ: ছোট শ্রেণীর আকার প্রতিটি শিক্ষার্থীর জন্য ব্যক্তিগত ফোকাস নিশ্চিত করে',
                    'আধুনিক শিক্ষণ পদ্ধতি: প্রযুক্তি-সমন্বিত, ইন্টারেক্টিভ শিক্ষার পদ্ধতি',
                    'চরিত্র উন্নয়ন: মূল্যবোধ, নৈতিকতা, নেতৃত্ব এবং সামাজিক দায়বদ্ধতার উপর ফোকাস',
                    'নিরাপদ পরিবেশ: যত্নশীল কর্মীদের সাথে নিরাপদ ক্যাম্পাস এবং ব্যাপক শিক্ষার্থী কল্যাণ'
                ],
            ],
            [
                'key'         => 'academic_curriculum',
                'name'        => 'ব্যাপক একাডেমিক প্রোগ্রাম',
                'description' => 'নালোপা স্কুল আধুনিক শিক্ষার বিকশিত চাহিদা পূরণের জন্য ডিজাইন করা একটি বৈচিত্র্যময়, কঠোর পাঠ্যক্রম প্রদান করে। আমাদের প্রোগ্রামগুলি সমসাময়িক দক্ষতার সাথে ঐতিহ্যবাহী একাডেমিক শ্রেষ্ঠত্ব মিশ্রিত করে, শিক্ষার্থীদের একটি চির-পরিবর্তনশীল বিশ্বে সাফল্যের জন্য প্রস্তুত করে এবং সমালোচনামূলক চিন্তাভাবনা, সৃজনশীলতা এবং চরিত্র লালন করে।',
                'upload_id'   => null,
                'data'        => [
                    'ইংরেজি মাধ্যম প্রোগ্রাম',
                    'জাতীয় পাঠ্যক্রম',
                    'বিজ্ঞান ও প্রযুক্তি ফোকাস',
                    'গণিতে শ্রেষ্ঠত্ব',
                    'ভাষা এবং সাহিত্য',
                    'সামাজিক বিজ্ঞান এবং মানবিক',
                ],
            ],
            [
                'key'         => 'coming_up',
                'name'        => 'আসন্ন ইভেন্ট এবং কার্যক্রম',
                'description' => 'আমাদের উত্তেজনাপূর্ণ ইভেন্ট ক্যালেন্ডারের মাধ্যমে নালোপা স্কুলের প্রাণবন্ত জীবনের সাথে সংযুক্ত থাকুন। একাডেমিক প্রতিযোগিতা এবং সাংস্কৃতিক উদযাপন থেকে খেলাধুলা মিট এবং অভিভাবক-শিক্ষক সম্মেলন পর্যন্ত, আমাদের গতিশীল ক্যাম্পাস সম্প্রদায়ে সর্বদা কিছু না কিছু ঘটছে।',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'news',
                'name'        => 'সর্বশেষ সংবাদ এবং অর্জন',
                'description' => 'নালোপা স্কুলে সর্বশেষ ঘটনাগুলি আবিষ্কার করুন—শিক্ষার্থী অর্জন এবং একাডেমিক মাইলফলক থেকে সম্প্রদায় উদ্যোগ এবং স্কুল ইভেন্ট পর্যন্ত। উত্তেজনাপূর্ণ উন্নয়ন এবং সাফল্যের গল্পগুলি সম্পর্কে অবগত থাকুন যা আমাদের স্কুল সম্প্রদায়কে গর্বিত করে।',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'our_gallery',
                'name'        => 'শ্রেষ্ঠত্বের মুহূর্ত',
                'description' => 'আমাদের ফটো গ্যালারির মাধ্যমে নালোপা স্কুলের অভিজ্ঞতা নিন যা আমাদের সম্প্রদায়কে সংজ্ঞায়িত করে এমন প্রাণবন্ত মুহূর্তগুলি প্রদর্শন করে—শ্রেণীকক্ষ শেখা এবং ক্রীড়া বিজয় থেকে সাংস্কৃতিক উদযাপন এবং বিশেষ ইভেন্ট পর্যন্ত। প্রতিটি ছবি বৃদ্ধি, আনন্দ এবং অর্জনের একটি গল্প বলে।',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'contact_information',
                'name'        => 'আমাদের যোগাযোগের তথ্য খুঁজুন',
                'description' => '',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'department_contact_information',
                'name'        => 'বিভাগ অনুযায়ী যোগাযোগ',
                'description' => 'সহায়তা প্রয়োজন? নালোপা স্কুলে সঠিক বিভাগের সাথে সংযুক্ত হন। আমাদের নিবেদিত টিম আপনার প্রশ্নের উত্তর দিতে, তথ্য প্রদান করতে এবং আমাদের সাথে আপনার যাত্রা সমর্থন করতে এখানে আছে। ব্যক্তিগত সহায়তার জন্য ভর্তি, একাডেমিক, প্রশাসন বা কোনো নির্দিষ্ট বিভাগে যোগাযোগ করুন।',
                'upload_id'   => null,
                'data'        => [],
            ],
            [
                'key'         => 'our_teachers',
                'name'        => 'আমাদের ব্যতিক্রমী শিক্ষাবিদদের সাথে দেখা করুন',
                'description' => 'প্রতিটি সফল শিক্ষার্থীর পেছনে একজন অনুপ্রেরণাদায়ক শিক্ষক আছেন। আমাদের নিবেদিত শিক্ষকমণ্ডলী শুধুমাত্র শিক্ষাবিদ নন—তারা পরামর্শদাতা, রোল মডেল এবং নিজেরাও আজীবন শিক্ষার্থী। বৈচিত্র্যময় দক্ষতা, অটুট আবেগ এবং প্রকৃত যত্ন সহ, তারা রূপান্তরকারী শেখার অভিজ্ঞতা তৈরি করে যা তরুণ মনকে আকার দেয় এবং উজ্জ্বল ভবিষ্যত তৈরি করে।',
                'upload_id'   => null,
                'data'        => [],
            ],
        ];


        foreach ($data as $key => $value){
            $row              = new PageSections();
            $row->key         = $value['key'];
            $row->name        = $value['name'];
            $row->description = $value['description'];
            $row->upload_id   = $value['upload_id'];
            $row->data        = $value['data'];
            $row->save();
        }

        foreach ($data as $key => $en_item) {
            $en = new SectionTranslate();
            $en->section_id = $key+1;
            $en->locale = 'en';
            $en->name = $en_item['name'];
            $en->description = $en_item['description'];
            $en->data        = json_encode($en_item['data']);
            $en->save();
        }

        foreach ($bn_data as $key => $bn_item) {
            $bn = new SectionTranslate();
            $bn->section_id = $key+1;
            $bn->locale = 'bn';
            $bn->name = $bn_item['name'];
            $bn->description = $bn_item['description'];
            $bn->data        = json_encode($bn_item['data']);
            $bn->save();
        }

    }
}
