<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('projects');
    }

    public function down(): void
    {
        // Portfolio/projects were removed from the product; the table is not restored.
    }
};
