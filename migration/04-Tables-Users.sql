-- Create the users table in the srv database
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` CHAR(64) NOT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user'
);