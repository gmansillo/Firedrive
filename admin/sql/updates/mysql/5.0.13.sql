SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE IF NOT EXISTS `#__simplefilemanager_user_documents` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11) NOT NULL,
  `document_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__simplefilemanager_group_documents` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `group_id`    INT(11) NOT NULL,
  `document_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT IGNORE INTO `#__simplefilemanager_user_documents` (user_id, document_id)
  SELECT
    reserved_user,
    id
  FROM `#__simplefilemanager`
  WHERE reserved_user != NULL;

INSERT IGNORE INTO `#__simplefilemanager_group_documents` (group_id, document_id)
  SELECT
    reserved_group,
    id
  FROM `#__simplefilemanager`
  WHERE reserved_group != NULL;

UPDATE `#__simplefilemanager`
SET `reserved_user` = NULL, `reserved_group` = NULL;