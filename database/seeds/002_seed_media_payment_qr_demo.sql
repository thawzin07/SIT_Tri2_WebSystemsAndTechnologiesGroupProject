USE pulsepoint_fitness;

START TRANSACTION;

-- 1) DEMO IMAGE PATHS

UPDATE users
SET profile_image_path = 'profiles/member-2.jpg'
WHERE id = 2;

UPDATE trainers
SET image_path = CASE id
  WHEN 1 THEN 'trainers/aiden-cruz.jpg'
  WHEN 2 THEN 'trainers/maya-tan.jpg'
  WHEN 3 THEN 'trainers/noah-lim.jpg'
  ELSE image_path
END,
image_alt = CASE id
  WHEN 1 THEN 'Trainer Aiden Cruz portrait'
  WHEN 2 THEN 'Trainer Maya Tan portrait'
  WHEN 3 THEN 'Trainer Noah Lim portrait'
  ELSE image_alt
END
WHERE id IN (1, 2, 3);

UPDATE gym_locations
SET image_path = CASE id
  WHEN 1 THEN 'locations/pulsepoint-downtown.jpg'
  WHEN 2 THEN 'locations/pulsepoint-riverside.jpg'
  ELSE image_path
END,
latitude = CASE id
  WHEN 1 THEN 1.2902700
  WHEN 2 THEN 1.3001000
  ELSE latitude
END,
longitude = CASE id
  WHEN 1 THEN 103.8519590
  WHEN 2 THEN 103.8455000
  ELSE longitude
END,
map_place_id = CASE id
  WHEN 1 THEN 'demo_place_downtown'
  WHEN 2 THEN 'demo_place_riverside'
  ELSE map_place_id
END
WHERE id IN (1, 2);

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
  2,
  (SELECT id FROM memberships WHERE user_id = 2 ORDER BY id DESC LIMIT 1),
  2,
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
  2,
  'email',
  'payment_success',
  'member@pulsepoint.test',
  'sent',
  JSON_OBJECT('invoice_no', 'INV-20260315-0001', 'amount', 149.00, 'currency', 'USD'),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM notification_logs
  WHERE user_id = 2
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
  2,
  'telegram',
  'invoice_sent',
  'demo_chat_member_2',
  'sent',
  JSON_OBJECT('invoice_no', 'INV-20260315-0001', 'pdf_path', 'invoices/INV-20260315-0001.pdf'),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM notification_logs
  WHERE user_id = 2
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
  2,
  SHA2('demo-member-2-qr-token', 256),
  DATE_ADD(NOW(), INTERVAL 365 DAY),
  1
WHERE NOT EXISTS (
  SELECT 1 FROM member_qr_tokens WHERE user_id = 2
);

INSERT INTO check_ins (
  user_id,
  location_id,
  checkin_method,
  scanned_by_admin_id,
  checked_in_at
)
SELECT
  2,
  1,
  'qr',
  1,
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM check_ins
  WHERE user_id = 2
    AND location_id = 1
    AND DATE(checked_in_at) = CURDATE()
);

COMMIT;
