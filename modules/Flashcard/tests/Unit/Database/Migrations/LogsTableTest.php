<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\Database\Migrations;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class LogsTableTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        // Run core Laravel migrations first
        $this->artisan('migrate', ['--path' => 'database/migrations']);

        // Run module migrations
        $this->artisan('migrate', ['--path' => 'modules/Flashcard/database/migrations']);
    }

    protected function tearDown(): void
    {
        // Re-enable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        }

        parent::tearDown();
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../../../../../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    #[Test]
    public function logs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('logs'));
    }

    #[Test]
    public function logs_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('logs', [
            'id', 'user_id', 'action', 'details', 'level', 'created_at',
        ]));
    }

    #[Test]
    public function logs_table_columns_have_correct_data_types(): void
    {
        $this->assertTrue(Schema::hasColumn('logs', 'id'));
        $this->assertTrue(Schema::hasColumn('logs', 'user_id'));
        $this->assertTrue(Schema::hasColumn('logs', 'action'));
        $this->assertTrue(Schema::hasColumn('logs', 'details'));
        $this->assertTrue(Schema::hasColumn('logs', 'level'));
        $this->assertTrue(Schema::hasColumn('logs', 'created_at'));
    }

    #[Test]
    public function logs_table_has_correct_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('logs', 'user_id'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));
    }
}
