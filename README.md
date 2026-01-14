# E-Learning Platform

A small Udemy-like e-learning platform built with PHP, MySQL, Bootstrap, and Stripe integration.

## Features

### Authentication & Security
- User registration with email validation
- Secure login with session management
- Remember-me functionality with secure tokens
- Account lockout after 7 failed login attempts (30 minutes)
- Login attempt logging (IP, timestamp, success/fail)
- Auto logout after 15 minutes of inactivity
- Password hashing using bcrypt

### User Management
- User roles: User, Instructor, Admin
- Profile management with profile picture upload
- Admin can view/edit all users and change roles
- Admin can create new users

### Course Management
- Course catalog with search functionality
- Course details page
- Course creation/editing for instructors
- Course enrollment tracking
- My Courses page for enrolled courses

### Payment Integration
- Stripe payment processing
- Payment logging for all transactions
- Admin view of payment logs

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP/WAMP (for local development)
- Composer (for Stripe PHP library)
- Stripe account (for payment processing)

## Installation

1. **Clone or extract the project** to your XAMPP/WAMP htdocs directory:
   ```
   C:\xampp\htdocs\e-learning-platform\
   ```

2. **Create the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the file `database/schema.sql`
   - This will create the database and all required tables

3. **Configure the database**:
   - Edit `config/config.php` if your MySQL credentials differ:
     ```php
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Install Composer dependencies**:
   ```bash
   composer install
   ```
   Or download Stripe PHP library manually and place it in a `vendor` directory.

5. **Configure Stripe** (optional, for payment features):
   - Edit `config/config.php`:
     ```php
     define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_key_here');
     define('STRIPE_SECRET_KEY', 'sk_test_your_key_here');
     ```
   - Get your keys from: https://dashboard.stripe.com/test/apikeys

6. **Create upload directories**:
   ```bash
   mkdir uploads
   mkdir uploads/profiles
   mkdir uploads/courses
   ```
   Make sure these directories are writable by the web server.

7. **Access the application**:
   - Open your browser and go to: `http://localhost/e-learning-platform/`

## Default Admin Account

After importing the database, you can login with:
- **Email**: admin@example.com
- **Password**: Admin123

**Important**: Change this password immediately in production!

## Project Structure

```
e-learning-platform/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   ├── config.php          # Configuration settings
│   └── database.php         # Database connection
├── database/
│   └── schema.sql          # Database schema
├── includes/
│   ├── auth.php            # Authentication functions
│   ├── functions.php       # Helper functions
│   ├── header.php          # Page header/navbar
│   └── footer.php          # Page footer
├── pages/
│   ├── admin/
│   │   ├── users.php       # User management
│   │   └── stripe-logs.php # Payment logs
│   ├── course-details.php  # Course details page
│   ├── courses.php         # Course catalog
│   ├── create-course.php   # Create/edit courses
│   ├── checkout.php        # Payment checkout
│   ├── login.php           # Login page
│   ├── logout.php          # Logout handler
│   ├── my-courses.php      # Enrolled courses
│   ├── process-payment.php # Stripe payment processing
│   ├── profile.php         # User profile
│   ├── register.php        # Registration page
│   └── verify-email.php    # Email verification
├── uploads/                # User uploaded files
│   ├── profiles/           # Profile pictures
│   └── courses/            # Course thumbnails
├── .htaccess               # Apache configuration
├── composer.json           # Composer dependencies
├── index.php               # Home page
└── README.md               # This file
```

## Database Tables

1. **users** - User accounts
2. **email_verification_codes** - Email verification
3. **login_attempts** - Login attempt logging
4. **remember_tokens** - Remember-me tokens
5. **sessions** - Active sessions
6. **courses** - Course information
7. **enrollments** - Course enrollments
8. **stripe_logs** - Payment transaction logs

## Security Features

- SQL injection prevention (PDO prepared statements)
- XSS protection (input sanitization)
- CSRF protection (session-based)
- Password hashing (bcrypt)
- Secure session management
- File upload validation
- Account lockout mechanism

## Testing Stripe Payments

Use Stripe test cards:
- **Success**: 4242 4242 4242 4242
- **Decline**: 4000 0000 0000 0002
- Use any future expiry date and any CVC

## Notes

- This is a student project for learning purposes
- Not recommended for production use without additional security hardening
- Email verification codes are stored in database (check `email_verification_codes` table for demo)
- In production, implement actual email sending functionality

## License

This project is for educational purposes only.

