# ---------------------------------------------------------
# UPDATES TO Database tables for utf8 compliance
# ---------------------------------------------------------
ALTER TABLE `altuseremail` DEFAULT CHARACTER SET utf8;
ALTER TABLE `altuseremail` CHANGE `email` `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `announcement` DEFAULT CHARACTER SET utf8;
ALTER TABLE `announcement` CHANGE `title` `title` varchar(125) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `announcement` CHANGE `excerpt` `excerpt` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `announcement` CHANGE `post` `post` longtext CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `announcement_recipient` DEFAULT CHARACTER SET utf8;
ALTER TABLE `autoresponders` DEFAULT CHARACTER SET utf8;
ALTER TABLE `autoresponders` CHANGE `name` `name` varchar(75) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `autoresponders` CHANGE `subject` `subject` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `autoresponders` CHANGE `contents` `contents` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `autoresponders` CHANGE `contents_html` `contents_html` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `autoresponders` CHANGE `description` `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `billingtype` DEFAULT CHARACTER SET utf8;
ALTER TABLE `billingtype` CHANGE `name` `name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `billingtype` CHANGE `description` `description` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `billingtype` CHANGE `detail` `detail` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `billingtype` CHANGE `price` `price` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT '0.00';
ALTER TABLE `clients_notes` DEFAULT CHARACTER SET utf8;
ALTER TABLE `clients_notes` CHANGE `note` `note` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `clients_notes_tickettypes` DEFAULT CHARACTER SET utf8;
ALTER TABLE `conversation` DEFAULT CHARACTER SET utf8;
ALTER TABLE `conversation_messages` DEFAULT CHARACTER SET utf8;
ALTER TABLE `conversation_messages` CHANGE `message` `message` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `conversation_participants` DEFAULT CHARACTER SET utf8;
ALTER TABLE `conversation_participants` CHANGE `color` `color` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT 'black';
ALTER TABLE `coupons` DEFAULT CHARACTER SET utf8;
ALTER TABLE `coupons` CHANGE `coupons_name` `coupons_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `coupons` CHANGE `coupons_description` `coupons_description` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `coupons` CHANGE `coupons_code` `coupons_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `coupons_packages` DEFAULT CHARACTER SET utf8;
ALTER TABLE `coupons_usage` DEFAULT CHARACTER SET utf8;
ALTER TABLE `currency` DEFAULT CHARACTER SET utf8;
ALTER TABLE `currency` CHANGE `name` `name` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `currency` CHANGE `symbol` `symbol` char(3) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `currency` CHANGE `abrv` `abrv` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `currency` CHANGE `alignment` `alignment` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT 'left';
ALTER TABLE `customuserfields` DEFAULT CHARACTER SET utf8;
ALTER TABLE `customuserfields` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `customuserfields` CHANGE `dropdownoptions` `dropdownoptions` longtext CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `departments` DEFAULT CHARACTER SET utf8;
ALTER TABLE `departments` ADD `emails_to_notify` TEXT NULL ;
ALTER TABLE `departments` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `departments_members` DEFAULT CHARACTER SET utf8;
ALTER TABLE `email_queue` DEFAULT CHARACTER SET utf8;
ALTER TABLE `email_queue` CHANGE `subject` `subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue` CHANGE `from` `from` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue` CHANGE `from_name` `from_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue` CHANGE `bcc` `bcc` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue` CHANGE `emailtype` `emailtype` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue` CHANGE `body` `body` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue` CHANGE `dfilename` `dfilename` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue` CHANGE `attachment` `attachment` longtext CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `email_queue_addressees` DEFAULT CHARACTER SET utf8;
ALTER TABLE `escalationrules` DEFAULT CHARACTER SET utf8;
ALTER TABLE `escalationrules` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `escalationrules` CHANGE `ticket_tag` `ticket_tag` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `escalationrules` CHANGE `new_tag` `new_tag` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `escalationrules` CHANGE `transcription_destinataries` `transcription_destinataries` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `escalationrules_departments` DEFAULT CHARACTER SET utf8;
ALTER TABLE `groups` DEFAULT CHARACTER SET utf8;
ALTER TABLE `groups` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `groups` CHANGE `description` `description` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `groups` CHANGE `groupcolor` `groupcolor` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `help` DEFAULT CHARACTER SET utf8;
ALTER TABLE `help` CHANGE `title` `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `help` CHANGE `detail` `detail` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `help` CHANGE `linkwords` `linkwords` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT 'Click to view available tags';
ALTER TABLE `nameserver` DEFAULT CHARACTER SET utf8;
ALTER TABLE `nameserver` CHANGE `ip` `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `nameserver` CHANGE `hostname` `hostname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `package` DEFAULT CHARACTER SET utf8;
ALTER TABLE `package` CHANGE `planname` `planname` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `package` CHANGE `description` `description` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `package` CHANGE `welcomeemail_text` `welcomeemail_text` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `package` CHANGE `welcomeemail_html` `welcomeemail_html` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `package` CHANGE `welcomeemail_subject` `welcomeemail_subject` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `package_server` DEFAULT CHARACTER SET utf8;
ALTER TABLE `package_tld` DEFAULT CHARACTER SET utf8;
ALTER TABLE `package_variable` DEFAULT CHARACTER SET utf8;
ALTER TABLE `package_variable` CHANGE `varname` `varname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `package_variable` CHANGE `value` `value` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `packageaddon` DEFAULT CHARACTER SET utf8;
ALTER TABLE `packageaddon` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `packageaddon` CHANGE `description` `description` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `packageaddon` CHANGE `plugin_var` `plugin_var` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `packageaddon_prices` DEFAULT CHARACTER SET utf8;
ALTER TABLE `packageaddon_prices` CHANGE `detail` `detail` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `packageaddon_prices` CHANGE `plugin_var_value` `plugin_var_value` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `plugin_custom_data` DEFAULT CHARACTER SET utf8;
ALTER TABLE `plugin_custom_data` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `plugin_custom_data` CHANGE `value` `value` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `plugin_custom_data` CHANGE `plugin_name` `plugin_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `plugin_custom_data` CHANGE `plugin_type` `plugin_type` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `product_addon` DEFAULT CHARACTER SET utf8;
ALTER TABLE `productgroup_addon` DEFAULT CHARACTER SET utf8;
ALTER TABLE `promotion` DEFAULT CHARACTER SET utf8;
ALTER TABLE `promotion` CHANGE `description` `description` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `promotion` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `promotion` CHANGE `welcomeemail_text` `welcomeemail_text` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `promotion` CHANGE `welcomeemail_subject` `welcomeemail_subject` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `promotion` CHANGE `welcomeemail_html` `welcomeemail_html` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `promotion` CHANGE `maindomain` `maindomain` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT 'yourdomain.com';
ALTER TABLE `recurringfee` DEFAULT CHARACTER SET utf8;
ALTER TABLE `recurringfee` CHANGE `description` `description` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `recurringfee` CHANGE `detail` `detail` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `recurringfee` CHANGE `monthlyusage` `monthlyusage` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `registrars` DEFAULT CHARACTER SET utf8;
ALTER TABLE `registrars` CHANGE `plugin` `plugin` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `registrars` CHANGE `tld` `tld` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `registrars` CHANGE `extra_attributes` `extra_attributes` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `report` DEFAULT CHARACTER SET utf8;
ALTER TABLE `report` CHANGE `name` `name` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` DEFAULT CHARACTER SET utf8;
ALTER TABLE `routingrules` CHANGE `name` `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` CHANGE `emails` `emails` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` CHANGE `copy_destinataries` `copy_destinataries` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` CHANGE `filter_out` `filter_out` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` CHANGE `pop3_hostname` `pop3_hostname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` CHANGE `pop3_port` `pop3_port` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` CHANGE `pop3_username` `pop3_username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules` CHANGE `pop3_password` `pop3_password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `routingrules_groups` DEFAULT CHARACTER SET utf8;
ALTER TABLE `server` DEFAULT CHARACTER SET utf8;
ALTER TABLE `server` CHANGE `name` `name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `server` CHANGE `hostname` `hostname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `server` CHANGE `sharedip` `sharedip` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `server` CHANGE `plugin` `plugin` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `server` CHANGE `statsurl` `statsurl` varchar(225) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `serverip` DEFAULT CHARACTER SET utf8;
ALTER TABLE `serverip` CHANGE `ip` `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `serverplugin_options` DEFAULT CHARACTER SET utf8;
ALTER TABLE `serverplugin_options` CHANGE `optionname` `optionname` varchar(125) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `services_log` DEFAULT CHARACTER SET utf8;
ALTER TABLE `services_log` CHANGE `plugin` `plugin` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `services_log` CHANGE `results` `results` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `services_log` CHANGE `errors` `errors` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `services_log` CHANGE `status` `status` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `setting_options` DEFAULT CHARACTER SET utf8;
ALTER TABLE `setting_options` CHANGE `name` `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `setting_options` CHANGE `value` `value` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `taxrule` DEFAULT CHARACTER SET utf8;
ALTER TABLE `taxrule` CHANGE `countrycode` `countrycode` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `taxrule` CHANGE `state` `state` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `taxrule` CHANGE `name` `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `team_status` DEFAULT CHARACTER SET utf8;
ALTER TABLE `team_status` CHANGE `userstatus` `userstatus` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `team_status_follow` DEFAULT CHARACTER SET utf8;
ALTER TABLE `tld` DEFAULT CHARACTER SET utf8;
ALTER TABLE `tld` CHANGE `name` `name` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `tld` CHANGE `plugin` `plugin` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `tld` CHANGE `pricing` `pricing` text CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;

