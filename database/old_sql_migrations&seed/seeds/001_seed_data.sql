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
