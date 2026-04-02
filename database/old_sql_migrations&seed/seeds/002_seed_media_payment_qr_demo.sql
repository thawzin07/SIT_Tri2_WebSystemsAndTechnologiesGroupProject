USE pulsepoint_fitness;

START TRANSACTION;
SET @OLD_SQL_SAFE_UPDATES := @@SQL_SAFE_UPDATES;
SET SQL_SAFE_UPDATES = 0;

-- 1) DEMO IMAGE PATHS

UPDATE users
SET profile_image_path = '/assets/images/profiles/member-2.jpg'
WHERE email = 'member@pulsepoint.test';

UPDATE trainers
SET image_path = CASE name
  WHEN 'Aiden Cruz' THEN '/assets/images/trainers/aiden-cruz.png'
  WHEN 'Maya Tan' THEN '/assets/images/trainers/maya-tan.png'
  WHEN 'Noah Lim' THEN '/assets/images/trainers/noah-lim.png'
  WHEN 'Hannah Teo' THEN '/assets/images/trainers/hannah-teo.png'
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
  WHEN 'PulsePoint Downtown' THEN '/assets/images/locations/pulsepoint-downtown.jpg'
  WHEN 'PulsePoint Riverside' THEN '/assets/images/locations/pulsepoint-riverside.jpg'
  WHEN 'PulsePoint East Hub' THEN '/assets/images/locations/pulsepoint-east-hub.jpg'
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
