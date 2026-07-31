-- ============================================================================
-- Hotel Management System — Core Schema (v2)
-- Engine: InnoDB, Charset: utf8mb4 (full Unicode + emoji support)
--
-- DESIGN NOTES (why this replaces the original schema):
--   - The original DB had a separate table per room category (club, classic,
--     superior, family, presidential, bachelor, luxury, delux, superdelux),
--     each with an `Availability` flag and no foreign keys. This made adding
--     a room type require a new TABLE, and booking logic had to build SQL
--     with string-interpolated table names (a SQL injection vector).
--     -> Replaced with `room_types` + `rooms`, a standard 1:N relation.
--   - Passwords were stored in plaintext in `signup`. -> `users.password_hash`
--     stores bcrypt hashes only (see App\Core\Auth).
--   - There was no concept of roles; the admin login was a hardcoded
--     username/password in success.php. -> `users.role` with an ENUM plus
--     proper authentication for every role (super_admin/admin/receptionist).
--   - Booking availability was checked with a race-prone "loop until a free
--     slot is found" pattern with no transaction or locking. -> availability
--     is now derived from date-range overlap queries executed inside a
--     transaction with row locking (see BookingService).
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- users — replaces `signup`, adds roles, hashed passwords, account status
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)        NOT NULL UNIQUE,
    full_name       VARCHAR(150)    NOT NULL,
    username        VARCHAR(50)     NOT NULL UNIQUE,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    phone           VARCHAR(30)     NULL,
    password_hash   VARCHAR(255)    NOT NULL,
    role            ENUM('super_admin','admin','receptionist','customer') NOT NULL DEFAULT 'customer',
    status          ENUM('active','suspended') NOT NULL DEFAULT 'active',
    avatar_path     VARCHAR(255)    NULL,
    failed_logins   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until    DATETIME        NULL,
    remember_token  VARCHAR(100)    NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- password_resets — token-based forgot-password flow (original had none)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    token_hash  VARCHAR(255) NOT NULL,
    expires_at  DATETIME NOT NULL,
    used_at     DATETIME NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pwreset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pwreset_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- room_types — replaces the 9 duplicated per-category tables
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS room_types (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(60)     NOT NULL UNIQUE,
    name            VARCHAR(100)    NOT NULL,
    description     TEXT            NULL,
    base_price      DECIMAL(10,2)   NOT NULL,
    max_guests      TINYINT UNSIGNED NOT NULL DEFAULT 2,
    bed_count       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    size_sqm        SMALLINT UNSIGNED NULL,
    amenities_json  TEXT            NULL COMMENT 'JSON array of amenity strings',
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- room_type_images — multiple photos per room type (gallery)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS room_type_images (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_type_id    INT UNSIGNED NOT NULL,
    image_path      VARCHAR(255) NOT NULL,
    is_primary      TINYINT(1) NOT NULL DEFAULT 0,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rtimg_type FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE,
    INDEX idx_rtimg_type (room_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- rooms — individual physical rooms/units belonging to a room_type
-- (replaces the "Availability flag on 10 blank pre-seeded rows" design)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rooms (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_type_id    INT UNSIGNED NOT NULL,
    room_number     VARCHAR(20)  NOT NULL UNIQUE,
    floor           TINYINT UNSIGNED NULL,
    status          ENUM('available','maintenance','out_of_service') NOT NULL DEFAULT 'available',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_rooms_type FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE,
    INDEX idx_rooms_type (room_type_id),
    INDEX idx_rooms_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- extra_services — add-on services selectable during booking
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS extra_services (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- coupons — discount codes applied at booking time
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(40) NOT NULL UNIQUE,
    discount_type   ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_value  DECIMAL(10,2) NOT NULL,
    max_uses        INT UNSIGNED NULL,
    used_count      INT UNSIGNED NOT NULL DEFAULT 0,
    valid_from      DATE NULL,
    valid_until     DATE NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- bookings — replaces the `room` table; one row per room reservation
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_ref         CHAR(12)        NOT NULL UNIQUE COMMENT 'Public-facing reference, e.g. BK-8F3A2C1D',
    user_id             INT UNSIGNED    NULL COMMENT 'NULL allowed for guest bookings',
    room_id             INT UNSIGNED    NOT NULL,
    guest_name          VARCHAR(150)    NOT NULL,
    guest_phone         VARCHAR(30)     NOT NULL,
    guest_city          VARCHAR(100)    NULL,
    guests_count        TINYINT UNSIGNED NOT NULL DEFAULT 1,
    check_in            DATE            NOT NULL,
    check_out           DATE            NOT NULL,
    nights              SMALLINT UNSIGNED NOT NULL,
    room_rate_snapshot  DECIMAL(10,2)   NOT NULL COMMENT 'Price/night at time of booking',
    services_total      DECIMAL(10,2)   NOT NULL DEFAULT 0,
    tax_amount          DECIMAL(10,2)   NOT NULL DEFAULT 0,
    discount_amount     DECIMAL(10,2)   NOT NULL DEFAULT 0,
    coupon_id           INT UNSIGNED    NULL,
    total_amount        DECIMAL(10,2)   NOT NULL,
    status              ENUM('pending','confirmed','paid','checked_in','checked_out','cancelled','rejected') NOT NULL DEFAULT 'pending',
    payment_method      ENUM('pay_at_hotel','stripe','paypal','paymob','fawry') NOT NULL DEFAULT 'pay_at_hotel',
    notes               TEXT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_room (room_id),
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_dates (check_in, check_out)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- booking_extra_services — pivot table for add-ons chosen per booking
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS booking_extra_services (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id          INT UNSIGNED NOT NULL,
    extra_service_id    INT UNSIGNED NOT NULL,
    quantity            SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    price_snapshot      DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_bes_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_bes_service FOREIGN KEY (extra_service_id) REFERENCES extra_services(id) ON DELETE RESTRICT,
    INDEX idx_bes_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- event_types — conference hall / banquet / wedding etc.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_types (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    base_price  DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- event_bookings — replaces `events` table
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_bookings (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_ref     CHAR(12)        NOT NULL UNIQUE,
    user_id         INT UNSIGNED    NULL,
    event_type_id   INT UNSIGNED    NOT NULL,
    guest_name      VARCHAR(150)    NOT NULL,
    guest_phone     VARCHAR(30)     NOT NULL,
    guest_city      VARCHAR(100)    NULL,
    guests_count    INT UNSIGNED    NOT NULL DEFAULT 1,
    event_date      DATE            NOT NULL,
    start_time      TIME            NOT NULL,
    end_time        TIME            NOT NULL,
    total_amount    DECIMAL(10,2)   NOT NULL DEFAULT 0,
    status          ENUM('pending','confirmed','paid','cancelled','rejected') NOT NULL DEFAULT 'pending',
    notes           TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_evb_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_evb_type FOREIGN KEY (event_type_id) REFERENCES event_types(id) ON DELETE RESTRICT,
    INDEX idx_evb_user (user_id),
    INDEX idx_evb_status (status),
    INDEX idx_evb_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- invoices — one per paid booking (room or event), used for PDF generation
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoices (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number  VARCHAR(30) NOT NULL UNIQUE,
    booking_type    ENUM('room','event') NOT NULL,
    booking_id      INT UNSIGNED NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    issued_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- payment_transactions — gateway-agnostic ledger (Stripe/PayPal/Paymob/Fawry)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_transactions (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_type        ENUM('room','event') NOT NULL,
    booking_id          INT UNSIGNED NOT NULL,
    gateway             ENUM('pay_at_hotel','stripe','paypal','paymob','fawry') NOT NULL,
    gateway_reference    VARCHAR(150) NULL,
    amount              DECIMAL(10,2) NOT NULL,
    currency             CHAR(3) NOT NULL DEFAULT 'USD',
    status              ENUM('pending','succeeded','failed','refunded') NOT NULL DEFAULT 'pending',
    raw_response        TEXT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pay_booking (booking_type, booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- notifications — in-app notification bell
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    title       VARCHAR(150) NOT NULL,
    body        VARCHAR(255) NOT NULL,
    url         VARCHAR(255) NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- activity_logs — audit trail for admin/staff actions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,
    action      VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    ip_address  VARCHAR(45) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_log_user (user_id),
    INDEX idx_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- contact_messages — replaces mail.php's fire-and-forget mail() call
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    subject     VARCHAR(200) NOT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- settings — key/value site configuration (hotel name, tax rate, currency…)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key     VARCHAR(60) PRIMARY KEY,
    setting_value   TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- login_attempts — basic rate limiting for the login endpoint
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier  VARCHAR(150) NOT NULL COMMENT 'username or IP',
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempts_identifier (identifier, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
