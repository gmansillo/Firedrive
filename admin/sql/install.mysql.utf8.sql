CREATE TABLE IF NOT EXISTS `#__firedrive` (
  `id` INTEGER (10) unsigned NOT NULL AUTO_INCREMENT,
  `title` CHARACTER VARYING (250) NOT NULL DEFAULT '',
  `alias` CHARACTER VARYING (255) NOT NULL DEFAULT '',
  `catid` INTEGER (11) NOT NULL DEFAULT '0',
  `state` TINYINT (1) NOT NULL default '0',
  `icon` CHARACTER VARYING (255) NOT NULL,
  `description` TEXT,
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` INTEGER (11) NOT NULL DEFAULT '0',
  `checked_out` INTEGER (11) NOT NULL DEFAULT '0',
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` INTEGER (11) NOT NULL DEFAULT '1',
  `language` character (7) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INTEGER (10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` CHARACTER VARYING (255) NOT NULL DEFAULT '',
  `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INTEGER (10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `params` text NOT NULL,
  `download_counter` INTEGER (11) DEFAULT 0,
  `download_last` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `file_name` CHARACTER VARYING (255) NOT NULL DEFAULT '',
  `file_size` INTEGER (11) NOT NULL DEFAULT 0,
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