# CineTime – Movie Ticket Booking System

CineTime is a web-based movie ticket booking system built using **PHP, MySQL, and TCPDF**.  
It allows users to browse movies, book tickets, and download e-tickets in PDF format, while an admin manages movies and bookings.

---

## Features

### User Features
- User registration and login system  
- Browse available movies  
- Book movie tickets  
- View booking history  
- Download e-ticket in PDF format  

### Ticket System
- Automatic ticket generation after booking  
- PDF e-ticket using TCPDF  
- Includes movie details, booking ID, and seat info  

### Admin Features
- Add, update, delete movies  
- Manage bookings  
- View user activity  

---

## Tech Stack

- Frontend: HTML, CSS, JavaScript, Bootstrap  
- Backend: PHP  
- Database: MySQL  
- PDF Generation: TCPDF  
- Server: XAMPP / Apache  

---

## Installation & Setup

### 1. Clone the repository
```bash
git clone https://github.com/Rishikashah2674/cinetime-movie-booking-system.git
### 1. Clone the repository
```bash
git clone https://github.com/your-username/cinetime.git

---

## CineTime – Project Structure

cinetime/
│
├── admin/                   # Admin dashboard panel
│
├── assets/                 # Static assets (images, media, etc.)
├── css/                    # Stylesheets
├── js/                     # JavaScript files
├── scss/                  # SCSS source files
├── lib/                   # External libraries / helpers
│
├── moives/                # Movie-related assets/data (rename to "movies")
├── TCPDF/                 # PDF generation library (e-ticket system)
│
├── config.php            # Database configuration
├── connection.php        # DB connection handler
├── connect.php           # Alternate DB connection file
├── config_razorpay.php   # Razorpay payment configuration
│
├── index.php             # Home page
├── login.php             # User login
├── register.php          # User registration
├── logout.php            # Logout handler
│
├── booking.php           # Ticket booking system
├── seat_selection.php    # Seat selection UI
├── payment.php           # Payment page
├── process_payment.php   # Payment processing logic
│
├── confirmation.php      # Booking confirmation page
├── download_ticket.php   # PDF ticket download (TCPDF)
│
├── admin.php             # Admin dashboard
├── admin_login.php       # Admin login system
│
├── wizard.php            # Movie booking wizard flow
├── api_wizard.php        # API for wizard functionality
├── chatbot.php           # Chatbot feature (extra feature)
│
├── contact.php           # Contact page
├── about.php             # About page
│
├── database_setup.sql    # Database setup file
├── cinema_upgrade.sql    # Updated DB schema
│
├── header.php            # Common header
├── footer.php            # Common footer
├── styles.css            # Main stylesheet
│
└── README.md             # Project documentation
