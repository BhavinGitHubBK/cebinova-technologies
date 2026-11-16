<?php

namespace Database\Seeders;

use App\Models\CareerDepartment;
use App\Models\CareerJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CareerSampleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $department = CareerDepartment::query()->updateOrCreate(['slug' => 'mobile-development'], ['name' => 'Mobile Development', 'sort_order' => 10, 'is_active' => true]);
            foreach ([
                ['Android Developer', 'android-developer', 'Kotlin, Android SDK, REST APIs, Git'],
                ['iOS Developer', 'ios-developer', 'Swift, SwiftUI, iOS SDK, REST APIs, Git'],
                ['Flutter / React Native Developer', 'flutter-react-native-developer', 'Flutter or React Native, Dart or TypeScript, REST APIs, Git'],
            ] as $index => [$title, $slug, $skills]) {
                CareerJob::withTrashed()->updateOrCreate(['slug' => $slug], [
                    'career_department_id' => $department->id, 'title' => $title, 'location' => 'Ahmedabad, Gujarat',
                    'work_mode' => 'hybrid', 'employment_type' => 'full-time', 'experience_min' => 1, 'experience_max' => 4,
                    'summary' => 'Build reliable, polished mobile experiences for CEBINOVA clients and products.',
                    'overview' => '<p>Join our technology team to design and deliver practical mobile products for growing businesses.</p>',
                    'responsibilities' => '<ul><li>Build and maintain production mobile applications.</li><li>Collaborate with design, backend, and QA teams.</li><li>Review code and improve product quality.</li></ul>',
                    'required_skills' => '<p>'.$skills.'</p>',
                    'qualifications' => '<p>Strong problem-solving, communication, and ownership of delivery.</p>',
                    'status' => 'draft', 'published_at' => null, 'sort_order' => ($index + 1) * 10, 'is_featured' => false,
                ]);
            }
        });
    }
}
