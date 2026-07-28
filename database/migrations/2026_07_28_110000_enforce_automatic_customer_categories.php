<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customers')->update(['category_override' => false]);
    }

    public function down(): void
    {
        // Automatic categorization remains enabled when rolling back.
    }
};
