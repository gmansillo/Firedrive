CREATE TABLE IF NOT EXISTS `#__firedrive` (
  `id` INTEGER (10) unsigned NOT NULL AUTO_INCREMENT,
  `title` CHARACTER VARYING (250) NOT NULL,
  `alias` CHARACTER VARYING (255) NOT NULL,
  `catid` INTEGER (11) NOT NULL,
  `state` TINYINT (1) NOT NULL,
  `icon` CHARACTER VARYING (255),
  `description` TEXT,
  `publish_up` DATETIME,
  `publish_down` DATETIME,
  `ordering` INTEGER (11) NOT NULL DEFAULT '0',
  `checked_out` INTEGER (11) NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME,
  `access` INTEGER (11),
  `language` character (7) NOT NULL DEFAULT '',
  `created` DATETIME,
  `created_by` INTEGER (10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` CHARACTER VARYING (255) NOT NULL DEFAULT '',
  `modified` DATETIME,
  `modified_by` INTEGER (10) unsigned NOT NULL DEFAULT '0',
  `metakey` text,
  `metadesc` text,
  `metadata` text,
  `params` text,
  `download_counter` INTEGER (11) DEFAULT 0,
  `download_last` DATETIME,
  `file_name` CHARACTER VARYING (255) NOT NULL,
  `file_size` INTEGER (11) unsigned NOT NULL DEFAULT 0,
  `license` CHARACTER VARYING (250),
  `license_link` CHARACTER VARYING (250),
  `version` CHARACTER VARYING (250),
  `notes` TEXT,
  `md5hash` CHARACTER VARYING (250),
  `visibility` TINYINT (1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__firedrive_user_documents` (
  `id` INTEGER (11) NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER (11) NOT NULL,
  `document_id` INTEGER (11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__firedrive_group_documents` (
  `id` INTEGER (11) NOT NULL AUTO_INCREMENT,
  `group_id` INTEGER (11) NOT NULL,
  `document_id` INTEGER (11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__firedrive_download_tracking` (
  `id` INTEGER (10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` INTEGER (11) NOT NULL,
  `user_id`  INTEGER (11) NULL,
  `download_time` DATETIME NOT NULL,
  `ip_address` CHARACTER VARYING (255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;