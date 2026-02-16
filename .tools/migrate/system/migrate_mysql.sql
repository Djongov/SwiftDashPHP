-- App Settings TABLE
CREATE TABLE IF NOT EXISTS app_settings (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    type ENUM('string','int','float','bool','date','json') NOT NULL DEFAULT 'string',
    admin_setting TINYINT(1) NOT NULL DEFAULT 0,
    owner VARCHAR(50) NOT NULL DEFAULT 'system',
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clean up any existing duplicates (keep the first occurrence of each name)
DELETE t1 FROM app_settings t1
INNER JOIN app_settings t2 
WHERE t1.id > t2.id AND t1.name = t2.name;

INSERT INTO app_settings (id, name, value, type, owner, admin_setting, description)
VALUES 
    (4, 'default_data_grid_engine', 'DataGrid', 'string', 'system', 1, 'AGGrid or DataGrid for values.'),
    (3, 'auth_expiry', '3600', 'int', 'system', 1, 'Number in seconds for the JWT Token''s lifetime'),
    (2, 'use_tailwind_cdn', '1', 'bool', 'system', 1, 'Whether to use Tailwind CDN or local. Local is huge because of themes'),
    (1, 'color_scheme', 'amber', 'string', 'system', 1, 'The default tailwind color for theming')
ON DUPLICATE KEY UPDATE name = name; -- does nothing if id already exists

-- Email App Settings
-- Only insert if the name doesn't already exist
INSERT INTO app_settings (name, value, type, owner, admin_setting, description)
SELECT * FROM (
    SELECT 'email_smtp_host' as name, '' as value, 'string' as type, 'smtp' as owner, 1 as admin_setting, 'SMTP server host' as description
    UNION ALL SELECT 'email_smtp_port', '587', 'int', 'smtp', 1, 'SMTP server port'
    UNION ALL SELECT 'email_smtp_ssl', '1', 'bool', 'smtp', 1, 'SMTP server encryption (tls/ssl)'
    UNION ALL SELECT 'email_smtp_verify_peer', '1', 'bool', 'smtp', 1, 'Verify SSL peer certificate'
    UNION ALL SELECT 'email_smtp_verify_peer_name', '1', 'bool', 'smtp', 1, 'Verify SSL peer certificate name'
    UNION ALL SELECT 'email_smtp_allow_self_signed', '0', 'bool', 'smtp', 1, 'Allow self-signed SSL certificates'
    UNION ALL SELECT 'email_smtp_username', '', 'string', 'smtp', 1, 'SMTP server username'
    UNION ALL SELECT 'email_smtp_password', '', 'string', 'smtp', 1, 'SMTP server password'
    UNION ALL SELECT 'email_smtp_from_address', '', 'string', 'smtp', 1, 'Default from email address'
    UNION ALL SELECT 'email_smtp_from_name', 'No Reply', 'string', 'smtp', 1, 'Default from name'
    UNION ALL SELECT 'email_smtp_administrator_email', '', 'string', 'smtp', 1, 'Administrator email for system notifications'
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM app_settings WHERE app_settings.name = tmp.name
);

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    email VARCHAR(512) DEFAULT NULL,
    name VARCHAR(255) DEFAULT NULL,
    last_ips TEXT,
    origin_country VARCHAR(25) DEFAULT NULL,
    role VARCHAR(255) DEFAULT NULL,
    theme VARCHAR(20) DEFAULT NULL,
    picture VARCHAR(255) DEFAULT NULL,
    provider VARCHAR(255) DEFAULT NULL,
    enabled BOOLEAN DEFAULT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
);

-- CACHE TABLE
CREATE TABLE IF NOT EXISTS cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    value TEXT NOT NULL,
    type VARCHAR(255) NOT NULL,
    unique_property VARCHAR(255) NOT NULL,
    expiration TIMESTAMP NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- FIREWALL TABLE
