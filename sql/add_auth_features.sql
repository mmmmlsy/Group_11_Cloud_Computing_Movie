-- FilmVault Auth Features Migration
-- Run on EC2: mysql -h <RDS_ENDPOINT> -P 3306 -u admin -p filmvault < sql/add_auth_features.sql

USE filmvault;

ALTER TABLE users
    ADD COLUMN email_verified    TINYINT(1)   NOT NULL DEFAULT 0          AFTER display_name,
    ADD COLUMN verification_token VARCHAR(64)  NULL     DEFAULT NULL        AFTER email_verified,
    ADD COLUMN reset_token        VARCHAR(64)  NULL     DEFAULT NULL        AFTER verification_token,
    ADD COLUMN reset_expires      DATETIME     NULL     DEFAULT NULL        AFTER reset_token;
