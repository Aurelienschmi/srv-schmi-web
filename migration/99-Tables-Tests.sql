-- Insertion de données de test dans la table 'liens'
INSERT INTO liens (url, label) VALUES
('https://fstream.top/', 'fstream'),
('https://dpstream.fyi/', 'dpstream'),
('https://rogzov.com/', 'rogzov'),
('https://yostav.com/', 'yostav');

-- Insertion de données de test dans la table 'palworld_sessions'
INSERT INTO palworld_sessions (player, join_time, leave_time, session_duration) VALUES
('Aurel',   '2025-05-19 19:22:08', '2025-05-19 19:22:32', 24),
('Aurel',   '2025-05-19 19:28:01', '2025-05-19 19:28:48', 47),
('Aurel',   '2025-05-19 19:34:36', '2025-05-19 19:34:46', 405),
('Mathoss', '2025-05-19 20:44:05', '2025-05-19 22:38:27', 6863),
('Mathoss', '2025-05-20 11:57:55', '2025-05-20 12:45:10', 2837);

-- Insertion de données de test dans la table 'pages'
INSERT INTO pages (name, path) VALUES
('CSGO', 'csgo.php'),
('Palworld', 'palworld.php'),
('Schmischmi', 'schmischmi.php');

-- Insertion d'un utilisateur admin par défaut (mdp : azerty, hashé en SHA2-256)
INSERT INTO users (username, password, role) VALUES
('admin', SHA2('azerty', 256), 'admin'),
('test1', SHA2('azer', 256), 'user'),
('test2', SHA2('azer', 256), 'user');
