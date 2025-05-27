CREATE TABLE matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player1_id INT NOT NULL,
  player2_id INT NOT NULL,
  player1_connected TINYINT(1) DEFAULT 0,
  player2_connected TINYINT(1) DEFAULT 0,
  status ENUM('waiting','banning','ready','finished') DEFAULT 'waiting',
  current_turn INT, -- user_id du joueur qui doit bannir
  maps_left TEXT,   -- JSON array des maps restantes
  selected_map VARCHAR(64) DEFAULT NULL,
  winner_id INT DEFAULT NULL,
  side_picker INT DEFAULT NULL, -- user_id du joueur qui choisira le côté
  selected_side VARCHAR(8) DEFAULT NULL, -- <--- AJOUT ICI
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  finished_at DATETIME DEFAULT NULL,
  FOREIGN KEY (player1_id) REFERENCES users(id),
  FOREIGN KEY (player2_id) REFERENCES users(id),
  FOREIGN KEY (side_picker) REFERENCES users(id)
);