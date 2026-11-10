<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Organization;
use App\Models\PracticumSchedule;
use App\Models\Room;
use Illuminate\Database\Seeder;

class PracticumScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $courses = Course::where('organization_id', $organization->id)->pluck('id', 'code');
        $rooms = Room::where('organization_id', $organization->id)->pluck('id', 'code');

        $schedules = [
            ['course' => 'DKV-201', 'room' => 'R-01', 'day' => 1, 'start' => '08:00', 'end' => '11:30'],
            ['course' => 'DKV-312', 'room' => 'R-01', 'day' => 4, 'start' => '09:00', 'end' => '12:00'],
            ['course' => 'DKV-405', 'room' => 'R-02', 'day' => 2, 'start' => '09:00', 'end' => '12:00'],
            ['course' => 'DKV-312', 'room' => 'R-02', 'day' => 4, 'start' => '13:00', 'end' => '16:30'],
            ['course' => 'DKV-501', 'room' => 'R-03', 'day' => 3, 'start' => '10:00', 'end' => '13:00'],
        ];

        foreach ($schedules as $schedule) {
            PracticumSchedule::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'course_id' => $courses[$schedule['course']],
                    'room_id' => $rooms[$schedule['room']],
                    'day_of_week' => $schedule['day'],
                    'start_time' => $schedule['start'].':00',
                ],
                [
                    'end_time' => $schedule['end'].':00',
                    'is_active' => true,
                ],
            );
        }
    }
}
