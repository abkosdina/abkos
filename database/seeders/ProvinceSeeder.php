<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provincesCities = [
            ['province' => 'آذربایجان شرقی', 'cities' => ['تبریز', 'مراغه', 'مرند']],
            ['province' => 'آذربایجان غربی', 'cities' => ['ارومیه', 'خوی', 'مهاباد']],
            ['province' => 'اردبیل', 'cities' => ['اردبیل', 'مشگین‌شهر', 'پارس‌آباد']],
            ['province' => 'اصفهان', 'cities' => ['اصفهان', 'کاشان', 'نجف‌آباد']],
            ['province' => 'البرز', 'cities' => ['کرج', 'هشتگرد', 'نظرآباد']],
            ['province' => 'ایلام', 'cities' => ['ایلام', 'دهلران', 'ایوان']],
            ['province' => 'بوشهر', 'cities' => ['بوشهر', 'برازجان', 'گناوه']],
            ['province' => 'تهران', 'cities' => ['تهران', 'کرج', 'ورامین']],
            ['province' => 'چهارمحال و بختیاری', 'cities' => ['شهرکرد', 'بروجن', 'لردگان']],
            ['province' => 'خراسان جنوبی', 'cities' => ['بیرجند', 'قائن', 'فردوس']],
            ['province' => 'خراسان رضوی', 'cities' => ['مشهد', 'نیشابور', 'سبزوار']],
            ['province' => 'خراسان شمالی', 'cities' => ['بجنورد', 'شیروان', 'اسفراین']],
            ['province' => 'خوزستان', 'cities' => ['اهواز', 'آبادان', 'دزفول']],
            ['province' => 'زنجان', 'cities' => ['زنجان', 'ابهر', 'خرمدره']],
            ['province' => 'سمنان', 'cities' => ['سمنان', 'شاهرود', 'دامغان']],
            ['province' => 'سیستان و بلوچستان', 'cities' => ['زاهدان', 'زابل', 'چابهار']],
            ['province' => 'فارس', 'cities' => ['شیراز', 'مرودشت', 'جهرم']],
            ['province' => 'قزوین', 'cities' => ['قزوین', 'البرز', 'تاکستان']],
            ['province' => 'قم', 'cities' => ['قم']],
            ['province' => 'کردستان', 'cities' => ['سنندج', 'سقز', 'بانه']],
            ['province' => 'کرمان', 'cities' => ['کرمان', 'رفسنجان', 'سیرجان']],
            ['province' => 'کرمانشاه', 'cities' => ['کرمانشاه', 'اسلام‌آباد غرب', 'کنگاور']],
            ['province' => 'کهگیلویه و بویراحمد', 'cities' => ['یاسوج', 'دهدشت', 'گچساران']],
            ['province' => 'گلستان', 'cities' => ['گرگان', 'گنبد کاووس', 'علی‌آباد']],
            ['province' => 'گیلان', 'cities' => ['رشت', 'انزلی', 'لاهیجان']],
            ['province' => 'لرستان', 'cities' => ['خرم‌آباد', 'بروجرد', 'دورود']],
            ['province' => 'مازندران', 'cities' => ['ساری', 'بابل', 'آمل']],
            ['province' => 'مرکزی', 'cities' => ['اراک', 'ساوه', 'خمین']],
            ['province' => 'هرمزگان', 'cities' => ['بندرعباس', 'میناب', 'قشم']],
            ['province' => 'همدان', 'cities' => ['همدان', 'ملایر', 'نهاوند']],
            ['province' => 'یزد', 'cities' => ['یزد', 'میبد', 'اردکان']],
        ];

        foreach ($provincesCities as $pIndex => $entry) {
            $province = Province::firstOrCreate([
                'name' => $entry['province'],
            ], [
                'name_en' => null,
                'slug' => Str::slug($entry['province']),
            ]);

            foreach ($entry['cities'] as $cIndex => $cityName) {
                City::firstOrCreate([
                    'province_id' => $province->id,
                    'name' => $cityName,
                ], [
                    'name_en' => null,
                    'slug' => Str::slug($cityName),
                    'is_capital' => $cIndex === 0,
                ]);
            }
        }
    }
}
