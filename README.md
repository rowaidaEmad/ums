
# University Management System – Beginner Course Bundle (Laravel + Breeze + Livewire)

This folder contains **ready-to-use code and a guided path** to build your University Management System in Laravel.
It includes:
- Code for the 4 modules (Facilities, Curriculum, Staff, Community)
- An **EAV implementation** for flexible attributes
- Seeders to create an admin user
- Simple Blade views compatible with Breeze (Livewire stack also included by Breeze)
- A backlog CSV to help you import tasks into Jira or manage your Agile sprints

> You will still need to create a fresh Laravel project and **copy these files** into it (paths are relative).

## Quick Run (Checklist)
1. Install PHP 8.2+, Composer, Node.js LTS, Git, and SQLite (or MySQL).
2. `composer create-project laravel/laravel ums`
3. `cd ums`
4. `composer require laravel/breeze --dev`
5. `php artisan breeze:install livewire`
6. `npm install && npm run dev`
7. Use SQLite (easiest): create `database/database.sqlite` (empty file)
8. Edit `.env` → set `DB_CONNECTION=sqlite` and remove other DB_* lines
9. Copy the **code/** contents from this bundle into your Laravel project (matching folders).
10. `php artisan migrate --seed`
11. `php artisan serve`
12. Login with: **admin@example.com** / **password**

## EAV – Where it is used
EAV tables are provided and a `HasAttributes` trait is ready. Attach EAV to any model (e.g., Equipment) using the trait.

## Routes
All routes are defined as resource routes in `routes/web.php`. They are protected by `auth` middleware.

## Breeze + Livewire Notice
Breeze with **Livewire** stack is used for the auth scaffolding and layout. CRUD views here are simple Blade files and work fine with Breeze's layout.
You can progressively replace forms with Livewire components later if you wish.
