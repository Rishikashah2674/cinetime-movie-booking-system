-- 1. Updates to the bookings table to track user and payment state
ALTER TABLE bookings ADD COLUMN user_id INT NULL;
ALTER TABLE bookings ADD COLUMN payment_status VARCHAR(50) DEFAULT 'Pending';

-- 2. Create the payments table to store detailed gateway transaction data
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    razorpay_payment_id VARCHAR(255) NOT NULL,
    razorpay_order_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);
