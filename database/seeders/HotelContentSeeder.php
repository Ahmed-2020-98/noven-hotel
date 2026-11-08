<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Faq;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Starter content for NOVEN. Placeholder values (address, phones, prices, room counts)
 * must be replaced with the client's real data from the dashboard.
 */
class HotelContentSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::updateOrCreate(['slug' => 'noven-riyadh'], [
            'name_ar' => 'فندق نوفين',
            'name_en' => 'NOVEN Hotel',
            'city_ar' => 'الرياض',
            'city_en' => 'Riyadh',
            'address_ar' => 'الرياض، المملكة العربية السعودية',
            'address_en' => 'Riyadh, Saudi Arabia',
            'phone' => '+966 11 000 0000',
            'whatsapp' => '+966 50 000 0000',
            'email' => 'reservations@noven.test',
            'map_embed_url' => 'https://www.google.com/maps?q=24.7136,46.6753&z=13&output=embed',
            'map_link' => 'https://maps.google.com/?q=24.7136,46.6753',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'check_in_time' => '15:00',
            'check_out_time' => '12:00',
        ]);

        $amenities = collect([
            ['wifi', 'واي فاي مجاني', 'Free Wi-Fi'],
            ['sun', 'تكييف', 'Air conditioning'],
            ['tv', 'شاشة تلفزيون ذكية', 'Smart TV'],
            ['cube', 'ميني بار', 'Minibar'],
            ['sparkles', 'دورة مياه خاصة', 'Private bathroom'],
            ['lock-closed', 'خزنة إلكترونية', 'In-room safe'],
            ['beaker', 'ركن قهوة وشاي', 'Coffee & tea corner'],
            ['briefcase', 'مكتب عمل', 'Work desk'],
        ])->map(fn ($a, $i) => Amenity::updateOrCreate(['name_en' => $a[2]], ['icon' => $a[0], 'name_ar' => $a[1], 'sort_order' => $i]));

        $rooms = [
            ['superior-room', 'غرفة سوبيريور', 'Superior Room', 28, 2, 1, 'سرير كينج', 'King bed', 350, 420, 8],
            ['deluxe-double', 'غرفة ديلوكس مزدوجة', 'Deluxe Double Room', 28, 2, 1, 'سرير كينج', 'King bed', 420, 490, 10],
            ['deluxe-twin', 'غرفة ديلوكس توين', 'Deluxe Twin Room', 28, 2, 1, 'سريران منفصلان', 'Two single beds', 420, 490, 6],
            ['junior-suite', 'جناح جونيور', 'Junior Suite', 40, 3, 1, 'سرير كينج + أريكة', 'King bed + sofa', 600, 700, 4],
            ['king-suite', 'جناح كينج', 'King Suite', 50, 4, 2, 'سرير كينج + صالة', 'King bed + living room', 850, 950, 2],
        ];

        foreach ($rooms as $i => [$slug, $ar, $en, $size, $adults, $kids, $bedsAr, $bedsEn, $price, $weekend, $units]) {
            $room = RoomType::updateOrCreate(['slug' => $slug], [
                'hotel_id' => $hotel->id,
                'name_ar' => $ar,
                'name_en' => $en,
                'summary_ar' => "مساحة {$size} م² بتصميم هادئ وكل ما تحتاجه لإقامة مريحة.",
                'summary_en' => "{$size} m² of calm design with everything you need for a comfortable stay.",
                'description_ar' => "تجمع {$ar} بين الراحة والأناقة بألوان هادئة وإضاءة دافئة، مع سرير مريح ودورة مياه خاصة ومكتب عمل وإنترنت سريع مجاني. مثالية لرحلات العمل والعائلات.",
                'description_en' => "The {$en} blends comfort and elegance with calm tones and warm lighting, a comfortable bed, private bathroom, work desk and free high-speed internet. Ideal for business and family trips.",
                'beds_ar' => $bedsAr,
                'beds_en' => $bedsEn,
                'size_sqm' => $size,
                'max_adults' => $adults,
                'max_children' => $kids,
                'base_price' => $price,
                'weekend_price' => $weekend,
                'units_count' => $units,
                'sort_order' => $i,
            ]);
            $room->amenities()->sync($amenities->pluck('id'));
        }

        $services = [
            ['cake', 'إفطار يومي', 'Daily breakfast', 'إفطار قاري متنوع كل صباح.', 'A varied continental breakfast every morning.'],
            ['beaker', 'ركن القهوة', 'Coffee lounge', 'قهوة سعودية وشاي ووجبات خفيفة بعد الظهر.', 'Saudi coffee, tea and snacks every afternoon.'],
            ['clock', 'استقبال 24/7', '24/7 front desk', 'فريقنا في خدمتك على مدار الساعة.', 'Our team is at your service around the clock.'],
            ['wifi', 'إنترنت سريع', 'High-speed internet', 'واي فاي مجاني في كل أرجاء الفندق.', 'Free Wi-Fi throughout the hotel.'],
            ['truck', 'مواقف سيارات', 'Parking', 'مواقف مجانية للنزلاء.', 'Free parking for guests.'],
            ['user-group', 'قاعة اجتماعات', 'Meeting room', 'قاعة مجهزة لاجتماعات العمل الصغيرة.', 'An equipped room for small business meetings.'],
        ];
        foreach ($services as $i => [$icon, $ar, $en, $dAr, $dEn]) {
            Service::updateOrCreate(['title_en' => $en], ['icon' => $icon, 'title_ar' => $ar, 'description_ar' => $dAr, 'description_en' => $dEn, 'sort_order' => $i]);
        }

        $faqs = [
            ['ما مواعيد تسجيل الدخول والخروج؟', 'What are the check-in and check-out times?', 'تسجيل الدخول من الساعة 3 عصرًا وتسجيل الخروج حتى 12 ظهرًا.', 'Check-in is from 3 PM and check-out is until 12 PM.'],
            ['هل يلزم الدفع عند إرسال طلب الحجز؟', 'Do I need to pay when sending a booking request?', 'لا، يتواصل معك فريق الاستقبال لتأكيد الحجز ويتم الدفع عند الوصول.', 'No. Our front desk contacts you to confirm, and payment is made on arrival.'],
            ['هل يتوفر موقف سيارات؟', 'Is parking available?', 'نعم، تتوفر مواقف مجانية للنزلاء.', 'Yes, free parking is available for guests.'],
            ['هل يمكن إلغاء الحجز؟', 'Can I cancel my booking?', 'يمكن الإلغاء مجانًا قبل 24 ساعة من موعد الوصول.', 'Free cancellation up to 24 hours before arrival.'],
        ];
        foreach ($faqs as $i => [$qAr, $qEn, $aAr, $aEn]) {
            Faq::updateOrCreate(['question_en' => $qEn], ['question_ar' => $qAr, 'answer_ar' => $aAr, 'answer_en' => $aEn, 'sort_order' => $i]);
        }

        Setting::put([
            'about_ar' => '<p>نوفين فندق سعودي بروح عصرية، يجمع بين دفء الضيافة السعودية وهدوء التصميم الحديث. اسمنا مستوحى من الشمس التي تشرق كل يوم بداية جديدة، ولهذا نسعى أن تكون كل إقامة عندنا بداية مريحة لرحلتك.</p><p>نقدّم غرفًا وأجنحة مجهزة بعناية، وخدمة استقبال على مدار الساعة، وموقعًا يسهّل وصولك لأهم معالم المدينة.</p>',
            'about_en' => '<p>NOVEN is a Saudi hotel with a modern spirit, combining the warmth of Saudi hospitality with the calm of contemporary design. Our name is inspired by the sun that rises every day as a new beginning, and we want every stay with us to be a comfortable start to your journey.</p><p>We offer carefully equipped rooms and suites, a 24/7 front desk, and a location that makes the city easy to reach.</p>',
            'policies_ar' => '<ul><li>تسجيل الدخول من 3 عصرًا، والخروج حتى 12 ظهرًا.</li><li>يلزم تقديم هوية سارية عند الوصول.</li><li>الإلغاء مجاني قبل 24 ساعة من الوصول.</li><li>الأسعار شاملة ضريبة القيمة المضافة 15%.</li></ul>',
            'policies_en' => '<ul><li>Check-in from 3 PM, check-out until 12 PM.</li><li>A valid ID is required on arrival.</li><li>Free cancellation up to 24 hours before arrival.</li><li>Prices include 15% VAT.</li></ul>',
            'privacy_ar' => '<p>نحترم خصوصيتك. نستخدم البيانات التي ترسلها (الاسم، رقم الجوال، البريد الإلكتروني) فقط لمعالجة طلب الحجز والتواصل معك، ولا نشاركها مع أي طرف ثالث إلا عند الحاجة القانونية.</p>',
            'privacy_en' => '<p>We respect your privacy. The data you send (name, mobile, email) is used only to process your booking request and contact you, and is not shared with third parties except where required by law.</p>',
            'terms_ar' => '<p>طلب الحجز عبر الموقع لا يعد حجزًا مؤكدًا حتى يتم تأكيده من فريق الاستقبال. الأسعار المعروضة تقديرية وقد تتغير حسب المواسم والتوفر.</p>',
            'terms_en' => '<p>A booking request on the website is not a confirmed reservation until our front desk confirms it. Displayed prices are estimates and may change with seasons and availability.</p>',
            'social_instagram' => '',
            'social_x' => '',
            'social_snapchat' => '',
            'social_tiktok' => '',
        ]);
    }
}
