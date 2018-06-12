CREATE TABLE IF NOT EXISTS `#__simplefilemanager` (
  `id` integer (10) unsigned NOT NULL AUTO_INCREMENT,
  `title` character varying (250) NOT NULL DEFAULT '',
  `alias` character varying (255) NOT NULL DEFAULT '',
  `catid` integer (11) NOT NULL DEFAULT '0',
  `state` tinyint (1) NOT NULL default '0',
  `icon` character varying (255) NOT NULL,
  `description` TEXT,
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` integer (11) NOT NULL DEFAULT '0',
  `checked_out` integer (11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` integer (11) NOT NULL DEFAULT '1',
  `language` character (7) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` integer (10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` character varying (255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` integer (10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `params` text NOT NULL,
  `download_counter` integer (11) DEFAULT 0,
  `download_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `file_name` character varying (255) NOT NULL DEFAULT '',
  `file_size` integer (11) NOT NULL DEFAULT 0,
  `license` character varying (250),
  `license_link` character varying (250),
  `version` character varying (250),
  `notes` TEXT,
  `md5hash` character varying (250),
  `visibility` tinyinteger (1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__simplefilemanager_user_documents` (
  `id` integer (11) NOT NULL AUTO_INCREMENT,
  `user_id` integer (11) NOT NULL,
  `document_id` integer (11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__simplefilemanager_group_documents` (
  `id` integer (11) NOT NULL AUTO_INCREMENT,
  `group_id` integer (11) NOT NULL,
  `document_id` integer (11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__simplefilemanager_download_tracking` (
  `id` integer (10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` integer (11) NOT NULL,
  `user_id`  integer (11) NULL,
  `download_time` datetime NOT NULL,
  `ip_address` character varying (255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;