ALTER TABLE `users` DEFAULT CHARACTER SET utf8;
ALTER TABLE `users` CHANGE `paymenttype` `paymenttype` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT '0';
ALTER TABLE `users` CHANGE `password` `password` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `users` CHANGE `signature_text` `signature_text` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `users` CHANGE `signature_html` `signature_html` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `users` CHANGE `currency` `currency` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci  NULL   DEFAULT '0';
ALTER TABLE `users` CHANGE `data1` `data1` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `users` CHANGE `ccmonth` `ccmonth` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT '0';
ALTER TABLE `users` CHANGE `ccyear` `ccyear` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL  DEFAULT '0';
ALTER TABLE `users` CHANGE `cclastfour` `cclastfour` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `users` CHANGE `data2` `data2` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `users` CHANGE `data3` `data3` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `users` CHANGE `firstname` `firstname` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `users` CHANGE `lastname` `lastname` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `users` CHANGE `email` `email` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `users` CHANGE `organization` `organization` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
ALTER TABLE `users` CHANGE `usernotes` `usernotes` text CHARACTER SET utf8 COLLATE utf8_general_ci  NULL  ;
ALTER TABLE `users` CHANGE `usernotespos` `usernotespos` varchar(75) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL ;
# ---------------------------------------------------------
# Removing unneeded settings
# ---------------------------------------------------------
Delete FROM `setting` WHERE `name` = 'Character Set';
Delete FROM `setting` WHERE `name` = 'Show Client UserID';
Delete FROM `setting` WHERE `name` = 'Title';
Delete FROM `setting` WHERE `name` = 'E-mail For New High Priority Support Tickets';
update `setting` set type=12 WHERE `name` = 'Show Change Domain Password';
update `setting` set type=12 WHERE `name` = 'Access Via Domain Username';

UPDATE `setting` SET `value` =  'Get CDG' WHERE `name` = 'plugin_cdgcommerceform_Plugin Name';
UPDATE `setting` SET `value` =  'Get eNom' WHERE `name` = 'plugin_enomform_Plugin Name';


# ----------------------------------------------------------------------------------
# New setting for the checking wiki for context sensitive help
# ----------------------------------------------------------------------------------
INSERT INTO `setting` VALUES (NULL, 'Show Context Help When Available', '1', '', 'Select Yes if you want to check the wiki at http://www.clientexec.com/wiki has any help information for the active view.', 1, 1, 1, 0, 0, 0, 3, 0, 0, 0, 0);