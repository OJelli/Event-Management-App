USE event_management;

-- Reset tables before inserting
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE registrations;
TRUNCATE TABLE events;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Seed users
INSERT INTO users (username, email, password) VALUES
('jerry', 'jerry@example.com', 'test123'),
('alice', 'alice@example.com', 'password1'),
('bob', 'bob@example.com', 'password2');

-- Seed events (link to correct user IDs)
INSERT INTO events (title, description, event_date, location, created_by) VALUES
('Hackathon', 'Coding competition', '2026-07-01', 'Taylor University', 
 (SELECT id FROM users WHERE username='jerry')),
('Workshop', 'Web development basics', '2026-07-15', 'Taylor University Lab', 
 (SELECT id FROM users WHERE username='alice'));

-- Seed registrations (link to correct IDs)
INSERT INTO registrations (user_id, event_id) VALUES
((SELECT id FROM users WHERE username='jerry'), (SELECT id FROM events WHERE title='Hackathon')),
((SELECT id FROM users WHERE username='alice'), (SELECT id FROM events WHERE title='Hackathon')),
((SELECT id FROM users WHERE username='bob'), (SELECT id FROM events WHERE title='Workshop'));
