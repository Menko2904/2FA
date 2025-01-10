CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    secret_key VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE Users ADD COLUMN twofa_method ENUM('app', 'email') NOT NULL DEFAULT 'email';

