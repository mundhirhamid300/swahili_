# Swahili Learning for Foreigners

A modern **Learning Management System (LMS)** built with Laravel to help non-Swahili speakers learn **Kiswahili** from scratch. Includes courses, lessons, flashcards, quizzes, progress tracking, certificates, a Swahili chatbot, and an **AI Translator** (OpenAI + ElevenLabs).

## Features

- **Role-based access**: Admin, Teacher, Student
- **Course management**: Beginner, intermediate, advanced levels
- **Lessons** with rich content and optional audio
- **Flashcards** with study mode and text-to-speech
- **Quizzes** with auto-grading, pass marks, timers, and retakes
- **Progress tracking** and automatic certificate issuance at 100% completion
- **PDF certificates** with public verification (`/verify/{code}`)
- **Quiz quality controls**: pass mark, timed attempts, retakes, detailed reports
- **Teacher analytics**: averages, struggling lessons, drop-off hotspots
- **Student insights**: streak, weekly goal, continue learning
- **In-app notifications** and admin audit logging
- **Search/filters** for courses, users, enrollments, certificates
- **AI Translator**: English ↔ Swahili via OpenAI; Listen via ElevenLabs
- **Swahili Chatbot**: Keyword-based assistance with chat history
- **REST API** (Laravel Sanctum) for future mobile app integration
- **MySQL** by default (SQLite still supported for quick demos)

## Tech Stack

- Laravel 12 (PHP 8.2+)
- MySQL / SQLite
- Bootstrap 5 + Tabler Icons
- Laravel Sanctum (API auth)
- OpenAI (translation / language tools)
- ElevenLabs (text-to-speech)

## Requirements

- PHP 8.2+
- Composer
- MySQL (or SQLite for quick local setup)

## Installation

```bash
# 1. Install dependencies
composer install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Configure database in .env (MySQL default)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=swahili_lms
DB_USERNAME=root
DB_PASSWORD=

# Create the database (XAMPP / MySQL):
# mysql -u root -e "CREATE DATABASE IF NOT EXISTS swahili_lms;"

# For SQLite instead:
# DB_CONNECTION=sqlite
# (and create database/database.sqlite)

# 5. Run migrations and seeders
php artisan migrate --seed

# 6. Create storage symlink
php artisan storage:link

# 7. Start the server
php artisan serve
```

Visit: **http://localhost:8000**

## Default Login Credentials

| Role    | Email              | Password  |
|---------|--------------------|-----------|
| Admin   | admin@gmail.com    | Password1 |
| Teacher | teacher@gmail.com  | Password1 |
| Student | student@gmail.com  | Password1 |

## API Endpoints

Base URL: `/api`

### Public
- `POST /api/login` — `{ email, password }`
- `POST /api/register` — `{ name, email, password, password_confirmation }`
- `GET /api/translate/phrases` — Quick phrases and dictionary

### Authenticated (Bearer token)
- `POST /api/logout`
- `GET /api/me`
- `POST /api/translate` — `{ text, from, to }` (en/sw)
- `GET /api/courses`
- `GET /api/courses/{id}`
- `POST /api/courses/{id}/enroll`
- `GET /api/my-courses`
- `GET /api/lessons/{id}`
- `POST /api/quizzes/{id}/submit` — `{ selected_answer: "a"|"b"|"c"|"d" }`
- `GET /api/progress/stats`
- `GET /api/progress/courses/{id}`
- `POST /api/chatbot` — `{ message }`

## Project Structure

```
app/
├── Http/Controllers/     # Web & API controllers
├── Http/Middleware/      # RoleMiddleware
├── Http/Requests/        # Form validation
├── Models/               # Eloquent models
└── Services/             # Translation, Progress, Certificate, Chatbot
config/swahili.php        # Dictionary & chatbot responses
database/migrations/      # Database schema
database/seeders/         # Sample data
resources/views/          # Blade templates
public/js/translator.js   # AI translator frontend
routes/web.php            # Web routes
routes/api.php            # API routes
```

## AI Translator

Uses **OpenAI** for translate / grammar / vocabulary / sentence tools, and **ElevenLabs** for Listen (text-to-speech).

Configure in `.env`:
- `OPENAI_API_KEY`
- `ELEVENLABS_API_KEY`
- `ELEVENLABS_VOICE_ID` (or set via Admin → AI Settings)

Translation sources (in order):
1. Built-in Swahili phrase dictionary (`config/swahili.php`)
2. Flashcard vocabulary from the database
3. Cached previous OpenAI results
4. OpenAI live translation

## Password Reset

Use **Forgot password?** on the login page. With `MAIL_MAILER=log` (default in development), reset links are written to `storage/logs/laravel.log`.


- Passwords hashed with bcrypt
- CSRF protection on web forms
- Role middleware on admin/teacher routes
- Sanctum token authentication for API
- Students can only access their own progress, quiz answers, and certificates

## License

MIT License — open source for educational use.
