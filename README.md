CREATE TABLE user_sessions (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,

    user_id INT NOT NULL,
    token CHAR(64) NOT NULL,
    device_id VARCHAR(100) NOT NULL,

    ip VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,

    created_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    last_seen_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
    expires_at DATETIME2 NOT NULL,

    revoked BIT NOT NULL DEFAULT 0
);


-- lookup principal
CREATE UNIQUE INDEX idx_token
ON user_sessions(token);

-- por usuario
CREATE INDEX idx_user
ON user_sessions(user_id);

-- device tracking
CREATE INDEX idx_user_device
ON user_sessions(user_id, device_id);

-- limpieza / expiración
CREATE INDEX idx_exp
ON user_sessions(expires_at);
