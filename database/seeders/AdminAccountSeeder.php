<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Illuminate\Notifications\Notifiable;

use App\Models\UsersAccount;
use App\Models\Setting;

use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAccountSeeder extends Seeder
{
    // use Notifiable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // تحقق إذا كان هناك Admin موجود مسبقًا
        $existingAdmin = UsersAccount::where('role', 'superAdmin')->first();

        if (!$existingAdmin) {
            // إنشاء حساب Admin
            $admin = UsersAccount::create([
                'username' => 'AdminUser',
                'email' => 'adminuser@example.com',
                'password' => 'admin123', // التشفير الصحيح
                'phone' => '01234567891',
                'role' => 'superAdmin',
                'countries_id' => 3,
            ]);
            // إنشاء JWT Token لهذا الـ Admin
            $token = JWTAuth::fromUser($admin);

            // طباعة الـ Token في Console أو حفظه في مكان مناسب
            $this->command->info('Admin account created successfully!');
            $this->command->info('JWT Token: ' . $token);
        } else {
            $this->command->info('Admin account already exists.');
        }
        // في حالة وجود الأدمن مسبقًا، تحديث رقم الهاتف في settings
        Setting::updateOrCreate(
            ['key' => 'admin_phone'],
            ['value' => $existingAdmin->phone]
        );

        $this->command->info("Admin Phone updated in settings.");
    }
}
