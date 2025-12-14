CREATE DATABASE IF NOT EXISTS homelink;
CREATE USER IF NOT EXISTS 'homelink'@'localhost' IDENTIFIED BY 'homelink_pw';
GRANT ALL PRIVILEGES ON homelink.* TO 'homelink'@'localhost';
FLUSH PRIVILEGES;
