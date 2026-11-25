<?php

namespace Database\Seeders;

use App\Models\CareerDepartment;
use App\Models\CareerJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketingExecutiveJobSeeder extends Seeder
{
    public const SLUG = 'marketing-executive';

    public function run(): void
    {
        DB::transaction(function () {
            // Preserve later admin edits, closed openings and deleted records on repeat runs.
            if (CareerJob::withTrashed()->where('slug', self::SLUG)->exists()) {
                return;
            }

            $department = CareerDepartment::query()->firstOrCreate(
                ['slug' => 'sales-marketing'],
                ['name' => 'Sales & Marketing', 'sort_order' => 20, 'is_active' => true],
            );

            CareerJob::query()->firstOrCreate(['slug' => self::SLUG], [
                'career_department_id' => $department->id,
                'title' => 'Marketing Executive / Business Development Executive',
                'location' => 'Ahmedabad, Gujarat',
                'work_mode' => 'on-site',
                'employment_type' => 'full-time',
                'experience_min' => 0,
                'experience_max' => null,
                'compensation_type' => 'salary',
                'show_compensation' => false,
                'summary' => 'Generate leads and build client relationships for our IT services and digital solutions in Ahmedabad. Freshers and experienced candidates welcome.',
                'overview' => <<<'HTML'
<p>Join CEBINOVA Technologies and grow your career with us. We are looking for energetic, confident and result-oriented Marketing Executives / Business Development Executives who can help businesses grow through technology.</p>
<p>If you are passionate about marketing, sales and business development, we would love to hear from you.</p>
<h3>Benefits &amp; perks</h3>
<ul>
<li>Fixed salary + performance-based incentives.</li>
<li>Attractive incentives on achieving assigned targets.</li>
<li>Petrol allowance for official travel.</li>
<li>Career growth opportunities.</li>
<li>Supportive and professional work environment.</li>
</ul>
<h3>Why join CEBINOVA?</h3>
<p>At CEBINOVA Technologies, we believe in rewarding performance, encouraging innovation and growing together. Your efforts deserve recognition, and your achievements deserve rewards.</p>
<p>Technology for Every Business.</p>
<h3>How to apply</h3>
<p>Use the Apply now button to submit your details and resume through our website. For questions about the role, call +91 9054315515.</p>
HTML,
                'responsibilities' => <<<'HTML'
<ul>
<li>Generate new business leads and potential clients.</li>
<li>Promote our IT services and digital solutions.</li>
<li>Meet potential customers and understand their requirements.</li>
<li>Build strong relationships with clients.</li>
<li>Achieve monthly sales and marketing targets.</li>
<li>Identify new business opportunities.</li>
</ul>
HTML,
                'required_skills' => <<<'HTML'
<ul>
<li>Good communication and negotiation skills.</li>
<li>Passionate about sales and marketing.</li>
<li>Self-motivated and target-oriented.</li>
<li>Basic understanding of IT services (preferred).</li>
</ul>
HTML,
                'qualifications' => <<<'HTML'
<ul>
<li>Freshers and experienced candidates are welcome.</li>
<li>Willingness to travel for client meetings.</li>
</ul>
HTML,
                'status' => 'published',
                'published_at' => now(),
                'application_deadline' => null,
                'sort_order' => 0,
                'is_featured' => true,
                'seo_title' => 'Marketing Executive Jobs in Ahmedabad | CEBINOVA Technologies',
                'seo_description' => 'Apply for Marketing Executive / Business Development Executive at CEBINOVA Technologies, Ahmedabad. Full-time; freshers and experienced candidates welcome. Fixed salary, incentives and petrol allowance.',
            ]);
        });
    }
}
