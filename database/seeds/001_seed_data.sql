USE pulsepoint_fitness;

INSERT INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'member');

INSERT INTO users (role_id, full_name, email, password_hash, phone) VALUES
(1, 'System Admin', 'admin@pulsepoint.test', '$2b$12$.bExm4I/cenZ2K0JxL0nDuytbXdEu7xtWQcJcbbxPqSGF7EGeA74m', '+65 9000 1001'),
(2, 'Jamie Member', 'member@pulsepoint.test', '$2b$12$Z1MB2rPZ1A7QpEpcFCt.nOSnxnJ9FroNsnsqvBNSN72QhFoSBz5r.', '+65 9000 2002');

INSERT INTO membership_plans (name, price, duration_months, description, status) VALUES
('Starter Flex', 59.00, 1, 'Access to gym floor and basic classes.', 'active'),
('Performance Plus', 149.00, 3, 'Unlimited classes plus trainer consultations.', 'active'),
('Elite Annual', 499.00, 12, 'Best value with full access and priority booking.', 'active');

INSERT INTO memberships (user_id, plan_id, start_date, end_date, status) VALUES
(2, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'active');

INSERT INTO trainers (name, specialty, bio, image_path, status) VALUES
('Aiden Cruz', 'Strength & Conditioning', 'Focuses on progressive overload and athletic performance.', '', 'active'),
('Maya Tan', 'HIIT & Fat Loss', 'Designs high-intensity circuits for endurance and body composition.', '', 'active'),
('Noah Lim', 'Mobility & Recovery', 'Helps members improve movement quality and prevent injuries.', '', 'active');

INSERT INTO gym_locations (name, address, phone, opening_hours, status) VALUES
('PulsePoint Downtown', '101 Core Street, Central City', '+65 6123 1111', '6:00 AM - 11:00 PM', 'active'),
('PulsePoint Riverside', '88 River Lane, West District', '+65 6123 2222', '24 Hours', 'active');

INSERT INTO classes (trainer_id, location_id, title, description, class_date, start_time, end_time, capacity, status) VALUES
(1, 1, 'Power Lift Fundamentals', 'Technique-first barbell session.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', '19:00:00', 20, 'active'),
(2, 2, 'Pulse HIIT', 'Fast-paced conditioning workout.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '19:00:00', '20:00:00', 25, 'active'),
(3, 1, 'Mobility Reset', 'Guided mobility and recovery drills.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '17:00:00', '18:00:00', 18, 'active');

INSERT INTO bookings (user_id, class_id, booking_status) VALUES
(2, 1, 'booked'),
(2, 2, 'booked');

INSERT INTO contact_messages (name, email, subject, message) VALUES
('Taylor Lee', 'taylor@example.com', 'Corporate Membership', 'Can your team provide a company wellness package?');
