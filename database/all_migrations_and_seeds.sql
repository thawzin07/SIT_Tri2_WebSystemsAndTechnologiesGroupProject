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


USE pulsepoint_fitness;

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE check_ins;
TRUNCATE TABLE member_qr_tokens;
TRUNCATE TABLE notification_logs;
TRUNCATE TABLE invoices;
TRUNCATE TABLE payments;
TRUNCATE TABLE class_waitlist;
TRUNCATE TABLE bookings;
TRUNCATE TABLE contact_messages;
TRUNCATE TABLE classes;
TRUNCATE TABLE gym_locations;
TRUNCATE TABLE trainers;
TRUNCATE TABLE memberships;
TRUNCATE TABLE membership_plans;
TRUNCATE TABLE users;
TRUNCATE TABLE roles;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'member');

-- Passwords used in README:
-- admin@pulsepoint.test / Admin@123
-- member@pulsepoint.test / Member@123
INSERT INTO users (role_id, full_name, email, password_hash, phone) VALUES
(1, 'System Admin', 'admin@pulsepoint.test', '$2b$12$.bExm4I/cenZ2K0JxL0nDuytbXdEu7xtWQcJcbbxPqSGF7EGeA74m', '+65 9000 1001'),
(2, 'Jamie Member', 'member@pulsepoint.test', '$2b$12$Z1MB2rPZ1A7QpEpcFCt.nOSnxnJ9FroNsnsqvBNSN72QhFoSBz5r.', '+65 9000 2002'),
(2, 'Alicia Tan', 'alicia@pulsepoint.test', '$2b$12$Z1MB2rPZ1A7QpEpcFCt.nOSnxnJ9FroNsnsqvBNSN72QhFoSBz5r.', '+65 9000 2003'),
(2, 'Marcus Lim', 'marcus@pulsepoint.test', '$2b$12$Z1MB2rPZ1A7QpEpcFCt.nOSnxnJ9FroNsnsqvBNSN72QhFoSBz5r.', '+65 9000 2004'),
(2, 'Priya Nair', 'priya@pulsepoint.test', '$2b$12$Z1MB2rPZ1A7QpEpcFCt.nOSnxnJ9FroNsnsqvBNSN72QhFoSBz5r.', '+65 9000 2005');

INSERT INTO membership_plans (name, price, duration_months, description, status) VALUES
('Starter Flex', 59.00, 1, 'Access to gym floor and selected group classes during staffed hours.', 'active'),
('Performance Plus', 149.00, 3, 'Unlimited classes, priority waitlist promotion, and quarterly trainer check-ins.', 'active'),
('Elite Annual', 499.00, 12, 'Best-value annual commitment with full access, class priority, and partner perks.', 'active'),
('Student Off-Peak', 39.00, 1, 'Budget-friendly off-peak access for students on weekdays before 5 PM.', 'inactive');

INSERT INTO memberships (user_id, plan_id, start_date, end_date, status) VALUES
((SELECT id FROM users WHERE email = 'member@pulsepoint.test'), (SELECT id FROM membership_plans WHERE name = 'Performance Plus'), DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 76 DAY), 'active'),
((SELECT id FROM users WHERE email = 'alicia@pulsepoint.test'), (SELECT id FROM membership_plans WHERE name = 'Starter Flex'), DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'expired'),
((SELECT id FROM users WHERE email = 'marcus@pulsepoint.test'), (SELECT id FROM membership_plans WHERE name = 'Elite Annual'), DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_ADD(CURDATE(), INTERVAL 7 MONTH), 'active'),
((SELECT id FROM users WHERE email = 'priya@pulsepoint.test'), (SELECT id FROM membership_plans WHERE name = 'Starter Flex'), DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'active');

INSERT INTO trainers (name, specialty, bio, image_path, status) VALUES
('Aiden Cruz', 'Strength & Conditioning', 'Specializes in athletic performance programming and movement efficiency for intermediate lifters.', '', 'active'),
('Maya Tan', 'HIIT & Fat Loss', 'Builds structured cardio and metabolic sessions for sustainable fat loss and endurance gains.', '', 'active'),
('Noah Lim', 'Mobility & Recovery', 'Focuses on flexibility, joint health, and smart recovery for desk-bound professionals.', '', 'active'),
('Hannah Teo', 'Functional Training', 'Leads functional strength sessions for daily movement, posture, and balance.', '', 'active'),
('Ethan Goh', 'Rehab & Correctives', 'Designs low-impact classes for return-to-training pathways after minor injuries.', '', 'inactive');

