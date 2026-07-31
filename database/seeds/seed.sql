-- ============================================================================
-- Seed data — demo content to explore the rebuilt system immediately.
-- Room categories mirror the original project's 9 room tables (bachelor,
-- classic, club, delux, family, luxury, presidential, superdelux, superior),
-- now as rows in `room_types` instead of separate tables, each seeded with
-- 6 physical rooms in `rooms` instead of 10 blank "Availability" slots.
-- ============================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------- Settings --
INSERT INTO settings (setting_key, setting_value) VALUES
    ('hotel_name', 'The Pacific Hotel'),
    ('currency_symbol', '$'),
    ('tax_rate_percent', '10'),
    ('contact_notify_email', 'info@thepacifichotel.example'),
    ('contact_phone', '+1 (555) 010-2200'),
    ('contact_address', '1 Harbor View Drive, Coastal City');

-- ------------------------------------------------------------------- Users --
-- Demo passwords (change immediately after install — see docs/INSTALL.md):
--   super_admin  | superadmin | Admin@12345
--   admin        | admin      | Admin@12345
--   receptionist | reception  | Reception@12345
--   customer     | customer   | Customer@12345
INSERT INTO users (uuid, full_name, username, email, phone, password_hash, role, status) VALUES
    ('1461939c-15e0-44d9-9e8a-d6b4c1983162', 'System Super Admin', 'superadmin', 'superadmin@thepacifichotel.example', '+15550000001', '$2y$12$XJuAKeUN6TXl8xElU685pe6FRMg8GP5Ol9Uzo8bKOQ2iFXl680kaa', 'super_admin', 'active'),
    ('db76abbf-45e2-4105-a86c-910272446a73', 'Front Desk Reception', 'reception', 'reception@thepacifichotel.example', '+15550000002', '$2y$12$/bnG8bApqXQNM38yX3YkC.efXZLoAmyqNsMGBvBOpxasnvJhRPMw2', 'receptionist', 'active'),
    ('e57420c7-c44c-4ec5-892c-f4df69428c27', 'Demo Customer', 'customer', 'customer@example.com', '+15550000003', '$2y$12$T5IF68cNWgVdRJroG76GJ.D4HYdr84dKpqybCCUj6e2dBkCxD88rC', 'customer', 'active');

-- Second admin account (same demo password as super admin for convenience)
INSERT INTO users (uuid, full_name, username, email, phone, password_hash, role, status) VALUES
    (UUID(), 'Hotel Admin', 'admin', 'admin@thepacifichotel.example', '+15550000004', '$2y$12$XJuAKeUN6TXl8xElU685pe6FRMg8GP5Ol9Uzo8bKOQ2iFXl680kaa', 'admin', 'active');

-- -------------------------------------------------------------- Room Types --
INSERT INTO room_types (slug, name, description, base_price, max_guests, bed_count, size_sqm, amenities_json, sort_order) VALUES
    ('classic', 'Classic Room', 'A comfortable, well-appointed room with everything you need for a relaxing stay.', 89.00, 2, 1, 24, '["Free Wi-Fi","Air Conditioning","Flat-screen TV","Work Desk"]', 1),
    ('superior', 'Superior Room', 'Extra space and a refined finish for guests who want a little more comfort.', 119.00, 2, 1, 28, '["Free Wi-Fi","Air Conditioning","Mini Fridge","City View"]', 2),
    ('club', 'Club Room', 'Access to the Club Lounge with complimentary breakfast and evening cocktails.', 159.00, 2, 1, 32, '["Free Wi-Fi","Club Lounge Access","Mini Bar","Bathrobe & Slippers"]', 3),
    ('delux', 'Deluxe Room', 'Generously sized room with premium furnishings and a soaking tub.', 149.00, 2, 1, 34, '["Free Wi-Fi","Soaking Tub","Premium Toiletries","Balcony"]', 4),
    ('superdelux', 'Super Deluxe Room', 'Our top single-key room, with panoramic views and a dedicated lounge chair.', 189.00, 3, 2, 40, '["Free Wi-Fi","Panoramic View","Nespresso Machine","Rain Shower"]', 5),
    ('family', 'Family Suite', 'Two connected sleeping areas, ideal for families traveling with children.', 219.00, 4, 2, 48, '["Free Wi-Fi","Connecting Rooms Available","Kids Amenities","Sofa Bed"]', 6),
    ('luxury', 'Luxury Suite', 'A sweeping suite with a separate living area and premium in-room dining.', 279.00, 3, 2, 55, '["Free Wi-Fi","Separate Living Area","Butler Service","Ocean View"]', 7),
    ('presidential', 'Presidential Suite', 'The pinnacle of the property — a private terrace, dining room and study.', 459.00, 4, 2, 90, '["Free Wi-Fi","Private Terrace","Dedicated Butler","Dining Room"]', 8),
    ('bachelor', 'Bachelor Studio', 'A smart, compact studio designed for solo travelers on business or leisure.', 69.00, 1, 1, 18, '["Free Wi-Fi","Air Conditioning","Work Desk","Compact Kitchenette"]', 9);

-- ------------------------------------------------------------------- Rooms --
-- 6 physical rooms per type (replaces the original's 10 blank Availability rows per table)
INSERT INTO rooms (room_type_id, room_number, floor)
SELECT rt.id, CONCAT(UPPER(rt.slug), '-', LPAD(n.n, 3, '0')), n.n
FROM room_types rt
CROSS JOIN (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) n;

-- --------------------------------------------------------------- Services --
INSERT INTO extra_services (name, description, price) VALUES
    ('Airport Pickup', 'One-way private transfer from the airport.', 35.00),
    ('Breakfast Package', 'Daily buffet breakfast for the length of stay (per booking).', 15.00),
    ('Early Check-in', 'Guaranteed check-in from 8:00 AM.', 20.00),
    ('Late Check-out', 'Check-out extended to 4:00 PM.', 20.00),
    ('Spa Access', 'Full-day access to the spa and wellness center.', 45.00);

-- ----------------------------------------------------------------- Coupon --
INSERT INTO coupons (code, discount_type, discount_value, max_uses, valid_from, valid_until, is_active) VALUES
    ('WELCOME10', 'percent', 10.00, 200, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1);

-- ------------------------------------------------------------- Event Types --
INSERT INTO event_types (name, description, base_price) VALUES
    ('Grand Ballroom (Wedding/Banquet)', 'Our largest venue, seats up to 300 guests, full AV and catering kitchen access.', 2500.00),
    ('Conference Hall', 'Boardroom-style conference space for up to 80 guests with projector and video conferencing.', 900.00),
    ('Garden Terrace', 'Open-air terrace overlooking the harbor, ideal for cocktail receptions.', 1200.00);
