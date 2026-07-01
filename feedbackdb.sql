-- ADDED: New table for patient feedback/ratings (real testimonials system).
-- Submitters are verified against an existing confirmed booking by email
-- (see api/feedback-create.php) rather than requiring full account
-- registration. All entries start 'pending' and require moderator
-- approval (see api/feedback-moderate.php) before being publicly visible
-- via api/feedback-list.php, reviews.php, and the index.php testimonials section.
CREATE TABLE IF NOT EXISTS feedback (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  booking_id    INT NOT NULL,                          -- the confirmed booking used to verify the submitter
  display_name  VARCHAR(150) NOT NULL,                 -- name shown publicly (not necessarily the booking's legal name)
  email         VARCHAR(255) NOT NULL,                 -- used for verification + rate limiting, never displayed publicly
  service_key   VARCHAR(100) NULL,                      -- optional; matches DENTAL_SERVICES keys, NULL = general feedback
  rating        TINYINT UNSIGNED NOT NULL,              -- 1–5
  comment       TEXT NOT NULL,
  status        ENUM('pending','approved','rejected') DEFAULT 'pending',
  moderated_by  INT NULL,                                -- references users.id of the admin/staff who approved/rejected, if applicable
  approved_at   TIMESTAMP NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT chk_feedback_rating CHECK (rating BETWEEN 1 AND 5)
);

-- ADDED: Indexes matching the real query patterns in feedback-list.php
-- and feedback-create.php (filtering by status, sorting by rating/date,
-- filtering by service, and rate-limiting lookups by email).
ALTER TABLE feedback
ADD INDEX idx_status_created (status, created_at);

ALTER TABLE feedback
ADD INDEX idx_status_rating (status, rating);

ALTER TABLE feedback
ADD INDEX idx_service_key (service_key);

ALTER TABLE feedback
ADD INDEX idx_email (email);

-- NOTE: Rate-limiting for feedback submissions reuses the existing
-- `rate_limits` table (added in a prior migration) via checkRateLimit()
-- in api/_helpers.php, keyed by email and the 'feedback-create' endpoint
-- name — no additional rate-limit table is needed for this feature.