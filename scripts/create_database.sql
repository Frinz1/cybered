
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS threat_scenarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('phishing', 'malware') NOT NULL,
    severity ENUM('low', 'medium', 'high') NOT NULL,
    keywords JSON NOT NULL,
    solution TEXT NOT NULL,
    mitigation_steps JSON NOT NULL,
    explanation TEXT NOT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS chat_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    status ENUM('active', 'completed', 'abandoned') DEFAULT 'active',
    current_step VARCHAR(255) DEFAULT 'greeting',
    context_data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_session_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_bot BOOLEAN DEFAULT FALSE,
    message_type VARCHAR(255) DEFAULT 'text',
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (chat_session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
);


INSERT INTO users (name, email, password, is_admin, created_at, updated_at) 
VALUES ('Admin', 'admin@municipality.gov', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, NOW(), NOW())
ON DUPLICATE KEY UPDATE name = name;


INSERT INTO users (name, email, password, is_admin, created_at, updated_at) 
VALUES 
    ('Charles Biasora', 'charles@municipality.gov', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, NOW(), NOW()),

ON DUPLICATE KEY UPDATE name = name;
