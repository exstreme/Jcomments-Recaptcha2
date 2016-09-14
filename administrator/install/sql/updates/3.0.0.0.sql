CREATE TABLE IF NOT EXISTS `#__jcomments_mailq` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`email` varchar(255) NOT NULL,
`subject` text NOT NULL,
`body` text NOT NULL,
`created` datetime NOT NULL,
`attempts` tinyint(1) NOT NULL DEFAULT '0',
`priority` tinyint(1) NOT NULL DEFAULT '0',
`session_id` VARCHAR(200) DEFAULT NULL,
PRIMARY KEY  (`id`),
KEY `idx_priority` (`priority`),
KEY `idx_attempts` (`attempts`)
) DEFAULT CHARSET=utf8;

ALTER IGNORE TABLE `#__jcomments_objects` CHANGE `link` `link` TEXT NOT NULL DEFAULT '';
ALTER IGNORE TABLE `#__jcomments_objects` ADD `category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `object_group`;
ALTER IGNORE TABLE `#__jcomments_subscriptions` ADD `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER IGNORE TABLE `#__jcomments_subscriptions` ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER IGNORE TABLE `#__jcomments_custom_bbcodes` ADD `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER IGNORE TABLE `#__jcomments_custom_bbcodes` ADD `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

CREATE TABLE IF NOT EXISTS `#__jcomments_smilies` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`code` varchar(39) NOT NULL DEFAULT '',
`alias` varchar(39) NOT NULL DEFAULT '',
`image` varchar(255) NOT NULL,
`name` varchar(255) NOT NULL,
`published` tinyint(1) NOT NULL DEFAULT '0',
`ordering` int(11) unsigned NOT NULL DEFAULT '0',
`checked_out` int(11) unsigned NOT NULL DEFAULT '0',
`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY (`id`),
KEY `idx_checkout` (`checked_out`)
) DEFAULT CHARSET=utf8;

UPDATE `#__jcomments_settings` SET `name` = 'enable_plugins' WHERE `name` = 'enable_mambots';
UPDATE `#__jcomments_settings` SET `name` = 'comments_list_order' WHERE `name` = 'comments_order';
UPDATE `#__jcomments_settings` SET `name` = 'comments_tree_order' WHERE `name` = 'tree_order';
UPDATE `#__jcomments_settings` SET `name` = 'smilies' WHERE `name` = 'smiles';
UPDATE `#__jcomments_settings` SET `name` = 'enable_smilies' WHERE `name` = 'enable_smiles';
UPDATE `#__jcomments_settings` SET `name` = 'smilies_path' WHERE `name` = 'smiles_path';
UPDATE `#__jcomments_settings` SET `value` = '/components/com_jcomments/images/smilies/' WHERE `value` = '/components/com_jcomments/images/smiles/';

ALTER TABLE `#__jcomments_votes` ADD INDEX `idx_user` (`userid`, `date`);

UPDATE #__jcomments_custom_bbcodes
SET `simple_replacement_html` = '<iframe width="425" height="350" src="//www.youtube.com/embed/{IDENTIFIER}?rel=0" frameborder="0" allowfullscreen></iframe>'
, `simple_replacement_text` = 'http://youtu.be/{IDENTIFIER}'
, `replacement_html` = '<iframe width="425" height="350" src="//www.youtube.com/embed/${1}?rel=0" frameborder="0" allowfullscreen></iframe>'
, `replacement_text` = 'http://youtu.be/${1}'
WHERE `simple_replacement_html` = '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/{IDENTIFIER}"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/{IDENTIFIER}" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';

UPDATE #__jcomments_custom_bbcodes
SET `simple_replacement_html` = '<iframe width="425" height="350" src="//www.facebook.com/photo.php?v={IDENTIFIER}" frameborder="0"></iframe>'
, `simple_replacement_text` = 'http://www.facebook.com/photo.php?v={IDENTIFIER}'
, `replacement_html` = '<iframe width="425" height="350" src="//www.facebook.com/photo.php?v=${1}" frameborder="0"></iframe>'
, `replacement_text` = 'http://www.facebook.com/photo.php?v=${1}'
WHERE `simple_replacement_html` = '<object width="425" height="350"><param name="movie" value="http://www.facebook.com/v/{IDENTIFIER}"></param><param name="wmode" value="transparent"></param><embed src="http://www.facebook.com/v/{IDENTIFIER}" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';


INSERT INTO `#__jcomments_custom_bbcodes` (`name`, `simple_pattern`, `simple_replacement_html`, `simple_replacement_text`, `pattern`, `replacement_html`, `replacement_text`, `button_enabled`, `ordering`, `published`, `button_acl`)
SELECT 'YouTube Video (alternate syntax)'
	, '[youtube]http://www.youtube.com/watch?feature=player_embedded&v={IDENTIFIER}[/youtube]'
	, '<iframe width="425" height="350" src="//www.youtube.com/embed/{IDENTIFIER}?rel=0" frameborder="0" allowfullscreen></iframe>'
	, 'http://youtu.be/{IDENTIFIER}'
	, '\\[youtube\\]http\\://www\\.youtube\\.com/watch\\?feature\\=player_embedded&v\\=([\\w0-9-_]+)\\[/youtube\\]'
	, '<iframe width="425" height="350" src="//www.youtube.com/embed/${1}?rel=0" frameborder="0" allowfullscreen></iframe>'
	, 'http://youtu.be/${1}'
	, 0
	, 100
	, 1
	, '1,9,6,7,2,3,4,5,8'
FROM `#__jcomments_custom_bbcodes`
WHERE NOT EXISTS (SELECT * FROM `#__jcomments_custom_bbcodes` WHERE `simple_pattern` = '[youtube]http://www.youtube.com/watch?feature=player_embedded&v={IDENTIFIER}[/youtube]')
LIMIT 0, 1;

INSERT INTO `#__jcomments_custom_bbcodes` (`name`, `simple_pattern`, `simple_replacement_html`, `simple_replacement_text`, `pattern`, `replacement_html`, `replacement_text`, `button_enabled`, `ordering`, `published`, `button_acl`)
SELECT 'YouTube Video (alternate syntax)'
	, '[youtube]http://youtu.be/{IDENTIFIER}[/youtube]'
	, '<iframe width="425" height="350" src="//www.youtube.com/embed/{IDENTIFIER}?rel=0" frameborder="0" allowfullscreen></iframe>'
	, 'http://youtu.be/{IDENTIFIER}'
	, '\\[youtube\\]http\\://youtu\\.be/([\\w0-9-_]+)\\[/youtube\\]'
	, '<iframe width="425" height="350" src="//www.youtube.com/embed/${1}?rel=0" frameborder="0" allowfullscreen></iframe>'
	, 'http://youtu.be/${1}'
	, 0
	, 101
	, 1
	, '1,9,6,7,2,3,4,5,8'
FROM `#__jcomments_custom_bbcodes`
WHERE NOT EXISTS (SELECT * FROM `#__jcomments_custom_bbcodes` WHERE `simple_pattern` = '[youtube]http://youtu.be/{IDENTIFIER}[/youtube]')
LIMIT 0, 1;

