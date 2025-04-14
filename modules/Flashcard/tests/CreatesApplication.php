<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $appPath = realpath(__DIR__.'/../../../bootstrap/app.php');

        $app = require $appPath;

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
