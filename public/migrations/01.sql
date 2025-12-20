CREATE TABLE login_attempts (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                email VARCHAR(255) NOT NULL,
                                ip_address VARCHAR(45) NOT NULL,
                                attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE register_attempts (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                email VARCHAR(255) NOT NULL,
                                ip_address VARCHAR(45) NOT NULL,
                                attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE `Utilizator` CHANGE `rol` `rol` ENUM('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'user';