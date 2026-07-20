<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            ['holiday_date' => '2026-01-01', 'description' => 'Tahun Baru 2026 Masehi', 'is_national_holiday' => true],
            ['holiday_date' => '2026-01-16', 'description' => "Isra Mi'raj Nabi Muhammad SAW", 'is_national_holiday' => true],
            ['holiday_date' => '2026-02-16', 'description' => 'Cuti Bersama Tahun Baru Imlek 2577 Kongzili', 'is_national_holiday' => false],
            ['holiday_date' => '2026-02-17', 'description' => 'Tahun Baru Imlek 2577 Kongzili', 'is_national_holiday' => true],
            ['holiday_date' => '2026-03-18', 'description' => 'Cuti Bersama Hari Suci Nyepi Tahun Baru Saka 1948', 'is_national_holiday' => false],
            ['holiday_date' => '2026-03-19', 'description' => 'Hari Suci Nyepi Tahun Baru Saka 1948', 'is_national_holiday' => true],
            ['holiday_date' => '2026-03-20', 'description' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'is_national_holiday' => false],
            ['holiday_date' => '2026-03-21', 'description' => 'Hari Raya Idul Fitri 1447 Hijriyah', 'is_national_holiday' => true],
            ['holiday_date' => '2026-03-22', 'description' => 'Hari Raya Idul Fitri 1447 Hijriyah', 'is_national_holiday' => true],
            ['holiday_date' => '2026-03-23', 'description' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'is_national_holiday' => false],
            ['holiday_date' => '2026-03-24', 'description' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'is_national_holiday' => false],
            ['holiday_date' => '2026-04-03', 'description' => 'Wafat Yesus Kristus / Jumat Agung', 'is_national_holiday' => true],
            ['holiday_date' => '2026-04-05', 'description' => 'Kebangkitan Yesus Kristus (Paskah)', 'is_national_holiday' => true],
            ['holiday_date' => '2026-05-01', 'description' => 'Hari Buruh Internasional', 'is_national_holiday' => true],
            ['holiday_date' => '2026-05-14', 'description' => 'Kenaikan Yesus Kristus', 'is_national_holiday' => true],
            ['holiday_date' => '2026-05-15', 'description' => 'Cuti Bersama Kenaikan Yesus Kristus', 'is_national_holiday' => false],
            ['holiday_date' => '2026-05-27', 'description' => 'Hari Raya Idul Adha 1447 Hijriyah', 'is_national_holiday' => true],
            ['holiday_date' => '2026-05-28', 'description' => 'Cuti Bersama Hari Raya Idul Adha 1447 Hijriyah', 'is_national_holiday' => false],
            ['holiday_date' => '2026-05-31', 'description' => 'Hari Raya Waisak 2570 BE', 'is_national_holiday' => true],
            ['holiday_date' => '2026-06-01', 'description' => 'Hari Lahir Pancasila', 'is_national_holiday' => true],
            ['holiday_date' => '2026-06-16', 'description' => 'Tahun Baru Islam 1448 Hijriyah', 'is_national_holiday' => true],
            ['holiday_date' => '2026-08-17', 'description' => 'Hari Kemerdekaan Republik Indonesia', 'is_national_holiday' => true],
            ['holiday_date' => '2026-08-25', 'description' => 'Maulid Nabi Muhammad SAW', 'is_national_holiday' => true],
            ['holiday_date' => '2026-12-24', 'description' => 'Cuti Bersama Hari Raya Natal', 'is_national_holiday' => false],
            ['holiday_date' => '2026-12-25', 'description' => 'Hari Raya Natal', 'is_national_holiday' => true],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['holiday_date' => $holiday['holiday_date']],
                [
                    'description' => $holiday['description'],
                    'is_national_holiday' => $holiday['is_national_holiday'],
                ]
            );
        }
    }
}
