<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->courses() as $course) {
            Course::create($course);
        }
    }

    public function courses()
    {
        return [
            [
                'name' => 'Bachelor of Science in Information Technology',
                'acronym' => 'BSIT'
            ]
        ];
    }
}
