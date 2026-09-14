<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Sam Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
        ]);

        User::create([
            'name' => 'Vic Viewer',
            'email' => 'viewer@example.com',
            'password' => Hash::make('viewer123'),
            'role' => 'viewer',
        ]);

        $courses = collect([
            ['name' => 'Mathematics 101', 'code' => 'MATH101', 'capacity' => 15],
            ['name' => 'Introduction to Biology', 'code' => 'BIO101', 'capacity' => 12],
            ['name' => 'World History', 'code' => 'HIST201', 'capacity' => 10],
            ['name' => 'Computer Science Fundamentals', 'code' => 'CS101', 'capacity' => 18],
        ])->map(fn (array $c) => Course::create($c));

        // Inserted one at a time (not via EnrollmentService) so seeding is fast and
        // doesn't have to fight over capacity locks — capacity is enforced for real
        // on the interactive Add/Edit Student form. `updated_by` is set before save()
        // so the AFTER INSERT trigger attributes each seeded row to the admin user
        // instead of logging a blank "created by nobody" entry.
        for ($i = 0; $i < 30; $i++) {
            $course = $courses->random();

            $student = Student::factory()->make([
                'course_id' => $course->id,
                'updated_by' => $admin->id,
            ]);
            $student->save();

            Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrolled_at' => now()->subDays(random_int(10, 300)),
            ]);
        }
    }
}
