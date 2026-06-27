<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::set('mobile_latest_version', '1.0.0');
        Setting::set('mobile_minimum_version', '1.0.0');
        Setting::set('mobile_download_url', 'https://berlianpratamaindonesia.com/tms/downloads/app-v1.apk');
    }
}
