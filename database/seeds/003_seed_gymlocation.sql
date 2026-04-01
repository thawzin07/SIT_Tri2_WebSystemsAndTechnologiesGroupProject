USE pulsepoint_fitness;

START TRANSACTION;

REPLACE INTO `gym_locations` (`id`, `name`, `address`, `phone`, `opening_hours`, `image_path`, `latitude`, `longitude`, `map_place_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PulsePoint Downtown', '101 Core Street, Central City', '+65 6123 1111', '6:00 AM - 11:00 PM', '/assets/images/locations/pulsepoint-downtown.jpg', 1.2902700, 103.8519590, 'demo_place_downtown', 'active', '2026-03-27 15:49:44', '2026-04-01 14:40:34'),
(2, 'PulsePoint Riverside', '88 River Lane, West District', '+65 6123 2222', '24 Hours', '/assets/images/locations/pulsepoint-riverside.jpg', 1.3001000, 103.8455000, 'demo_place_riverside', 'active', '2026-03-27 15:49:44', '2026-04-01 14:40:34'),
(3, 'PulsePoint East Hub', '12 Harbour View, East District', '+65 6123 3333', '6:00 AM - 10:00 PM', '/assets/images/locations/pulsepoint-east-hub.jpg', 1.3181000, 103.9138000, 'demo_place_east_hub', 'active', '2026-03-27 15:49:44', '2026-04-01 15:43:41');

COMMIT;
