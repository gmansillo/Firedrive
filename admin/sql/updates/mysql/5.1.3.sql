SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

UPDATE `#__firedrive` SET created = file_created;
ALTER TABLE `#__firedrive` DROP COLUMN `file_created`;

UPDATE `#__firedrive` SET created_by = author;
ALTER TABLE `#__firedrive` DROP COLUMN `author`;