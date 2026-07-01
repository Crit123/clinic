drop database clinicdb;
create database clinicdb;
use clinicdb;

CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  first_name    VARCHAR(100) NOT NULL,
  last_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(255) NOT NULL UNIQUE,
  phone         VARCHAR(30) NULL,               -- ADDED MISSING PHONE COLUMN HERE
  password_hash VARCHAR(255) NOT NULL,          -- password_hash(..., PASSWORD_BCRYPT)
  remember_token VARCHAR(64) NULL,              -- for "Keep me logged in"
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE users
ADD COLUMN google_id VARCHAR(255) NULL,
ADD COLUMN auth_provider ENUM('local','google') DEFAULT 'local';

CREATE TABLE bookings (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT NULL,                       -- FK to users (NULL if guest booking)
  reference_code VARCHAR(20) NOT NULL UNIQUE,   -- e.g. #DCP-8X92-ALQ
  first_name     VARCHAR(100) NOT NULL,
  last_name      VARCHAR(100) NOT NULL,
  email          VARCHAR(255) NOT NULL,
  phone          VARCHAR(30) NOT NULL,
  service_key    VARCHAR(100) NOT NULL,          -- matches service category key
  dentist_name   VARCHAR(150) NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time VARCHAR(20) NOT NULL,
  status         ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_resets (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(255) NOT NULL,
  otp_code    VARCHAR(10) NOT NULL,
  expires_at  TIMESTAMP NOT NULL,
  attempts    TINYINT DEFAULT 0,
  verified    TINYINT(1) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cancellation_logs (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  booking_id     INT NOT NULL,
  user_id        INT NOT NULL,
  reference_code VARCHAR(20) NOT NULL,
  cancelled_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reason         ENUM('user_requested', 'late_cancellation', 'no_show') DEFAULT 'user_requested',
  notes          TEXT NULL,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
);

-- ADDED: Fixes missing field from payload in booking-create.php so patient notes are not discarded
ALTER TABLE bookings 
ADD COLUMN notes TEXT NULL AFTER phone;

-- ADDED: Performance index for availability.php, booking-create.php, and slot-stream.php (filters on appointment_date + status)
ALTER TABLE bookings 
ADD INDEX idx_date_status (appointment_date, status);

-- ADDED: Performance index for user-bookings.php and check-patient.php (filters on email)
ALTER TABLE bookings 
ADD INDEX idx_email (email);

-- ADDED: Performance index for queried email during password reset flows
ALTER TABLE password_resets 
ADD INDEX idx_email (email);

-- ADDED: Defense-in-depth against double-booking on top of SELECT ... FOR UPDATE transaction logic in booking-create.php
-- Note: this constraint is best-effort and blocks conflicts when status is 'pending' or 'confirmed'. 
-- The real guarantee remains the transaction in booking-create.php.
ALTER TABLE bookings 
ADD UNIQUE KEY uq_slot (appointment_date, appointment_time, dentist_name, status);

-- ADDED: Improve remember_token security on users
-- Note: remember_token values should be stored as a hash (e.g. SHA-256) going forward, not the raw token — this is an application-layer change to flag for the auth code.
ALTER TABLE users 
ADD COLUMN remember_token_expires_at TIMESTAMP NULL AFTER remember_token;

-- ADDED: Generic rate-limiting table, to be reused by check-patient.php and booking-lookup.php
CREATE TABLE IF NOT EXISTS rate_limits (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  identifier    VARCHAR(255) NOT NULL,   -- e.g. IP address or IP+endpoint
  endpoint      VARCHAR(100) NOT NULL,
  attempt_count INT NOT NULL DEFAULT 1,
  window_start  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_identifier_endpoint (identifier, endpoint)
);

select * from users;
select * from bookings;