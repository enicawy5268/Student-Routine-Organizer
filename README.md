# Student Routine Organizer

Student Routine Organizer is a PHP and MySQL web application that enables users to manage exercise records, diary entries, financial transactions, and habits through a centralised dashboard. The system was developed as an academic project for UCCD3243 Server-Side Web Applications Development.

## Features

- User registration, login, and logout
- Session-based authentication
- Remember Me cookie
- Student and administrator access roles
- User-specific record management
- Exercise Tracker with add, view, edit, delete, search, filter, and sort functions
- Diary Journal with add, view, edit, and delete functions
- Money Tracker with add, view, edit, and delete functions
- Habit Tracker with add, view, update, and delete functions
- Administrator access to registered users and the system summary
- Shared navigation and interface styling across all modules

## Technologies Used

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript
- Apache Web Server
- phpMyAdmin

## System Requirements

- XAMPP or an equivalent PHP development environment
- PHP 8.0 or later
- MySQL or MariaDB
- A modern web browser

## Installation

1. Download or clone this repository.

   ```bash
   git clone <repository-url>
   ```

2. Rename the project folder to `assignment` if necessary.

3. Place the `assignment` folder inside the XAMPP `htdocs` directory:

   ```text
   C:\xampp\htdocs\assignment
   ```

4. Start the Apache and MySQL services from the XAMPP Control Panel.

5. Open phpMyAdmin at:

   ```text
   http://localhost/phpmyadmin
   ```

6. Create a database named `assignment`.

7. Import the following SQL file into the `assignment` database:

   ```text
   database/assignment.sql
   ```

8. Verify the database configuration in `database.php`. The default XAMPP configuration is:

   ```php
   $database_host = "localhost";
   $database_username = "root";
   $database_password = "";
   $database_name = "assignment";
   ```

9. Open the application in a web browser:

   ```text
   http://localhost/assignment/
   ```

10. Register a new account and log in to access the system dashboard.

## Project Structure

```text
assignment/
|-- admin/
|   |-- admin_summary.php
|   `-- admin_users.php
|-- assets/
|   `-- style.css
|-- database/
|   `-- assignment.sql
|-- diary_journal/
|   |-- add_diary.php
|   |-- delete_diary.php
|   |-- edit_diary.php
|   |-- index.php
|   `-- view_diary.php
|-- exercise_tracker/
|   |-- exercise_add.php
|   |-- exercise_delete.php
|   |-- exercise_edit.php
|   `-- exercise_view.php
|-- habit_tracker/
|   |-- add_habit.php
|   |-- update_habit.php
|   `-- view_habit.php
|-- money/
|   |-- money_add.php
|   |-- money_delete.php
|   |-- money_edit.php
|   `-- money_list.php
|-- auth.php
|-- dashboard.php
|-- database.php
|-- footer.php
|-- header.php
|-- index.php
|-- login.php
|-- logout.php
`-- registration.php
```

## Database Structure

The application uses five relational database tables:

- `users`
- `exercise_records`
- `diary_entries`
- `money_tracker`
- `habits`

The `users` table is the parent table. Each module table stores a `user_id` foreign key to associate its records with the authenticated user. This structure allows the system to store and retrieve user-specific information across all four modules.

## User Roles

### Student

Students can access the four tracker modules and manage records associated with their own accounts.

### Administrator

Administrators can access the registered-user list and the overall system summary through the Admin Section.

## Usage

1. Register an account or log in with an existing account.
2. Select a tracker module from the Dashboard.
3. Add, view, update, or delete records within the selected module.
4. Return to the Dashboard to access another module.
5. Log out after completing the session.

## Academic Purpose

This repository was created for educational purposes as part of the UCCD3243 Server-Side Web Applications Development assignment.
