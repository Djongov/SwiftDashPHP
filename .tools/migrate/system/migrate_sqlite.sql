-- App Settings TABLE 
CREATE TABLE IF NOT EXISTS app_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    value TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'string',
    admin_setting INTEGER NOT NULL DEFAULT 0,
    owner TEXT NOT NULL DEFAULT 'system',
    description TEXT,
    updated_at TEXT DEFAULT (datetime('now'))
);

INSERT OR IGNORE INTO app_settings (name, value, type, owner, admin_setting, description)
VALUES 
    ('default_data_grid_engine', 'DataGrid', 'string', 'system', TRUE, 'AGGrid or DataGrid for values.'),
    ('auth_expiry', '3600', 'int', 'system', TRUE, 'Number in seconds for the JWT Token''s lifetime'),
    ('use_tailwind_cdn', '1', 'bool', 'system', TRUE, 'Whether to use Tailwind CDN or local. Local is huge because of themes'),
    ('color_scheme', 'amber', 'string', 'system', TRUE, 'The default tailwind color for theming');

-- Email App Settings
INSERT OR IGNORE INTO app_settings (name, value, type, owner, admin_setting, description)
VALUES
    ('email_smtp_host', '', 'string', 'smtp', TRUE, 'SMTP server host'),
    ('email_smtp_port', '587', 'int', 'smtp', TRUE, 'SMTP server port'),
    ('email_smtp_ssl', '1', 'bool', 'smtp', TRUE, 'SMTP server encryption (tls/ssl)'),
    ('email_smtp_verify_peer', '1', 'bool', 'smtp', TRUE, 'Verify SSL peer certificate'),
    ('email_smtp_verify_peer_name', '1', 'bool', 'smtp', TRUE, 'Verify SSL peer certificate name'),
    ('email_smtp_allow_self_signed', '0', 'bool', 'smtp', TRUE, 'Allow self-signed SSL certificates'),
    ('email_smtp_username', '', 'string', 'smtp', TRUE, 'SMTP server username'),
    ('email_smtp_password', '', 'string', 'smtp', TRUE, 'SMTP server password'),
    ('email_smtp_from_address', '', 'string', 'smtp', TRUE, 'Default from email address'),
    ('email_smtp_from_name', 'No Reply', 'string', 'smtp', TRUE, 'Default from name'),
    ('email_smtp_administrator_email', '', 'string', 'smtp', TRUE, 'Administrator email for system notifications');

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
    ip_cidr VARCHAR(256) NOT NULL UNIQUE,
    created_by VARCHAR(1000),
    comment VARCHAR(1000),
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default firewall rules
INSERT OR IGNORE INTO firewall (ip_cidr, created_by, comment)
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

-- UTM CAPTURES TABLE
CREATE TABLE IF NOT EXISTS utm_captures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip_address TEXT NULL, 
    utm_source TEXT NULL,
    utm_medium TEXT NULL,
    utm_campaign TEXT NULL,
    utm_term TEXT NULL,
    utm_content TEXT NULL,
    referrer_url TEXT NULL,
    landing_page TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- SESSIONS TABLE (for distributed session management)
CREATE TABLE IF NOT EXISTS sessions (
    id TEXT NOT NULL PRIMARY KEY,
    data TEXT NOT NULL,
    expires_at TEXT NULL,
    last_activity TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_expires ON sessions (expires_at);
CREATE INDEX IF NOT EXISTS idx_last_activity ON sessions (last_activity);
