-- Real-World Cinema Upgrade Schema

SET FOREIGN_KEY_CHECKS=0;

-- 1. Modify Movies Table
ALTER TABLE movies ADD COLUMN IF NOT EXISTS tmdb_id INT UNIQUE NULL AFTER movie_id;
ALTER TABLE movies ADD COLUMN IF NOT EXISTS is_indian_release TINYINT(1) DEFAULT 1 AFTER tmdb_id;

-- 2. Theaters (Gujarat Specific)
DROP TABLE IF EXISTS theaters;
CREATE TABLE theaters (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  city VARCHAR(100) NOT NULL COMMENT 'Gujarat Cities: Ahmedabad, Gandhinagar, Surat, Vadodara, Rajkot',
  address TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Screens
DROP TABLE IF EXISTS screens;
CREATE TABLE screens (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  theater_id INT NOT NULL,
  screen_name VARCHAR(100) NOT NULL,
  total_seats INT NOT NULL DEFAULT 0,
  FOREIGN KEY (theater_id) REFERENCES theaters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Seats
DROP TABLE IF EXISTS seats;
CREATE TABLE seats (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  screen_id INT NOT NULL,
  row_name VARCHAR(5) NOT NULL,
  seat_number INT NOT NULL,
  seat_type VARCHAR(20) NOT NULL DEFAULT 'Regular',
  FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE,
  UNIQUE KEY unique_seat (screen_id, row_name, seat_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Shows
DROP TABLE IF EXISTS shows;
CREATE TABLE shows (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  movie_id INT NOT NULL,
  theater_id INT NOT NULL,
  screen_id INT NOT NULL,
  show_date DATE NOT NULL,
  show_time TIME NOT NULL,
  base_price DECIMAL(10,2) NOT NULL DEFAULT 150.00,
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
  FOREIGN KEY (theater_id) REFERENCES theaters(id) ON DELETE CASCADE,
  FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Dynamic Seat Pricing
DROP TABLE IF EXISTS seat_pricing;
CREATE TABLE seat_pricing (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  show_id INT NOT NULL,
  seat_type VARCHAR(20) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE,
  UNIQUE KEY unique_show_type (show_id, seat_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Modify Bookings
-- Add 'show_id'. Ignoring errors if columns already dropped.
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS show_id INT NULL AFTER user_id;
ALTER TABLE bookings DROP COLUMN IF EXISTS movie_id;
ALTER TABLE bookings DROP COLUMN IF EXISTS theater;
ALTER TABLE bookings DROP COLUMN IF EXISTS showtime;

-- 8. Booked Seats Mapping
DROP TABLE IF EXISTS booked_seats;
CREATE TABLE booked_seats (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  seat_id INT NOT NULL,
  FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
  FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cleanup Deprecated Tables
DROP TABLE IF EXISTS showtimes;
DROP TABLE IF EXISTS `seat table`;

SET FOREIGN_KEY_CHECKS=1;
