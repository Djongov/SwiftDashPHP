-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(255),
    password VARCHAR(255),
    email VARCHAR(512),
    name VARCHAR(255),
    last_ips TEXT,
    origin_country VARCHAR(25),
    role VARCHAR(255),
    theme VARCHAR(20),
    picture VARCHAR(255),
    provider VARCHAR(255),
    enabled BOOLEAN,
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- CACHE TABLE
CREATE TABLE IF NOT EXISTS cache (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    value TEXT NOT NULL,
    type VARCHAR(255) NOT NULL,
    unique_property VARCHAR(255) NOT NULL,
    expiration TIMESTAMP NOT NULL,
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FIREWALL TABLE
CREATE TABLE IF NOT EXISTS firewall (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip_cidr VARCHAR(256) NOT NULL,
    created_by VARCHAR(1000),
    comment VARCHAR(1000),
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default firewall rules
INSERT INTO firewall (ip_cidr, created_by, comment)
VALUES 
    ('127.0.0.1/32', 'System', 'private range'),
    ('10.0.0.0/8', 'System', 'private range'),
    ('172.16.0.0/12', 'System', 'private range'),
    ('192.168.0.0/16', 'System', 'private range');

-- CSP REPORTS TABLE
CREATE TABLE IF NOT EXISTS csp_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    data JSON NOT NULL,
    domain VARCHAR(60),
    url VARCHAR(2500),
    referrer VARCHAR(2500),
    violated_directive TEXT,
    effective_directive VARCHAR(2500),
    original_policy VARCHAR(5000),
    disposition VARCHAR(60),
    blocked_uri TEXT,
    line_number INT,
    column_number INT,
    source_file VARCHAR(1500),
    status_code INT,
    script_sample VARCHAR(1500),
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CSP APPROVED DOMAINS TABLE
CREATE TABLE IF NOT EXISTS csp_approved_domains (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    domain VARCHAR(255) NOT NULL,
    created_by VARCHAR(60),
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SYSTEM LOG TABLE
CREATE TABLE IF NOT EXISTS system_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    text TEXT NOT NULL,
    client_ip VARCHAR(256) NOT NULL,
    user_agent TEXT NOT NULL,
    uri TEXT NOT NULL,
    method VARCHAR(20) NOT NULL,
    category VARCHAR(255) NOT NULL,
    created_by VARCHAR(255) NOT NULL,
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API KEYS TABLE (updated schema)
CREATE TABLE IF NOT EXISTS api_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    api_key TEXT NOT NULL,
    access VARCHAR(256) NOT NULL,
    note TEXT NOT NULL,
    created_by VARCHAR(255) NOT NULL,
    last_updated_by VARCHAR(255),
    enabled BOOLEAN NOT NULL DEFAULT 1,
    executions INTEGER NOT NULL DEFAULT 0,
    executions_limit INTEGER,
    executions_total INTEGER DEFAULT 0,
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (executions >= 0 AND (executions_limit IS NULL OR executions_limit >= 0))
);

-- API ACCESS LOG TABLE
CREATE TABLE IF NOT EXISTS api_access_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    request_id VARCHAR(255) NOT NULL,
    api_key TEXT NOT NULL,
    client_ip VARCHAR(256) NOT NULL,
    uri TEXT NOT NULL,
    method VARCHAR(20) NOT NULL,
    status_code INT DEFAULT 0,
    user_agent TEXT NOT NULL,
    last_updated_by VARCHAR(255),
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
