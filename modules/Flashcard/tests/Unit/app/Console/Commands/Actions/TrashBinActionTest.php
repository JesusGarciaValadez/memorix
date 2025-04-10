<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Console\Commands\Actions\TrashBinAction;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class TrashBinActionTest extends TestCase
{
    use RefreshDatabase;

    private const USER_ID = 1;

    private Command $command;

    private FlashcardRepositoryInterface $flashcardRepository;

    private LogRepositoryInterface $logRepository;

    private ConsoleRendererInterface $renderer;

    private TrashBinAction $action;

    private User $user;

    private Log $mockLog;

    private Flashcard $flashcard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = $this->createMock(Command::class);
        $this->flashcardRepository = $this->createMock(FlashcardRepositoryInterface::class);
        $this->logRepository = $this->createMock(LogRepositoryInterface::class);
        $this->renderer = $this->createMock(ConsoleRendererInterface::class);

        // Create a real user
        $this->user = User::factory()->create([
            'id' => self::USER_ID,
        ]);
        $this->command->user = $this->user;

        // Create a mock log
        $this->mockLog = Log::factory()->create([
            'user_id' => self::USER_ID,
            'action' => 'test_action',
            'level' => Log::LEVEL_INFO,
            'details' => 'test details',
        ]);

        // Create a test flashcard
        $this->flashcard = Flashcard::factory()->create([
            'user_id' => self::USER_ID,
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ]);
        $this->flashcard->delete(); // Soft delete the flashcard

        $this->action = new TrashBinAction(
            $this->command,
            $this->flashcardRepository,
            $this->logRepository,
            $this->renderer
        );
    }

    #[Test]
    public function it_shows_warning_when_no_deleted_flashcards(): void
    {
        $emptyPaginator = new LengthAwarePaginator([], 0, 15);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getAllDeletedForUser')
            ->with(self::USER_ID, 15)
            ->willReturn($emptyPaginator);

        $this->renderer
            ->expects($this->once())
            ->method('warning')
            ->with('No deleted flashcards found.');

        $this->action->execute();
    }

    #[Test]
    public function it_displays_deleted_flashcards_and_handles_restore(): void
    {
        $paginator = new LengthAwarePaginator([$this->flashcard], 1, 15);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getAllDeletedForUser')
            ->with(self::USER_ID, 15)
            ->willReturn($paginator);

        $this->renderer
            ->expects($this->exactly(12))
            ->method('info')
            ->willReturnCallback(function ($message) {
                static $calls = 0;
                $calls++;
                match ($calls) {
                    1 => $this->assertEquals('Deleted Flashcards:', $message),
                    2 => $this->assertEquals('------------------', $message),
                    3 => $this->assertEquals('1. Question: Test Question', $message),
                    4 => $this->assertEquals('   Answer: Test Answer', $message),
                    5 => $this->assertStringContainsString('   Deleted at:', $message),
                    6 => $this->assertEquals('------------------', $message),
                    7 => $this->assertEquals('Options:', $message),
                    8 => $this->assertEquals('1. Restore a flashcard', $message),
                    9 => $this->assertEquals('2. Permanently delete a flashcard', $message),
                    10 => $this->assertEquals('3. Restore all flashcards', $message),
                    11 => $this->assertEquals('4. Permanently delete all flashcards', $message),
                    12 => $this->assertEquals('5. Exit', $message),
                    default => $this->fail('Unexpected info call')
                };
            });

        $this->renderer
            ->expects($this->exactly(2))
            ->method('ask')
            ->willReturnCallback(function ($message) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    $this->assertEquals('Enter your choice (1-5): ', $message);

                    return '1';
                }
                if ($calls === 2) {
                    $this->assertEquals('Enter the number of the flashcard to restore: ', $message);

                    return '1';
                }

                return '';
            });

        $this->flashcardRepository
            ->expects($this->once())
            ->method('findForUser')
            ->with($this->flashcard->id, self::USER_ID, true)
            ->willReturn($this->flashcard);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('restore')
            ->with($this->flashcard)
            ->willReturn(true);

        $this->logRepository
            ->expects($this->once())
            ->method('logFlashcardRestoration')
            ->with(self::USER_ID, $this->flashcard)
            ->willReturn($this->mockLog);

        $this->renderer
            ->expects($this->once())
            ->method('success')
            ->with('Flashcard restored successfully!');

        $this->action->execute();
    }

    #[Test]
    public function it_handles_permanent_delete(): void
    {
        $paginator = new LengthAwarePaginator([$this->flashcard], 1, 15);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getAllDeletedForUser')
            ->with(self::USER_ID, 15)
            ->willReturn($paginator);

        $this->renderer
            ->expects($this->exactly(12))
            ->method('info');

        $this->renderer
            ->expects($this->exactly(2))
            ->method('ask')
            ->willReturnCallback(function ($message) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    return '2'; // Choose permanent delete option
                }
                if ($calls === 2) {
                    return '1'; // Choose first flashcard
                }

                return '';
            });

        $this->flashcardRepository
            ->expects($this->once())
            ->method('findForUser')
            ->with($this->flashcard->id, self::USER_ID, true)
            ->willReturn($this->flashcard);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('forceDelete')
            ->with($this->flashcard)
            ->willReturn(true);

        $this->logRepository
            ->expects($this->once())
            ->method('logFlashcardDeletion')
            ->with(self::USER_ID, $this->flashcard)
            ->willReturn($this->mockLog);

        $this->renderer
            ->expects($this->once())
            ->method('success')
            ->with('Flashcard permanently deleted successfully!');

        $this->action->execute();
    }

    #[Test]
    public function it_handles_restore_all(): void
    {
        $paginator = new LengthAwarePaginator([$this->flashcard], 1, 15);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getAllDeletedForUser')
            ->with(self::USER_ID, 15)
            ->willReturn($paginator);

        $this->renderer
            ->expects($this->exactly(12))
            ->method('info');

        $this->renderer
            ->expects($this->exactly(2))
            ->method('ask')
            ->willReturnCallback(function ($message) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    return '3'; // Choose restore all option
                }
                if ($calls === 2) {
                    return 'yes'; // Confirm restore all
                }

                return '';
            });

        $this->flashcardRepository
            ->expects($this->once())
            ->method('restoreAll')
            ->with(self::USER_ID)
            ->willReturn(true);

        $this->logRepository
            ->expects($this->once())
            ->method('logAllFlashcardsRestore')
            ->with(self::USER_ID)
            ->willReturn($this->mockLog);

        $this->renderer
            ->expects($this->once())
            ->method('success')
            ->with('All flashcards restored successfully!');

        $this->action->execute();
    }

    #[Test]
    public function it_handles_permanent_delete_all(): void
    {
        $paginator = new LengthAwarePaginator([$this->flashcard], 1, 15);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getAllDeletedForUser')
            ->with(self::USER_ID, 15)
            ->willReturn($paginator);

        $this->renderer
            ->expects($this->exactly(12))
            ->method('info');

        $this->renderer
            ->expects($this->exactly(2))
            ->method('ask')
            ->willReturnCallback(function ($message) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    return '4'; // Choose permanent delete all option
                }
                if ($calls === 2) {
                    return 'yes'; // Confirm permanent delete all
                }

                return '';
            });

        $this->flashcardRepository
            ->expects($this->once())
            ->method('forceDeleteAll')
            ->with(self::USER_ID)
            ->willReturn(true);

        $this->logRepository
            ->expects($this->once())
            ->method('logAllFlashcardsPermanentDelete')
            ->with(self::USER_ID)
            ->willReturn($this->mockLog);

        $this->renderer
            ->expects($this->once())
            ->method('success')
            ->with('All flashcards permanently deleted!');

        $this->action->execute();
    }
}
