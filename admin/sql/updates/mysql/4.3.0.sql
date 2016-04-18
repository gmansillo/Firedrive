ALTER TABLE #__simplefilemanager
ADD
(
  `reserved_group` int(11),
  `license` varchar(250),
  `license_link` varchar(250),
  `author` int(11),
  `version` varchar(250),
  `notes` TEXT,
  `md5hash` varchar(250),
  `visibility` tinyint(1) NOT NULL DEFAULT 1
);