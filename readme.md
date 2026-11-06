# University Exam System

A web-based university examination management system developed as a semester project using PHP and the Nette framework.

The application provides separate interfaces for students and teachers, allowing them to manage examination dates, student registrations and exam results.

## Features

### Student
- Secure login
- Overview of enrolled subjects
- Browse available examination dates
- Register for and cancel examination dates
- View examination results

### Teacher
- Secure login
- Overview of assigned subjects
- Create, edit and delete examination dates
- Manage examination capacity
- View registered students
- Enter and update examination results

### System rules
- Role-based access control
- Students cannot register for the same examination twice
- Students cannot register for multiple dates of the same subject
- Examination capacity is enforced
- Past examination dates cannot be created
- Results can be entered after the examination date

## Technologies

- PHP 8.2
- Nette Framework 3.2
- MariaDB / MySQL
- HTML
- CSS
- Composer

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL or MariaDB
- Apache web server (e.g. XAMPP)

## Local installation

1. Clone the repository:

   ```bash
   git clone https://github.com/zuzicenek/university-exam-system.git
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Create a database named `projekt` and import the project database structure and sample data.

4. Configure the database connection in `config/common.neon`.

5. Start Apache and MySQL using XAMPP.

6. Open the application:

   `http://localhost/university-exam-system/www/`

## Project purpose

The project was created to practise PHP development, database operations, authentication, role-based access control and building a web application using the Nette framework.