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
