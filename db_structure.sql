CREATE DATABASE IF NOT EXISTS coastal_villa
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE coastal_villa;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    contact_number VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('guest', 'admin') NOT NULL DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(100) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    facilities TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_guests INT NOT NULL,
    room_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    image_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_booking_availability
        (room_id, booking_status, check_in, check_out),

    CONSTRAINT fk_booking_user
        FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_booking_room
        FOREIGN KEY (room_id) REFERENCES rooms(room_id)
) ENGINE=InnoDB;

INSERT INTO rooms
    (room_name, room_type, description, facilities,
     price_per_night, max_guests, room_status, image_name)
VALUES
    ('Ocean View Deluxe', 'Deluxe',
     'A comfortable room with a view of the ocean.',
     'Wi-Fi, Air conditioning, Breakfast',
     18000.00, 2, 'active', 'ocean-deluxe.jpg'),

    ('Garden Family Room', 'Family',
     'A spacious room for a small family, close to the garden.',
     'Wi-Fi, Air conditioning, Garden view',
     25000.00, 4, 'active', 'garden-family.jpg'),

    ('Cozy Garden Room', 'Standard',
     'A simple, relaxing room surrounded by tropical plants.',
     'Wi-Fi, Fan, Private bathroom',
     12000.00, 2, 'active', 'cozy-garden.jpg');