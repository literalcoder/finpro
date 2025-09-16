-- Drop existing tables if they exist to avoid conflicts
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS financial_transactions;
DROP TABLE IF EXISTS proposals;
DROP TABLE IF EXISTS organizations;
DROP TABLE IF EXISTS users;

-- Create organizations table
CREATE TABLE organizations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    status TEXT CHECK (status IN ('active', 'inactive')) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create users table with org_id foreign key
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role TEXT CHECK (role IN ('admin','president','treasurer','auditor','adviser','dean','ssc')) DEFAULT 'president',
    org_id INTEGER,
    status TEXT CHECK (status IN ('active', 'inactive')) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(id)
);

-- Create proposals table
CREATE TABLE proposals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type TEXT CHECK (type IN ('event','project','resolution','membership_fee','uniform','other')) NOT NULL,
    org_id INTEGER NOT NULL,
    created_by INTEGER NOT NULL,
    status TEXT CHECK (status IN ('draft','pending','approved','rejected','needs_revision')) DEFAULT 'draft',
    submitted_at TIMESTAMP,
    approved_at TIMESTAMP,
    rejected_at TIMESTAMP,
    rejection_reason TEXT,
    budget_amount DECIMAL(10,2) DEFAULT 0.00,
    attachments TEXT, -- JSON field for file paths
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create financial transactions table
CREATE TABLE financial_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    org_id INTEGER NOT NULL,
    proposal_id INTEGER,
    type TEXT CHECK (type IN ('income','expense','collection','liquidation')) NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    receipt_file VARCHAR(255),
    created_by INTEGER NOT NULL,
    transaction_date DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(id),
    FOREIGN KEY (proposal_id) REFERENCES proposals(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create audit logs table
CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    org_id INTEGER,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INTEGER,
    metadata TEXT, -- JSON field for additional data
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (org_id) REFERENCES organizations(id)
);

-- Insert initial data
INSERT INTO organizations (name, code, description) VALUES 
('Computer Science Society', 'CSS', 'Student organization for Computer Science students'),
('Business Administration Club', 'BAC', 'Student organization for Business Administration students');

-- Create system admin user (password: adminpass)
INSERT INTO users (name, email, password, role, org_id) VALUES 
('System Administrator', 'admin@orgfinpro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL);

-- Create sample organization users (password: password123)
INSERT INTO users (name, email, password, role, org_id) VALUES 
('John Doe', 'president@css.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'president', 1),
('Jane Smith', 'treasurer@css.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'treasurer', 1),
('Bob Johnson', 'president@bac.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'president', 2);