-- Create database and roles for System - E (example)
CREATE DATABASE IF NOT EXISTS `system_e`;

-- app_user: limited privileges for application (no DROP/ALTER)
CREATE USER IF NOT EXISTS 'app_user'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, INDEX, CREATE TEMPORARY TABLES ON `system_e`.* TO 'app_user'@'localhost';

-- read_only user
CREATE USER IF NOT EXISTS 'readonly_user'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
GRANT SELECT ON `system_e`.* TO 'readonly_user'@'localhost';

FLUSH PRIVILEGES;
