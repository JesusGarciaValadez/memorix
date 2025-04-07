# Laravel Flashcard Application

An interactive command-line flashcard application built with Laravel 12, which utilizes the module pattern for better code organization. The application allows users to create, manage, and practice flashcards.

## Features

- Interactive CLI interface for managing flashcards
- User authentication and authorization
- Create, list, delete, and practice flashcards
- Track statistics and progress
- Activity logging
- Modular architecture

## Requirements

- Docker
- Docker Compose
- Git

## Installation and Setup Instructions

### 1. Clone the Repository

First, clone the repository to your local machine:

```bash
git clone <repository-url>
cd laravel-engineer-gevgku
```

### 2. Install Laravel Sail

Laravel Sail is already included in the project, but if you need to install it in an existing application:

```bash
composer require laravel/sail --dev
```

### 3. Generate Application Key

Generate the application encryption key:

```bash
sail artisan key:generate
```

### 4. Start Laravel Sail

Start the Docker containers:

```bash
sail up -d
```

The `-d` flag runs the containers in the background.

### 5. Install Dependencies

Install PHP dependencies:

```bash
sail composer install
```

### 6. Run Migrations

Execute database migrations:

```bash
sail artisan migrate
```

### 7. Run Unit Tests

Execute the unit tests:

```bash
sail artisan test
```

### 8. Run the Flashcard Interactive Command

To run the interactive flashcard command:

```bash
sail artisan flashcard:interactive
```

## Command Documentation

### FlashcardInteractiveCommand

The `flashcard:interactive` command provides an interactive menu for managing flashcards.

**Signature:**
```
flashcard:interactive
    {email? : The email of the user}
    {password? : The password of the user}
    {--list : List all flashcards}
    {--create : Create a new flashcard}
    {--delete : Delete a flashcard}
    {--practice : Practice study mode}
    {--statistics : Show statistics}
    {--reset : Reset the flashcards data}
    {--register : Register a new user}
    {--logs : View user activity logs}
```

**Options:**

1. **email** (optional): The email of the user to authenticate with.
2. **password** (optional): The password of the user to authenticate with.
3. **--list**: Lists all flashcards for the authenticated user.
4. **--create**: Opens the create flashcard interface.
5. **--delete**: Opens the delete flashcard interface.
6. **--practice**: Enters practice study mode.
7. **--statistics**: Shows statistics about the user's flashcards.
8. **--reset**: Resets all flashcards data for the user.
9. **--register**: Registers a new user.
10. **--logs**: Views user activity logs.

**Examples:**

- Run the interactive menu: `sail artisan flashcard:interactive`
- List flashcards: `sail artisan flashcard:interactive --list`
- Create a flashcard: `sail artisan flashcard:interactive --create`
- Practice flashcards: `sail artisan flashcard:interactive --practice`
- Register a new user: `sail artisan flashcard:interactive --register`
- Login with credentials: `sail artisan flashcard:interactive user@example.com password123`

### FlashcardRegisterCommand

The `flashcard:register` command allows users to register a new account.

**Signature:**
```
flashcard:register
    {name : The name of the user}
    {email : The email of the user}
    {password : The password of the user}
    {--skip-interactive : Skip the interactive part}
```

**Options:**

1. **name** (required): The name of the user to register.
2. **email** (required): The email of the user to register.
3. **password** (required): The password for the user account.
4. **--skip-interactive**: Skips launching the interactive menu after registration.

**Examples:**

- Register a new user: `sail artisan flashcard:register "John Doe" john@example.com password123`
- Register and skip interactive menu: `sail artisan flashcard:register "John Doe" john@example.com password123 --skip-interactive`

## Project Structure

The application follows a modular architecture with the Flashcard functionality in the `modules/Flashcard` directory:

- `modules/Flashcard/app/Console/Commands`: Contains the command classes
- `modules/Flashcard/app/Models`: Contains the model classes
- `modules/Flashcard/app/Repositories`: Contains the repository classes
- `modules/Flashcard/app/Services`: Contains the service classes
- `modules/Flashcard/database/migrations`: Contains the database migrations
- `modules/Flashcard/tests`: Contains the test classes

## Additional Notes

- The application uses Laravel Sail for Docker containerization, making it easy to run in any environment.
- All commands should be executed using the `sail` prefix to ensure they run within the Docker container.
- The application implements proper authentication and authorization to ensure users can only access their own flashcards.
- The interactive command provides a user-friendly interface for managing flashcards through the command line.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Proposed New Feature: Bulk Import Flashcards

As an additional enhancement to the Flashcard Application, I propose implementing a bulk import feature that allows users to import multiple flashcards at once using a CSV file. This feature would significantly improve the user experience when creating large sets of flashcards.

### Feature Description

The bulk import feature would:

1. Allow authenticated users to import flashcards from a CSV file
2. Support a simple CSV structure with columns for "question" and "answer"
3. Validate the CSV format and content before importing
4. Provide clear feedback on the import process (success/failure)
5. Show a summary of imported flashcards and any errors encountered

### Implementation Details

The implementation would include:

1. A new command `flashcard:import` that accepts a CSV file path as an argument and the user's email
2. CSV parsing and validation logic
3. Batch processing of flashcards to improve performance
4. Error handling for malformed CSV files or invalid data
5. Progress indicators for large imports
6. Detailed reporting of the import results

### Example Usage

```bash
sail artisan flashcard:import --file=path/to/flashcards.csv --email=user@example.com
```

### CSV Format

The CSV file would follow a simple format:

```csv
question,answer
"What is the capital of France?","Paris"
"What is the largest planet in our solar system?","Jupiter"
```

This feature would be particularly valuable for educators, students, or anyone who needs to create large sets of flashcards quickly, making the application more versatile and user-friendly.

I have a video recorded explaining this feature by myself. If interested to see it, please just let me know where to send it.
