# 🎬 CineTime - Movie Ticket Booking System

CineTime is a web-based movie ticket booking platform developed using **PHP, MySQL, and TCPDF**. The application provides a complete online movie booking experience where users can browse movies, select seats, make payments, and download digitally generated e-tickets.

The system is designed to simulate a real-world cinema booking platform with features such as user authentication, seat selection, payment integration, booking management, PDF ticket generation, and an admin dashboard for managing movies and bookings.

---

# 🚀 Features

## 👤 User Features

- User registration and login system
- Browse available movies
- View movie details and show information
- Select seats using an interactive seat selection interface
- Book movie tickets
- Online payment integration using Razorpay
- Booking confirmation system
- Download PDF e-tickets
- View booking details

---

## 🧑‍💼 Admin Features

- Secure admin login
- Admin dashboard
- Add, update, and delete movies
- Manage movie listings
- View user bookings
- Monitor booking activities

---

## 🎟️ Ticket Management

- Automatic ticket generation after successful booking
- PDF ticket generation using TCPDF
- Includes:
  - Movie details
  - Booking ID
  - User information
  - Seat details
  - Payment information

---

## 🤖 Additional Features

- Integrated chatbot functionality
- Movie booking wizard flow
- Dynamic movie management
- Responsive user interface

---

# 🛠️ Tech Stack

## Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap
- SCSS

## Backend

- PHP

## Database

- MySQL

## Libraries & Tools

- TCPDF (PDF Ticket Generation)
- Razorpay Payment Gateway
- XAMPP (Apache + MySQL)
- Git & GitHub

---

# 📂 Project Structure

```
CineTime/
│
├── admin/                    # Admin dashboard and management features
│
├── assets/                   # Images and static resources
│
├── css/                      # CSS stylesheets
│
├── js/                       # JavaScript files
│
├── scss/                     # SCSS source files
│
├── lib/                      # Supporting libraries
│
├── TCPDF/                    # TCPDF library for PDF generation
│
├── moives/                   # Movie related assets
│
├── config.php                # Database configuration
├── connection.php            # Database connection
├── connect.php               # Database connectivity file
├── config_razorpay.php       # Razorpay configuration
│
├── index.php                 # Home page
├── login.php                 # User login
├── register.php              # User registration
├── logout.php                # Logout handler
│
├── booking.php               # Ticket booking page
├── seat_selection.php        # Seat selection module
├── payment.php               # Payment page
├── process_payment.php       # Payment processing logic
│
├── confirmation.php           # Booking confirmation
├── download_ticket.php        # PDF ticket download
│
├── admin.php                 # Admin panel
├── admin_login.php           # Admin authentication
│
├── wizard.php                # Booking wizard
├── api_wizard.php            # Wizard API handling
│
├── chatbot.php               # Chatbot module
│
├── database_setup.sql        # Database setup file
├── cinema_upgrade.sql        # Updated database schema
│
├── header.php                # Common header component
├── footer.php                # Common footer component
│
└── README.md                 # Project documentation
```

---

# ⚙️ Installation & Setup

Follow these steps to run CineTime locally.

## 1. Clone the Repository

```bash
git clone https://github.com/Rishikashah2674/cinetime-movie-booking-system.git
```

Navigate into the project folder:

```bash
cd cinetime
```

---

## 2. Setup XAMPP Server

1. Install and open XAMPP
2. Start:
   - Apache
   - MySQL

3. Move the project folder into:

```
xampp/htdocs/
```

Example:

```
xampp/htdocs/cinetime
```

---

## 3. Setup Database

1. Open phpMyAdmin:

```
http://localhost/phpmyadmin
```

2. Create a database:

```
cinetime
```

3. Import the database file:

```
database_setup.sql
```

or

```
cinema_upgrade.sql
```

---

## 4. Configure Database Connection

Open:

```
config.php
```

Update database credentials:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "cinetime";
```

---

## 5. Configure Razorpay Payment

Open:

```
config_razorpay.php
```

Add your Razorpay API credentials:

```
RAZORPAY_KEY_ID
RAZORPAY_SECRET_KEY
```

---

## 6. Run the Application

Open your browser:

```
http://localhost/cinetime
```

---

# 🔄 Application Workflow

1. User registers an account
2. User logs into the system
3. User browses available movies
4. User selects movie and show details
5. User selects available seats
6. User completes payment
7. Booking details are stored in MySQL database
8. PDF e-ticket is generated
9. User downloads the ticket

---

# 🗄️ Database Modules

The database manages:

- Users
- Movies
- Bookings
- Seats
- Payments
- Admin records

---

# 🚀 Future Enhancements

- Real-time seat availability
- Email ticket delivery
- Movie ratings and reviews
- Online movie recommendations
- Mobile application version
- Advanced analytics dashboard

---

# 🎯 Learning Outcomes

Through CineTime, the project demonstrates:

- Full-stack PHP development
- Database design and management
- Authentication and session handling
- CRUD operations
- Payment gateway integration
- PDF generation
- Real-world booking system architecture

---

# 👨‍💻 Author

**Your Name**

GitHub:
```
https://github.com/Rishikashah2674
```

---

# 📌 Disclaimer

This project is developed for educational and portfolio purposes to demonstrate web development skills and implementation of a real-world movie booking system.