CREATE TABLE IF NOT EXISTS firewall (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_cidr VARCHAR(256) NOT NULL,
    created_by VARCHAR(1000) DEFAULT NULL,
    comment VARCHAR(1000) DEFAULT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default firewall rules
INSERT IGNORE INTO firewall (ip_cidr, created_by, comment)
VALUES
    ('127.0.0.1/32', 'System', 'private range'),
    ('10.0.0.0/8', 'System', 'private range'),
    ('172.16.0.0/12', 'System', 'private range'),
    ('192.168.0.0/16', 'System', 'private range');

-- CSP REPORTS TABLE
CREATE TABLE IF NOT EXISTS csp_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data JSON NOT NULL,
    domain VARCHAR(60) DEFAULT NULL,
    url VARCHAR(2500) DEFAULT NULL,
    referrer VARCHAR(2500) DEFAULT NULL,
    violated_directive TEXT,
    effective_directive VARCHAR(2500) DEFAULT NULL,
    original_policy VARCHAR(5000) DEFAULT NULL,
    disposition VARCHAR(60) DEFAULT NULL,
    blocked_uri TEXT,
    line_number INT DEFAULT NULL,
    column_number INT DEFAULT NULL,
    source_file VARCHAR(1500) DEFAULT NULL,
    status_code INT DEFAULT NULL,
    script_sample VARCHAR(1500) DEFAULT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- CSP APPROVED DOMAINS TABLE
CREATE TABLE IF NOT EXISTS csp_approved_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) NOT NULL,
    created_by VARCHAR(60) DEFAULT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- SYSTEM LOG TABLE
CREATE TABLE IF NOT EXISTS system_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text TEXT NOT NULL,
    client_ip VARCHAR(256) NOT NULL,
    user_agent TEXT NOT NULL,
    uri TEXT NOT NULL,
    method VARCHAR(20) NOT NULL,
    category VARCHAR(255) NOT NULL,
    created_by VARCHAR(255) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- API KEYS TABLE (UPDATED)
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key TEXT NOT NULL,
    access VARCHAR(256) NOT NULL,
    note TEXT NOT NULL,
    created_by VARCHAR(255) NOT NULL,
    last_updated_by VARCHAR(255) DEFAULT NULL,
    enabled BOOLEAN NOT NULL DEFAULT TRUE,
    executions INT NOT NULL DEFAULT 0,
    executions_limit INT DEFAULT NULL,
    executions_total INT DEFAULT 0,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (executions >= 0 AND (executions_limit IS NULL OR executions_limit >= 0))
);

-- API ACCESS LOG TABLE
CREATE TABLE IF NOT EXISTS api_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id VARCHAR(255) NOT NULL,
    api_key TEXT NOT NULL,
    client_ip VARCHAR(256) NOT NULL,
    uri TEXT NOT NULL,
    method VARCHAR(20) NOT NULL,
    status_code INT DEFAULT 0,
    user_agent TEXT NOT NULL,
    last_updated_by VARCHAR(255) DEFAULT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS utm_captures (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NULL,

    utm_source VARCHAR(255) NULL,
    utm_medium VARCHAR(255) NULL,
    utm_campaign VARCHAR(255) NULL,
    utm_term VARCHAR(255) NULL,
    utm_content VARCHAR(255) NULL,

    referrer_url TEXT NULL,
    landing_page TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- BACKGROUND CLEANUP EVENT FOR CACHE
-- SET GLOBAL event_scheduler = ON;

-- CREATE EVENT IF NOT EXISTS cleanup_cache_event
-- ON SCHEDULE EVERY 10 MINUTE
-- DO
--   DELETE FROM cache WHERE expiration < NOW();

-- -- RESETTING THE DAILY EXECUTIONS OF API KEYS
-- CREATE EVENT IF NOT EXISTS reset_api_key_executions
-- ON SCHEDULE EVERY 1 DAY
-- STARTS (CURRENT_DATE + INTERVAL 1 DAY)
-- DO
--   UPDATE api_keys SET executions = 0;

-- SESSIONS TABLE (for distributed session management)
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    data TEXT NOT NULL,
    expires_at DATETIME NULL,
    last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expires (expires_at),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- END of MySQL migration script