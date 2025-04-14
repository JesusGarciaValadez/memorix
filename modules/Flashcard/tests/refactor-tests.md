# Test Refactoring Guide

This guide provides a step-by-step plan for migrating existing tests to the new structure that differentiates between unit and feature tests.

## Understanding Test Types

### Unit Tests
- Do not require real database interactions
- Test a single class or method in isolation
- Mock dependencies
- Are fast and focus on behavior, not implementation
- Examples: testing a service method, validator, or a single model method with mocked DB responses

### Feature Tests
- Involve real database interactions
- Test API endpoints, routes, middleware
- Test multiple components working together
- Examples: testing API endpoints, console commands, model relationships with actual database

## How to Migrate Your Tests

### Step 1: Update Base Classes

We have created three base test classes:

1. `TestCase` - The base class for all tests
2. `UnitTestCase` - For unit tests
3. `FeatureTestCase` - For feature tests

The `FeatureTestCase` uses the `RefreshDatabase` trait and sets up core database migrations, while `UnitTestCase` does not interact with the database by default.

### Step 2: Fix the RefreshDatabase Conflict

We've resolved the conflict between the `refreshDatabase()` method and the `RefreshDatabase` trait by:

1. Renaming the `refreshDatabase()` method to `setupCoreDatabase()`
2. Making it a public method that can be called when needed
3. Using the `RefreshDatabase` trait in the `FeatureTestCase`

### Step 3: Use the Migration Script

We've created a script to help automate the migration process. Here's how to use it:

```bash
php modules/Flashcard/tests/migrate-tests.php
```

This script will:
1. Create the `/Unit` and `/Feature` directories if they don't exist
2. Analyze your tests to determine if they're unit or feature tests
3. Move the tests to the appropriate directories
4. Update the namespaces
5. Update the parent class references
6. Remove the `RefreshDatabase` trait from unit tests

### Step 4: Manual Review and Testing

After running the migration script:
1. Review the changes to ensure tests were correctly categorized
2. Run your test suite to verify everything still works
3. Fix any issues that might have arisen during migration

### Step 5: Create New Tests Using the Correct Base Classes

When creating new tests:
1. For unit tests, extend `UnitTestCase` and place in the `/Unit` directory
2. For feature tests, extend `FeatureTestCase` and place in the `/Feature` directory

## Example Migration

Let's look at an example of how a test file is migrated:

### Before Migration (ExampleTest.php)

```php
<?php

namespace Modules\Flashcard\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\Models\Flashcard;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_flashcard()
    {
        // This test interacts with the database and should be a feature test
        $flashcard = Flashcard::factory()->create([
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ]);

        $this->assertDatabaseHas('flashcards', [
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ]);

        $this->assertEquals('Test Question', $flashcard->question);
    }

    public function test_flashcard_question_validation()
    {
        // This test could be a unit test with proper mocking
        $flashcard = new Flashcard();
        $flashcard->question = '';
        
        $this->assertFalse($flashcard->isValid());
    }
}
```

### After Migration (split into two files)

#### Feature Test (Feature/ExampleTest.php)

```php
<?php

namespace Modules\Flashcard\Tests\Feature;

use Modules\Flashcard\Tests\Feature\FeatureTestCase;
use Modules\Flashcard\Models\Flashcard;

class ExampleTest extends FeatureTestCase
{
    public function test_can_create_flashcard()
    {
        // This test interacts with the database and should be a feature test
        $flashcard = Flashcard::factory()->create([
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ]);

        $this->assertDatabaseHas('flashcards', [
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ]);

        $this->assertEquals('Test Question', $flashcard->question);
    }
}
```

#### Unit Test (Unit/FlashcardValidationTest.php)

```php
<?php

namespace Modules\Flashcard\Tests\Unit;

use Modules\Flashcard\tests\Feature\UnitTestCase;
use Modules\Flashcard\Models\Flashcard;
use Mockery;

class FlashcardValidationTest extends UnitTestCase
{
    public function test_flashcard_question_validation()
    {
        // Unit test with proper mocking
        $flashcard = new Flashcard();
        $flashcard->question = '';
        
        $this->assertFalse($flashcard->isValid());
    }
}
```

## Best Practices Moving Forward

1. **Keep Unit Tests Fast** - Avoid any real database operations in unit tests
2. **Test One Thing Per Test** - Each test should focus on testing one specific behavior
3. **Use Descriptive Test Names** - Name your tests to clearly describe what they're testing
4. **Use Proper Assertions** - Use the most specific assertion for your test case
5. **Mock External Dependencies** - In unit tests, mock all external dependencies
6. **Document Complex Tests** - Add comments to explain complex test setups or assertions
7. **Avoid Shared State** - Tests should be independent and not rely on state from other tests 
