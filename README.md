# Coastal Villa Booking System

## Project Overview

A responsive web application for exploring rooms, checking availability,
and submitting accommodation booking requests for a coastal villa in Sri Lanka.

## Technologies

- HTML5 and CSS3
- JavaScript
- PHP
- MySQL
- PDO for database access

## Main Features

- Guest registration and login
- Password hashing and PHP session management
- Dynamic room listings from MySQL
- Availability search by dates and number of guests
- Booking requests with server-side cost calculation
- Guest booking history and pending booking cancellation
- Admin room management
- Admin booking confirmation and cancellation
- Confirmed booking date-overlap checks
- Responsive page layouts

## Software Requirements

- XAMPP with Apache, PHP and MySQL
- A modern web browser

## Installation

1. Copy the `coastal-villa` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Import `db_structure.sql` into a fresh MySQL setup using phpMyAdmin.
4. Check the database settings in `config/database.php`.
   The default XAMPP settings are database `coastal_villa`, user `root`,
   and an empty password.
5. Open `http://localhost/coastal-villa/` in a browser.

## Sample Login Credentials

Admin:
- Email: `admin@coastalvilla.test`
- Password: `VillaAdmin@2026!`

Guest:
- Email: `guest@coastalvilla.test`
- Password: `VillaGuest@2026!`

## AI Use Declaration

I used OpenAI ChatGPT as a learning and coding support tool to understand
requirements, plan features, and review implementation steps. I reviewed,
adapted, and tested the project code and can explain the submitted work.