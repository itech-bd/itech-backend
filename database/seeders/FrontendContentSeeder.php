<?php

namespace Database\Seeders;

use App\Models\FrontendPage;
use App\Models\FrontendSection;
use Illuminate\Database\Seeder;

/**
 * Seeds initial multilingual frontend CMS content.
 *
 * @category Database
 * @package  Database\Seeders
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class FrontendContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $pages = [
            'home' => [
                [
                    'section_key' => 'hero_primary',
                    'title_en' => 'Learn with Experts',
                    'title_bn' => 'বিশেষজ্ঞদের সাথে শিখুন',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'hero_emphasis',
                    'title_en' => 'Build Real Projects',
                    'title_bn' => 'বাস্তব প্রজেক্ট তৈরি করুন',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'hero_paragraph',
                    'content_en' => implode(
                        ' ',
                        [
                            'Career-focused training with mentor guidance,',
                            'weekly reviews, and portfolio-ready projects.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'ক্যারিয়ার-ফোকাসড ট্রেনিং, মেন্টর গাইডেন্স,',
                            'সাপ্তাহিক রিভিউ এবং পোর্টফোলিও-রেডি প্রজেক্ট।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'hero_cta_primary',
                    'button_text_en' => 'Explore Courses',
                    'button_text_bn' => 'কোর্স দেখুন',
                    'button_link' => '/courses',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'about' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'About Us',
                    'title_bn' => 'আমাদের সম্পর্কে',
                    'content_en' => implode(
                        ' ',
                        [
                            'We help learners build real-world skills',
                            'with mentor-led learning, structured modules,',
                            'and project-based outcomes.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'আমরা মেন্টর-লেড লার্নিং, স্ট্রাকচার্ড মডিউল এবং',
                            'প্রজেক্ট-বেইজড আউটকামের মাধ্যমে বাস্তব স্কিল গড়ে',
                            'তুলতে সাহায্য করি।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_intro',
                    'title_en' => 'Who we are',
                    'title_bn' => 'আমরা কারা',
                    'content_en' => implode(
                        ' ',
                        [
                            'We are a career-focused learning platform built to help learners',
                            'go from fundamentals to portfolio-ready projects with mentor guidance.',
                            'Our approach is practical, structured, and focused on outcomes.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'আমরা একটি ক্যারিয়ার-ফোকাসড লার্নিং প্ল্যাটফর্ম—যা শিক্ষার্থীদের',
                            'বেসিক থেকে পোর্টফোলিও-রেডি প্রজেক্ট পর্যন্ত মেন্টর গাইডেন্সে এগিয়ে যেতে সাহায্য করে।',
                            'আমাদের ফোকাস প্র্যাকটিক্যাল, স্ট্রাকচার্ড এবং আউটকাম-ড্রিভেন।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_mission',
                    'title_en' => 'Our mission',
                    'title_bn' => 'আমাদের মিশন',
                    'content_en' => 'Make skill-building practical, structured, and outcome-driven for every learner.',
                    'content_bn' => 'প্রতিটি শিক্ষার্থীর জন্য স্কিল-বিল্ডিংকে প্র্যাকটিক্যাল, স্ট্রাকচার্ড এবং আউটকাম-ড্রিভেন করা।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_vision',
                    'title_en' => 'Our vision',
                    'title_bn' => 'আমাদের ভিশন',
                    'content_en' => 'Build a community of job-ready professionals and successful freelancers through portfolio-first learning.',
                    'content_bn' => 'পোর্টফোলিও-ফার্স্ট লার্নিং-এর মাধ্যমে জব-রেডি প্রফেশনাল ও সফল ফ্রিল্যান্সারদের কমিউনিটি তৈরি করা।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_value_1',
                    'title_en' => 'Mentor-led learning',
                    'title_bn' => 'মেন্টর-লেড লার্নিং',
                    'content_en' => 'Weekly guidance, reviews, and a structured path from fundamentals to advanced skills.',
                    'content_bn' => 'সাপ্তাহিক গাইডেন্স, রিভিউ এবং বেসিক থেকে অ্যাডভান্সড পর্যন্ত স্ট্রাকচার্ড পাথ।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_value_2',
                    'title_en' => 'Portfolio first',
                    'title_bn' => 'পোর্টফোলিও ফার্স্ট',
                    'content_en' => 'Projects that prove your ability and help you stand out to employers and clients.',
                    'content_bn' => 'আপনার স্কিল প্রমাণ করে এমন প্রজেক্ট—যা আপনাকে আলাদা করে তুলে ধরে।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_value_3',
                    'title_en' => 'Career support',
                    'title_bn' => 'ক্যারিয়ার সাপোর্ট',
                    'content_en' => 'CV + interview practice with feedback to help you become job-ready.',
                    'content_bn' => 'সিভি + ইন্টারভিউ প্র্যাকটিস ও ফিডব্যাক—জব-রেডি হতে সাহায্য করবে।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_value_4',
                    'title_en' => 'Freelancing support',
                    'title_bn' => 'ফ্রিল্যান্সিং সাপোর্ট',
                    'content_en' => 'Profile + proposal + client communication guidance for real-world freelancing.',
                    'content_bn' => 'প্রোফাইল + প্রপোজাল + ক্লায়েন্ট কমিউনিকেশন—রিয়েল-ওয়ার্ল্ড ফ্রিল্যান্সিং-এর জন্য।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_value_5',
                    'title_en' => 'Structured roadmap',
                    'title_bn' => 'স্ট্রাকচার্ড রোডম্যাপ',
                    'content_en' => 'Milestones and tasks that keep you consistent and focused on outcomes.',
                    'content_bn' => 'মাইলস্টোন ও টাস্ক—যা ধারাবাহিকতা ধরে রাখতে সাহায্য করে।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_value_6',
                    'title_en' => 'Community & accountability',
                    'title_bn' => 'কমিউনিটি ও অ্যাকাউন্টেবিলিটি',
                    'content_en' => 'Stay motivated with peer support, community, and regular check-ins.',
                    'content_bn' => 'পিয়ার সাপোর্ট, কমিউনিটি এবং নিয়মিত চেক-ইন—মোটিভেশন ধরে রাখতে সাহায্য করবে।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_stats_title',
                    'title_en' => 'Learning outcomes',
                    'title_bn' => 'লার্নিং আউটকাম',
                    'content_en' => 'Progress you can measure — skills you can demonstrate.',
                    'content_bn' => 'যে প্রগ্রেস মাপা যায়—যে স্কিল দেখানো যায়।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_stat_1',
                    'title_en' => 'Mentor-led',
                    'title_bn' => 'মেন্টর-লেড',
                    'content_en' => 'Guidance & reviews',
                    'content_bn' => 'গাইডেন্স ও রিভিউ',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_stat_2',
                    'title_en' => 'Project-based',
                    'title_bn' => 'প্রজেক্ট-বেইজড',
                    'content_en' => 'Portfolio outcomes',
                    'content_bn' => 'পোর্টফোলিও আউটকাম',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_stat_3',
                    'title_en' => 'Weekly',
                    'title_bn' => 'সাপ্তাহিক',
                    'content_en' => 'Consistency & milestones',
                    'content_bn' => 'ধারাবাহিকতা ও মাইলস্টোন',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_stat_4',
                    'title_en' => 'Career-ready',
                    'title_bn' => 'ক্যারিয়ার-রেডি',
                    'content_en' => 'Jobs & freelancing',
                    'content_bn' => 'চাকরি ও ফ্রিল্যান্সিং',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'about_cta',
                    'title_en' => 'Ready to start learning?',
                    'title_bn' => 'শুরু করতে প্রস্তুত?',
                    'content_en' => 'Explore courses, pick a skill track, and start building your portfolio with mentor support.',
                    'content_bn' => 'কোর্স দেখুন, একটি স্কিল ট্র্যাক বেছে নিন, এবং মেন্টর সাপোর্টের সাথে আপনার পোর্টফোলিও তৈরি শুরু করুন।',
                    'button_text_en' => 'Explore Courses',
                    'button_text_bn' => 'কোর্স দেখুন',
                    'button_link' => '/courses',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'courses' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'Courses',
                    'title_bn' => 'কোর্সসমূহ',
                    'content_en' => implode(
                        ' ',
                        [
                            'Choose a path and start building your portfolio',
                            'with guided practice and real projects.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'একটি পথ বেছে নিন এবং গাইডেড প্র্যাকটিস ও',
                            'বাস্তব প্রজেক্টের মাধ্যমে আপনার পোর্টফোলিও',
                            'তৈরি শুরু করুন।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'contact' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'Contact',
                    'title_bn' => 'যোগাযোগ',
                    'content_en' => implode(
                        ' ',
                        [
                            'Have questions? Reach out and we will get back to you',
                            'as soon as possible.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'কোনো প্রশ্ন আছে? যোগাযোগ করুন, আমরা দ্রুত',
                            'উত্তর দেওয়ার চেষ্টা করব।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'contact_email',
                    'title_en' => 'Email',
                    'title_bn' => 'ইমেইল',
                    'content_en' => 'info@example.com',
                    'content_bn' => 'info@example.com',
                    'button_link' => 'mailto:info@example.com',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
                [
                    'section_key' => 'contact_phone',
                    'title_en' => 'Phone',
                    'title_bn' => 'ফোন',
                    'content_en' => '+880 10 0000 0000',
                    'content_bn' => '+880 10 0000 0000',
                    'button_link' => 'tel:+8801000000000',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'mentors' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'Mentors',
                    'title_bn' => 'মেন্টরস',
                    'content_en' => implode(
                        ' ',
                        [
                            'Meet mentors from different topics',
                            'and learn with weekly guidance.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'বিভিন্ন টপিকের মেন্টরদের সাথে পরিচিত হন এবং',
                            'সাপ্তাহিক গাইডেন্সের মাধ্যমে শিখুন।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'news' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'News',
                    'title_bn' => 'নিউজ',
                    'content_en' => 'Latest updates, workshops, and announcements.',
                    'content_bn' => 'সর্বশেষ আপডেট, ওয়ার্কশপ এবং ঘোষণা।',
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'reviews' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'Reviews',
                    'title_bn' => 'রিভিউ',
                    'content_en' => implode(
                        ' ',
                        [
                            'What learners say about our mentoring and courses.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'আমাদের মেন্টরিং এবং কোর্স সম্পর্কে',
                            'শিক্ষার্থীদের মতামত।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'privacy' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'Privacy Policy',
                    'title_bn' => 'প্রাইভেসি পলিসি',
                    'content_en' => implode(
                        ' ',
                        [
                            'Replace this placeholder text',
                            'with your real privacy policy.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'এই প্লেসহোল্ডার টেক্সটটি আপনার',
                            'আসল প্রাইভেসি পলিসি দিয়ে বদলে দিন।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
            'terms' => [
                [
                    'section_key' => 'hero',
                    'title_en' => 'Terms & Conditions',
                    'title_bn' => 'শর্তাবলী',
                    'content_en' => implode(
                        ' ',
                        [
                            'Replace this placeholder text',
                            'with your real terms and conditions.',
                        ]
                    ),
                    'content_bn' => implode(
                        ' ',
                        [
                            'এই প্লেসহোল্ডার টেক্সটটি আপনার',
                            'আসল শর্তাবলী দিয়ে বদলে দিন।',
                        ]
                    ),
                    'status' => FrontendSection::STATUS_ACTIVE,
                ],
            ],
        ];

        foreach ($pages as $slug => $sections) {
            $page = FrontendPage::query()->firstOrCreate(['slug' => $slug]);

            foreach ($sections as $section) {
                FrontendSection::query()->updateOrCreate(
                    [
                        'frontend_page_id' => $page->id,
                        'section_key' => $section['section_key'],
                    ],
                    array_merge($section, ['frontend_page_id' => $page->id])
                );
            }
        }
    }
}
