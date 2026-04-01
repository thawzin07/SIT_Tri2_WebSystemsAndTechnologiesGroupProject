-- Auto-generated canonical migration script
-- Source: database/migrations/001..003

CREATE DATABASE IF NOT EXISTS pulsepoint_fitness CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pulsepoint_fitness;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE membership_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  duration_months INT NOT NULL,
  description TEXT NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE memberships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  plan_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_memberships_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_memberships_plan FOREIGN KEY (plan_id) REFERENCES membership_plans(id)
);

CREATE TABLE trainers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  specialty VARCHAR(120) NOT NULL,
  bio TEXT NOT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE gym_locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  address VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  opening_hours VARCHAR(120) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trainer_id INT NOT NULL,
  location_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  class_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  capacity INT NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_classes_trainer FOREIGN KEY (trainer_id) REFERENCES trainers(id),
  CONSTRAINT fk_classes_location FOREIGN KEY (location_id) REFERENCES gym_locations(id)
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  class_id INT NOT NULL,
  booking_status ENUM('booked','cancelled','completed') NOT NULL DEFAULT 'booked',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  UNIQUE KEY uq_active_booking (user_id, class_id, booking_status)
);

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- 002_waitlist_and_booking_integrity.sql
-- ========================================
ALTER TABLE classes
  ADD INDEX idx_classes_status_date_time (status, class_date, start_time);

ALTER TABLE bookings
  ADD INDEX idx_bookings_class_status (class_id, booking_status),
  ADD INDEX idx_bookings_user_status (user_id, booking_status),
  ADD INDEX idx_bookings_created_at (created_at);

CREATE TABLE class_waitlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  class_id INT NOT NULL,
  waitlist_status ENUM('waiting','promoted','removed') NOT NULL DEFAULT 'waiting',
  promoted_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_waitlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_waitlist_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  UNIQUE KEY uq_waitlist_user_class (user_id, class_id),
  INDEX idx_waitlist_class_status_created (class_id, waitlist_status, created_at),
  INDEX idx_waitlist_user_status (user_id, waitlist_status)
);

-- ========================================
-- 003_add_media_payment_notifications_qr.sql
-- ========================================
USE pulsepoint_fitness;

START TRANSACTION;

-- 1) MEDIA FIELDS

-- users: profile image
ALTER TABLE users
  ADD COLUMN profile_image_path VARCHAR(255) NULL AFTER phone;

-- trainers: optional image alt text (image_path already exists)
ALTER TABLE trainers
  ADD COLUMN image_alt VARCHAR(120) NULL AFTER image_path;

-- gym_locations: image + map coordinates
ALTER TABLE gym_locations
  ADD COLUMN image_path VARCHAR(255) NULL AFTER opening_hours,
  ADD COLUMN latitude DECIMAL(10,7) NULL AFTER image_path,
  ADD COLUMN longitude DECIMAL(10,7) NULL AFTER latitude,
  ADD COLUMN map_place_id VARCHAR(120) NULL AFTER longitude;

CREATE INDEX idx_gym_locations_lat_lng ON gym_locations (latitude, longitude);

-- 2) PAYMENTS

CREATE TABLE payments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  membership_id INT NULL,
  plan_id INT NOT NULL,
  provider ENUM('stripe') NOT NULL DEFAULT 'stripe',
  provider_session_id VARCHAR(191) NOT NULL,
  provider_payment_intent_id VARCHAR(191) NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  payment_type ENUM('purchase','renew') NOT NULL,
  status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_membership FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL,
  CONSTRAINT fk_payments_plan FOREIGN KEY (plan_id) REFERENCES membership_plans(id),
  UNIQUE KEY uq_payments_provider_session (provider_session_id),
  UNIQUE KEY uq_payments_payment_intent (provider_payment_intent_id),
  INDEX idx_payments_user_created (user_id, created_at),
  INDEX idx_payments_status_created (status, created_at)
);

-- 3) INVOICES

CREATE TABLE invoices (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  payment_id BIGINT NOT NULL,
  user_id INT NOT NULL,
  invoice_no VARCHAR(40) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  pdf_path VARCHAR(255) NOT NULL,
  issued_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoices_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
  CONSTRAINT fk_invoices_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uq_invoices_payment (payment_id),
  UNIQUE KEY uq_invoices_invoice_no (invoice_no),
  INDEX idx_invoices_user_issued (user_id, issued_at)
);

-- 4) NOTIFICATION LOGS (Email + Telegram)

CREATE TABLE notification_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  channel ENUM('email','telegram') NOT NULL,
  event_type ENUM('payment_success','membership_renewed','invoice_sent') NOT NULL,
  target VARCHAR(191) NOT NULL,
  status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
  error_message VARCHAR(255) NULL,
  payload_json JSON NULL,
  sent_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notification_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notification_logs_user_created (user_id, created_at),
  INDEX idx_notification_logs_channel_status (channel, status)
);

-- 5) QR TOKENS + CHECK-IN

CREATE TABLE member_qr_tokens (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_member_qr_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uq_member_qr_tokens_user (user_id),
  UNIQUE KEY uq_member_qr_tokens_hash (token_hash)
);

CREATE TABLE check_ins (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  location_id INT NOT NULL,
  checkin_method ENUM('qr') NOT NULL DEFAULT 'qr',
  scanned_by_admin_id INT NULL,
  checked_in_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_check_ins_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_check_ins_location FOREIGN KEY (location_id) REFERENCES gym_locations(id) ON DELETE CASCADE,
  CONSTRAINT fk_check_ins_admin FOREIGN KEY (scanned_by_admin_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_check_ins_user_time (user_id, checked_in_at),
  INDEX idx_check_ins_location_time (location_id, checked_in_at)
);

COMMIT;
