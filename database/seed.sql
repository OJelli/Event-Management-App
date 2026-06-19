USE event_management;

-- Seed users
INSERT INTO `users` (username, email, password) VALUES
('jerry', 'jerry@example.com', 'test123'),
('alice', 'alice@example.com', 'password1'),
('bob', 'bob@example.com', 'password2');

-- Seed events
INSERT INTO `events` (title, description, event_date, location, created_by) VALUES
('Hackathon', 'Coding competition', '2026-07-01', 'Taylor University', 1),
('Workshop', 'Web development basics', '2026-07-15', 'Taylor University Lab', 2);

-- Seed registrations
INSERT INTO `registrations` (user_id, event_id) VALUES
(1, 1),
(2, 1),
(3, 2);
