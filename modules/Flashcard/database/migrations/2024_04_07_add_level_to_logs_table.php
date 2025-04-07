<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->string('level')->default('info')->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
