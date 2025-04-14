# Flashcard Module Test Suite

This directory contains tests for the Flashcard module, organized to clearly differentiate between unit tests and feature/integration tests.

## Test Organization

Tests are organized into two main categories:

### 1. Unit Tests (`/Unit`)

Unit tests focus on testing individual components in isolation. These tests:

- Test a single unit of code (class, method, function)
- Mock all dependencies
- Do not rely on database interactions
- Test behavior, not implementation
- Are fast and have minimal dependencies

**Examples:**
- Testing service methods with mocked repositories
- Testing validation logic
- Testing controller actions with mocked services

### 2. Feature Tests (`/Feature`)

Feature tests focus on testing larger portions of the system working together. These tests:

- Test API endpoints, console commands, etc.
- Use real database interactions
- Test integration between components
- Focus on testing user-facing functionality

**Examples:**
- Testing API endpoints
- Testing database migrations
- Testing model relationships with real database operations
- Testing console commands

## Test Base Classes

We provide specialized base test classes to help organize tests:

- `Modules\Flashcard\Tests\TestCase` - Base class for all tests
- `Modules\Flashcard\tests\Feature\UnitTestCase` - Base class for unit tests
- `Modules\Flashcard\Tests\Feature\FeatureTestCase` - Base class for feature tests

## Guidelines for Writing Tests

1. **Use the correct base class** - Extend from `UnitTestCase` for unit tests and `FeatureTestCase` for feature tests.

2. **Focus on behavior, not implementation** - Test what a method does, not how it does it.

3. **Name tests descriptively** - Use the pattern `it_does_something_when_something()` to clearly describe what the test is verifying.

4. **One assertion per test** - As much as possible, focus each test on a single behavior or outcome.

5. **Use test attributes** - Use the `#[Test]` attribute to mark test methods.

6. **Mock dependencies in unit tests** - Use Mockery to create mock dependencies for true unit testing.

7. **Test edge cases** - Consider what happens with empty input, very large input, special characters, etc.

8. **Test error conditions** - Ensure that error conditions are properly tested and handled.

9. **Add comments to complex tests** - Explain the purpose of each section of a test with comments.

## Example Test Structure

```php
#[Test]
public function it_does_something_when_condition_is_met(): void
{
    // Setup / Arrange - prepare the test environment
    $this->setupSomething();
    
    // Exercise / Act - perform the action being tested
    $result = $this->callMethodBeingTested();
    
    // Verify / Assert - check that the expected outcome occurred
    $this->assertEquals($expected, $result);
}
```

## Running Tests

Run all tests for the Flashcard module:

```bash
sail test --filter=Modules\\Flashcard
```

Run only unit tests:

```bash
sail test --filter=Modules\\Flashcard\\Tests\\Unit
```

Run only feature tests:

```bash
sail test --filter=Modules\\Flashcard\\Tests\\Feature
```

Run a specific test class:

```bash
sail test --filter=Modules\\Flashcard\\Tests\\Unit\\Services\\FlashcardServiceTest
```

# Test Migration Guide

This guide provides instructions for migrating your tests from the current structure to a more organized unit and feature test structure.

## Background

In Laravel applications, it's a best practice to separate tests into two categories:

1. **Unit Tests**: These tests focus on testing individual units of code (like classes or methods) in isolation from the rest of the application. They should not require database access, file system access, or other external resources.

2. **Feature Tests**: These tests focus on testing the behavior of the application as a whole, often involving database access, API endpoints, and multiple components working together.

## Migration Steps

### Step 1: Run the Migration Script

The `migrate_tests.php` script will analyze your existing tests and automatically categorize them as unit or feature tests based on their characteristics.

```bash
cd modules/Flashcard/tests
php migrate_tests.php
```

The script will:
- Create Unit and Feature directories if they don't exist
- Analyze each test file to determine if it's a unit or feature test
- Move the file to the appropriate directory
- Update the namespace in the file to reflect its new location
- Update the base class to either UnitTestCase or FeatureTestCase

### Step 2: Review the Migrated Tests

After running the script, review the migrated tests to ensure they're correctly categorized. You may need to make manual adjustments to some tests, especially if they:

- Mix unit and feature testing behaviors
- Have complex inheritance structures
- Have custom setup/teardown logic that needs to be merged with the new base classes

### Step 3: Update Base Class Implementations (if needed)

The migration has created two base classes:

- `modules/Flashcard/tests/Unit/UnitTestCase.php`: Base class for unit tests
- `modules/Flashcard/tests/Feature/FeatureTestCase.php`: Base class for feature tests

Review these classes and modify them if needed to match your specific testing requirements.

## Manual Migration (if needed)

If you prefer to migrate tests manually or if the automatic migration doesn't work for some tests, follow these guidelines:

### For Unit Tests:

1. Move the test file to the `Unit` directory
2. Update the namespace to include `\Unit\`
3. Make the test class extend `UnitTestCase`
4. Remove any database interactions and replace them with mocks
5. Remove any direct HTTP calls and test the underlying methods directly

### For Feature Tests:

1. Move the test file to the `Feature` directory
2. Update the namespace to include `\Feature\`
3. Make the test class extend `FeatureTestCase`
4. Ensure the test uses the RefreshDatabase trait if it needs database access
5. Use HTTP testing methods (`get`, `post`, `put`, etc.) to test API endpoints

## Best Practices

- **Unit tests** should be fast and focus on testing a single unit of code in isolation
- **Feature tests** should test the behavior of the application from an external perspective
- Use mocks and stubs in unit tests to isolate the code being tested
- Use real database connections in feature tests to ensure the entire stack works properly
- Keep both types of tests focused on testing specific behavior 
