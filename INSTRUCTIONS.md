## Objective

Develop an interactive command-line flashcard application using Laravel, which utilizes the module pattern for better code organization. The application should allow users to create, manage, and practice flashcards. The goal is to assess your comfort level and proficiency with modern software development practices, including module usage, and automated testing in a Laravel environment.

## Tasks

You may use any plugins you want to complete this assignment. Some requirements may be intentionally vague, so use your best judgement and knowledge of industry standards to implement. You are not expected to complete all required and optional features within the alloted timeframe. If you have additional time after completing the required features, work on optional features that you feel will best showcase your existing skills first.

### (Required) Flashcard Application

You **must** complete the tasks outlined in this section, which form the basis of the assessment.

* **Application Setup**
   * Set up a new Laravel [11.x](https://laravel.com/docs/11.x/installation) project and add all assessment code to a `Flashcard` module.
   * If you are unfamiliar with the Module pattern, use the guidelines [here](https://techsemicolon.github.io/blog/2019/01/06/laravel-module-pattern/) to get yourself started.
   * Implement the flashcard functionality as a CLI tool.
   * Incorporate simple authentication to support multiple users.
* **Functionality**: The features listed below must support multiple users. *You can safely assume that users will not share their cards with others, so each user should only be able to view or otherwise interact with their own cards.*
   * **Interactive Menu**: Display a main menu of available Flashcard options using the command `php artisan flashcard:interactive`.
   * **List Flashcards**: Display all flashcards along with their answers.
   * **Create Flashcard**: Allow users to input a question and its answer, storing this data in the database.
   * **Delete Flashcard**: Allow users to delete a question and its answer, removing the data from the database.
   * **Practice Mode**: Users can practice their flashcards.
      * First, show the current progress: The user will be presented with a table listing all questions, and their practice status for each question: Not answered, Correct, Incorrect.
      * As a table footer, we want to present the percent of completion (all questions vs correctly answered).
      * Then, the user will pick the question they want to practice. We should not allow answering questions that are already correct.
      * Upon answering, store the answer in the database and print correct/incorrect.
      * Finally, show the first step again (the current progress) and allow the user to keep practicing until they explicitly decide to stop.
   * **Statistics**: Show total questions, percentage of questions answered, and percentage correctly answered.
   * **Reset**: Enable users to reset all practice progress.
   * **Exit**: Provide a mechanism to exit the application safely.
* **Application Setup**
   * Write comprehensive feature and unit tests to cover the functionality. Testing may be done in [either PHPUnit or Pest](https://laravel.com/docs/11.x/testing).
   * Maintain clear documentation on how to set up and run the project, along with any necessary explanations of the architecture and decisions.

### (Optional) Flashcard Application Upgrades

These tasks are **optional** and can be completed to demonstrate additional skills.

* **Implement an API**
   * Implement the same Flashcard features as an API (except interactive menu and exit) for third party applications to implement.
   * You may use any authentication method, or a combination.
   * API docuementation, including authentication method, **must** be provided to be considered complete.
* **Docker Integration**
   * Create a Dockerfile and docker-compose.yml to containerize the application, making it easy to set up and run in any environment.
   * Note that you may utilize [Laravel Sail](https://laravel.com/docs/11.x/sail).
* **CI/CD Pipeline Description**
   * Write a description of how you would set up a CI/CD pipeline for this project, including stages for build, test, and deployment.
   * Mention specific tools and practices you would use.
   * You may provide diagram(s), configuration files, or any other documentation you find relevant.
* **Comprehensive Logging**
   * Upgrade the Flashcard Application to log all actions taken on the server.
   * Include the method of access (API or CLI), action taken, user who performed the action, and appropriate level of log (debug, warning, error, etc.).
* **Change History**
   * Upgrade the Flashcard Application to store a history of all changes to Flashcards. This means implementing the following additional features:
      * Allow users to view previous versions of Flashcards, if available.
      * Allow users to restore previous versions of Flashcards, if available.
* **Soft Delete**
   * Upgrade the Flashcard Application to soft delete Flashcards. This means implementing the following additional features:
      * **Restore**: Allow users to restore any deleted Flashcard.
      * **Permanently Delete**: Allow users to permanently delete any soft deleted Flashcard.
* **New Project Requirement**
   * Do you have a better idea for an optional requirement that will make you stand out from the crowd? At Solar Insure, we pride ourselves on giving every team member a voice in projects. Consider this your opportunity to define a project requirement that best suites your custom skillset.
      * Include a brief description of the feature in your README file, similar to what is written above for other optional features.
      * Build something cool!
      * (Optional) Tell us about this feature in a video response. You will have the option to record yourself after you submit your assignment.

## Evaluation Criteria

* **Assignment Completeness**: The application must function as described, fulfilling all requirements.
* **Code Elegance**: Code should be concise, minimizing complexity. It should be easily handover-able to another developer of reasonable skill without requiring significant explanation.
* **Code Readability**: Use meaningful names for variables and functions. Organize code logically into functions and modules. Maintain consistent indentation and use white space judiciously for better readability.
   * Note that it is highly recommended to use a tool like [Laravel Pint](https://laravel.com/docs/12.x/pint).
* **Documentation**: Ensure the codebase is well-commented, but avoid excessive comments. The documentation should clearly explain the setup process, usage, and any important architectural choices. Git history should be meaningful and informative.

## CodeSubmit

Please organize, design, test, and document your code as if it were going into production - then push your changes to the `main` branch.

Have fun coding! 🚀