INSERT INTO gym_locations (name, address, phone, opening_hours, status) VALUES
('PulsePoint Downtown', '101 Core Street, Central City', '+65 6123 1111', '6:00 AM - 11:00 PM', 'active'),
('PulsePoint Riverside', '88 River Lane, West District', '+65 6123 2222', '24 Hours', 'active'),
('PulsePoint East Hub', '12 Harbour View, East District', '+65 6123 3333', '6:00 AM - 10:00 PM', 'active');

INSERT INTO classes (trainer_id, location_id, title, description, class_date, start_time, end_time, capacity, status) VALUES
((SELECT id FROM trainers WHERE name = 'Aiden Cruz'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint Downtown'), 'Power Lift Fundamentals', 'Technique-first barbell mechanics with progressive loading.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', '19:00:00', 20, 'active'),
((SELECT id FROM trainers WHERE name = 'Maya Tan'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint Riverside'), 'Pulse HIIT', 'High-intensity intervals to build stamina and improve conditioning.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '19:00:00', '20:00:00', 25, 'active'),
((SELECT id FROM trainers WHERE name = 'Noah Lim'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint Downtown'), 'Mobility Reset', 'Guided mobility drills and cooldown protocols for full-body recovery.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '17:30:00', '18:30:00', 18, 'active'),
((SELECT id FROM trainers WHERE name = 'Hannah Teo'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint East Hub'), 'Core & Stability', 'Functional core training focused on posture and injury prevention.', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '07:30:00', '08:15:00', 16, 'active'),
((SELECT id FROM trainers WHERE name = 'Aiden Cruz'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint Downtown'), 'Strength Lab', 'Advanced compound lifts with coached technique checkpoints.', DATE_ADD(CURDATE(), INTERVAL 6 DAY), '20:00:00', '21:00:00', 15, 'active'),
((SELECT id FROM trainers WHERE name = 'Maya Tan'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint Riverside'), 'Lunch Burn Express', 'Time-efficient lunchtime workout for busy professionals.', DATE_ADD(CURDATE(), INTERVAL 8 DAY), '12:15:00', '13:00:00', 22, 'active'),
((SELECT id FROM trainers WHERE name = 'Noah Lim'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint East Hub'), 'Weekend Recovery Flow', 'Gentle weekend session for flexibility and stress relief.', DATE_ADD(CURDATE(), INTERVAL 10 DAY), '10:00:00', '11:00:00', 24, 'active'),
((SELECT id FROM trainers WHERE name = 'Hannah Teo'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint Downtown'), 'Foundations Bootcamp', 'Beginner-friendly full-body routine with scalable options.', DATE_ADD(CURDATE(), INTERVAL 12 DAY), '18:30:00', '19:30:00', 28, 'active'),
((SELECT id FROM trainers WHERE name = 'Ethan Goh'), (SELECT id FROM gym_locations WHERE name = 'PulsePoint Riverside'), 'Joint Friendly Conditioning', 'Low-impact conditioning with corrective warm-up sets.', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '09:00:00', '10:00:00', 12, 'inactive');

INSERT INTO bookings (user_id, class_id, booking_status) VALUES
((SELECT id FROM users WHERE email = 'member@pulsepoint.test'), (SELECT id FROM classes WHERE title = 'Power Lift Fundamentals' AND class_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)), 'booked'),
((SELECT id FROM users WHERE email = 'member@pulsepoint.test'), (SELECT id FROM classes WHERE title = 'Pulse HIIT' AND class_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY)), 'booked'),
((SELECT id FROM users WHERE email = 'marcus@pulsepoint.test'), (SELECT id FROM classes WHERE title = 'Strength Lab' AND class_date = DATE_ADD(CURDATE(), INTERVAL 6 DAY)), 'booked'),
((SELECT id FROM users WHERE email = 'priya@pulsepoint.test'), (SELECT id FROM classes WHERE title = 'Core & Stability' AND class_date = DATE_ADD(CURDATE(), INTERVAL 4 DAY)), 'booked');

INSERT INTO class_waitlist (user_id, class_id, waitlist_status) VALUES
((SELECT id FROM users WHERE email = 'alicia@pulsepoint.test'), (SELECT id FROM classes WHERE title = 'Pulse HIIT' AND class_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY)), 'waiting'),
((SELECT id FROM users WHERE email = 'priya@pulsepoint.test'), (SELECT id FROM classes WHERE title = 'Strength Lab' AND class_date = DATE_ADD(CURDATE(), INTERVAL 6 DAY)), 'waiting');

INSERT INTO contact_messages (name, email, subject, message) VALUES
('Taylor Lee', 'taylor@example.com', 'Corporate Membership', 'Can your team provide a company wellness package with monthly billing?'),
('Isha Rao', 'isha.rao@example.com', 'Trial Class', 'I am new to resistance training. Which class should I start with next week?'),
('Ken Wong', 'ken.wong@example.com', 'Operating Hours', 'Will Downtown open earlier on public holidays?');

COMMIT;


USE pulsepoint_fitness;

START TRANSACTION;
SET @OLD_SQL_SAFE_UPDATES := @@SQL_SAFE_UPDATES;
SET SQL_SAFE_UPDATES = 0;

-- 1) DEMO IMAGE PATHS

UPDATE users
SET profile_image_path = 'profiles/member-2.jpg'
WHERE email = 'member@pulsepoint.test';

UPDATE trainers
SET image_path = CASE name
  WHEN 'Aiden Cruz' THEN 'trainers/aiden-cruz.jpg'
  WHEN 'Maya Tan' THEN 'trainers/maya-tan.jpg'
  WHEN 'Noah Lim' THEN 'trainers/noah-lim.jpg'
  WHEN 'Hannah Teo' THEN 'trainers/hannah-teo.jpg'
  ELSE image_path
END,
image_alt = CASE name
  WHEN 'Aiden Cruz' THEN 'Trainer Aiden Cruz portrait'
  WHEN 'Maya Tan' THEN 'Trainer Maya Tan portrait'
  WHEN 'Noah Lim' THEN 'Trainer Noah Lim portrait'
  WHEN 'Hannah Teo' THEN 'Trainer Hannah Teo portrait'
  ELSE image_alt
END
WHERE name IN ('Aiden Cruz', 'Maya Tan', 'Noah Lim', 'Hannah Teo');

UPDATE gym_locations
SET image_path = CASE name
  WHEN 'PulsePoint Downtown' THEN 'locations/pulsepoint-downtown.jpg'
  WHEN 'PulsePoint Riverside' THEN 'locations/pulsepoint-riverside.jpg'
  WHEN 'PulsePoint East Hub' THEN 'locations/pulsepoint-east-hub.jpg'
  ELSE image_path
END,
latitude = CASE name
  WHEN 'PulsePoint Downtown' THEN 1.2902700
  WHEN 'PulsePoint Riverside' THEN 1.3001000
  WHEN 'PulsePoint East Hub' THEN 1.3181000
  ELSE latitude
END,
longitude = CASE name
  WHEN 'PulsePoint Downtown' THEN 103.8519590
  WHEN 'PulsePoint Riverside' THEN 103.8455000
  WHEN 'PulsePoint East Hub' THEN 103.9138000
  ELSE longitude
END,
map_place_id = CASE name
  WHEN 'PulsePoint Downtown' THEN 'demo_place_downtown'
  WHEN 'PulsePoint Riverside' THEN 'demo_place_riverside'
  WHEN 'PulsePoint East Hub' THEN 'demo_place_east_hub'
  ELSE map_place_id
END
WHERE name IN ('PulsePoint Downtown', 'PulsePoint Riverside', 'PulsePoint East Hub');

-- 2) DEMO PAYMENT

INSERT INTO payments (
  user_id,
  membership_id,
  plan_id,
  provider,
  provider_session_id,
  provider_payment_intent_id,
  amount,
  currency,
  payment_type,
  status,
  paid_at
)
SELECT
  (SELECT id FROM users WHERE email = 'member@pulsepoint.test'),
  (SELECT m.id
   FROM memberships m
   JOIN users u ON u.id = m.user_id
   WHERE u.email = 'member@pulsepoint.test'
   ORDER BY m.id DESC
   LIMIT 1),
  (SELECT id FROM membership_plans WHERE name = 'Performance Plus'),
  'stripe',
  'cs_test_demo_member2_20260315',
  'pi_test_demo_member2_20260315',
  149.00,
  'USD',
  'renew',
  'paid',
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM payments WHERE provider_session_id = 'cs_test_demo_member2_20260315'
);

-- 3) DEMO INVOICE

INSERT INTO invoices (
  payment_id,
  user_id,
  invoice_no,
  subtotal,
  tax,
  total,
  currency,
  pdf_path,
  issued_at
)
SELECT
  p.id,
  p.user_id,
  'INV-20260315-0001',
  149.00,
  0.00,
  149.00,
  'USD',
  'invoices/INV-20260315-0001.pdf',
  NOW()
FROM payments p
WHERE p.provider_session_id = 'cs_test_demo_member2_20260315'
  AND NOT EXISTS (
    SELECT 1 FROM invoices WHERE invoice_no = 'INV-20260315-0001'
  );

-- 4) DEMO NOTIFICATION LOGS

INSERT INTO notification_logs (
  user_id,
  channel,
  event_type,
  target,
  status,
  payload_json,
  sent_at
)
SELECT
  (SELECT id FROM users WHERE email = 'member@pulsepoint.test'),
  'email',
  'payment_success',
  'member@pulsepoint.test',
  'sent',
  JSON_OBJECT('invoice_no', 'INV-20260315-0001', 'amount', 149.00, 'currency', 'USD'),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM notification_logs
  WHERE user_id = (SELECT id FROM users WHERE email = 'member@pulsepoint.test')
    AND channel = 'email'
    AND event_type = 'payment_success'
    AND target = 'member@pulsepoint.test'
);

INSERT INTO notification_logs (
  user_id,
  channel,
  event_type,
  target,
  status,
  payload_json,
  sent_at
)
SELECT
  (SELECT id FROM users WHERE email = 'member@pulsepoint.test'),
  'telegram',
  'invoice_sent',
  'demo_chat_member_2',
  'sent',
  JSON_OBJECT('invoice_no', 'INV-20260315-0001', 'pdf_path', 'invoices/INV-20260315-0001.pdf'),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM notification_logs
  WHERE user_id = (SELECT id FROM users WHERE email = 'member@pulsepoint.test')
    AND channel = 'telegram'
    AND event_type = 'invoice_sent'
    AND target = 'demo_chat_member_2'
);

-- 5) DEMO QR TOKEN + CHECK-IN

INSERT INTO member_qr_tokens (
  user_id,
  token_hash,
  expires_at,
  is_active
)
SELECT
  (SELECT id FROM users WHERE email = 'member@pulsepoint.test'),
  SHA2('demo-member-2-qr-token', 256),
  DATE_ADD(NOW(), INTERVAL 365 DAY),
  1
WHERE NOT EXISTS (
  SELECT 1 FROM member_qr_tokens WHERE user_id = (SELECT id FROM users WHERE email = 'member@pulsepoint.test')
);

INSERT INTO check_ins (
  user_id,
  location_id,
  checkin_method,
  scanned_by_admin_id,
  checked_in_at
)
SELECT
  (SELECT id FROM users WHERE email = 'member@pulsepoint.test'),
  (SELECT id FROM gym_locations WHERE name = 'PulsePoint Downtown'),
  'qr',
  (SELECT id FROM users WHERE email = 'admin@pulsepoint.test'),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM check_ins
  WHERE user_id = (SELECT id FROM users WHERE email = 'member@pulsepoint.test')
    AND location_id = (SELECT id FROM gym_locations WHERE name = 'PulsePoint Downtown')
    AND DATE(checked_in_at) = CURDATE()
);

COMMIT;
SET SQL_SAFE_UPDATES = @OLD_SQL_SAFE_UPDATES;
