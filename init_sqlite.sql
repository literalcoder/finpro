CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role TEXT CHECK (role IN ('president','treasurer','auditor','adviser','dean','admin')) DEFAULT 'president',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@example.com', '21232f297a57a5a743894a0e4a801fc3', 'admin');