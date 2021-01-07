<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('weekly_schedule')->truncate();
        DB::table('daily_schedule')->truncate();
        DB::table('consultations')->truncate();

        User::factory()->count(5)->create();

        $roleDoctor = Role::create(
            ['name' => Role::ROLE_DOCTOR]
        );

        $rolePatient = Role::create(
            ['name' => Role::ROLE_PATIENT]
        );

        $doctor = User::find(1);
        $doctor->assignRole($roleDoctor);

        foreach (['Monday', 'Tuesday'] as $day){
            $data = [
                'day' => $day,
                'user_id' => $doctor->id
            ];

            $id = DB::table('weekly_schedule')->insertGetId($data);

            foreach (["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00"] as $time){
                $data = [
                    'time' => $time,
                    'day_id' => $id,
                    'user_id' => $doctor->id
                ];

                DB::table('daily_schedule')->insertGetId($data);
            }
        }

/*        Consultation::create([
            'doctor_id' => 1,
            'patient_id' => 2,
            'booked_date' => '2021-01-04 10:00:00',
            'status' => Consultation::STATUS_CREATED
        ]);

        Consultation::create([
            'doctor_id' => 1,
            'patient_id' => 2,
            'booked_date' => '2021-01-05 09:00:00',
            'status' => Consultation::STATUS_CREATED
        ]);*/


        $patient = User::find(2);
        $patient->assignRole($rolePatient);

        $patient = User::find(3);
        $patient->assignRole($rolePatient);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
