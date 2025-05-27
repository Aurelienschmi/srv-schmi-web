CREATE TABLE `game_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `game_name` VARCHAR(50) NOT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);