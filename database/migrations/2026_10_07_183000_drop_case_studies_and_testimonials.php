<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('case_studies');
    }

    public function down(): void
    {
        // Tables stay removed. Recreate them only if case studies and testimonials return.
    }
};
