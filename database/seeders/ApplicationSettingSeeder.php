<?php

namespace Database\Seeders;

use App\Models\ApplicationSetting;
use Illuminate\Database\Seeder;

class ApplicationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApplicationSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'application_name' => config('app.name', 'Titon'),
                'deployment_mode' => 'development',
            ],
        );
    }
}
