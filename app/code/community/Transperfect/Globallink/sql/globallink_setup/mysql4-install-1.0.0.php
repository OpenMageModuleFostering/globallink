<?php
/* © 2013 Translations.com, a TransPerfect company 
 * Translations.com, Inc., its affiliates and its licensors (collectively, “Translations.com”) own all right, title and interest, 
 * including but not limited to all intellectual property rights, in and to this software and all associated documentation, updates, 
 * new releases and work product, if any (collectively, the “Software”), in both object code and source code formats.  
 * 
 * By making use of the Software, you agree 
 * (i)	to reproduce the copyright, trademark and other proprietary notices contained on or in the Software as delivered to you (including this Notice)
 *      on any reproductions you cause to be made of the Software and further agree not to remove any such notices (including this Notice) from the Software or any copies thereof; 
 * (ii) the Software shall not be further licensed, sold or otherwise transferred by you, except if otherwise approved in writing by an authorized Translations.com representative; 
 * (iii) not to release the results of any benchmark testing of the Software or use any trademark, logo or proprietary notice of Translations.com (except as required by this Notice or law) without Translations.com’s prior written approval; and 
 * (iv) you shall not modify, change nor create any derivative works of the Software.  
 * Any derivative works of the Software created by you, your employees, agents, or contractors, including any and all modifications or changes to the Software, in any format whatsoever, shall be the exclusive property of Translations.com.
*/

$installer = $this;

$installer->startSetup();

$installer->run("


--
-- Table structure for table `globallink_attribute_sets`
--

DROP TABLE IF EXISTS `globallink_attribute_sets`;
CREATE TABLE IF NOT EXISTS `globallink_attribute_sets` (
  `attribute_set_id` int(11) unsigned NOT NULL,
  `attribute_id` int(11) unsigned NOT NULL,
  `attribute_code` varchar(255) NOT NULL,
  `include_in_translation` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`attribute_set_id`,`attribute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `globallink_field`
--

DROP TABLE IF EXISTS `globallink_field`;
CREATE TABLE IF NOT EXISTS `globallink_field` (
  `globallink_field_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `object_type` varchar(255) NOT NULL,
  `field_code` varchar(255) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `include_in_translation` tinyint(1) NOT NULL DEFAULT '0',
  `user_submitted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`globallink_field_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `globallink_locale`
--

DROP TABLE IF EXISTS `globallink_locale`;
CREATE TABLE IF NOT EXISTS `globallink_locale` (
  `gl_locale_code` varchar(10) NOT NULL,
  `gl_locale_desc` varchar(255) NOT NULL,
  `store_id` smallint(5) DEFAULT NULL,
  `gl_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gl_locale_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `globallink_projects`
--

DROP TABLE IF EXISTS `globallink_projects`;
CREATE TABLE IF NOT EXISTS `globallink_projects` (
  `globallink_project_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `globallink_url` varchar(255) NOT NULL DEFAULT '',
  `project_short_code` varchar(255) NOT NULL DEFAULT '',
  `classifier` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(255) NOT NULL DEFAULT 'text/xml',
  `files_per_submission` smallint(6) NOT NULL DEFAULT '9999',
  `max_target_count` smallint(6) NOT NULL DEFAULT '9999',
  PRIMARY KEY (`globallink_project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `globallink_status`
--

DROP TABLE IF EXISTS `globallink_status`;
CREATE TABLE IF NOT EXISTS `globallink_status` (
  `globallink_job_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `globallink_user_id` int(11) unsigned NOT NULL,
  `globallink_project_id` int(11) unsigned NOT NULL,
  `object_type` varchar(255) NOT NULL DEFAULT 'catalog/product',
  `object_id` int(11) unsigned NOT NULL,
  `object_name` varchar(255) NOT NULL,
  `document_name` text NOT NULL,
  `document_id` text NOT NULL,
  `submission_name` text NOT NULL,
  `submission_id` text NOT NULL,
  `gl_source_locale` varchar(10) NOT NULL,
  `gl_target_locale` varchar(10) NOT NULL,
  `create_date` bigint(20) NOT NULL,
  `update_date` bigint(20) NOT NULL,
  `due_date` bigint(20) NOT NULL,
  `status` varchar(255) NOT NULL,
  `submission_type` varchar(255) NOT NULL DEFAULT 'Manual',
  `submitter` varchar(255) NOT NULL,
  `last_modified` bigint(20) DEFAULT NULL,
  `changed` tinyint(1) NOT NULL DEFAULT '0',
  `extra` text DEFAULT NULL,
  PRIMARY KEY (`globallink_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `globallink_settings`
--

DROP TABLE IF EXISTS `globallink_settings`;
CREATE TABLE IF NOT EXISTS `globallink_settings` (
  `gl_setting_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gl_setting_name` mediumblob DEFAULT NULL,
  `gl_setting_info` smallint(5) DEFAULT NULL,
  `gl_setting_extra` text DEFAULT NULL,
  PRIMARY KEY (`gl_setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Insert locales
--

INSERT INTO  `globallink_settings` ( `gl_setting_name`) VALUES ('gl_logging');
INSERT INTO  `globallink_settings` ( `gl_setting_name`) VALUES ('gl_cron');

-- --------------------------------------------------------

--
-- Table structure for table `globallink_users`
--

DROP TABLE IF EXISTS `globallink_users`;
CREATE TABLE IF NOT EXISTS `globallink_users` (
  `globallink_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `globallink_user_name` varchar(255) NOT NULL,
  `globallink_user_password` varchar(255) NOT NULL,
  PRIMARY KEY (`globallink_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Insert locales
--

INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('aa-DJ', 'Afar, Djibouti');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('aa-ER', 'Afar, Eritrea');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('aa-ET', 'Afar, Ethiopia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ab-GE', 'Abkhazian, Georgia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ae-ES', 'Spain, Aranese');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('af-NA', 'Afrikaans, Namibia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('af-ZA', 'Afrikaans, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('am-ET', 'Amharic, Ethiopia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('an-ES', 'Aragonese, Spain');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-AE', 'Arabic, United Arab Emirates');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-BH', 'Arabic, Bahrain');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-DZ', 'Arabic, Algeria');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-EG', 'Arabic, Egypt');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-IQ', 'Arabic, Iraq');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-JO', 'Arabic, Jordan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-KW', 'Arabic, Kuwait');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-LB', 'Arabic, Lebanon');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-LY', 'Arabic, Libya');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-MA', 'Arabic, Morocco');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-OM', 'Arabic, Oman');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-QA', 'Arabic, Qatar');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-SA', 'Arabic, Saudi Arabia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-SD', 'Arabic, Sudan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-SY', 'Arabic, Syria');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-TN', 'Arabic, Tunisia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ar-YE', 'Arabic, Yemen');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('as-IN', 'Assamese, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ay-BO', 'Aymara, Bolivia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ay-PE', 'Aymara, Peru');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('az-AZ', 'Azerbaijani, Azerbaijan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ba-RU', 'Bashkir, Russia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('bd-ID', 'Bahasa, Indonesia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('bd-MY', 'Bahasa, Malaysia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('be-BY', 'Byelorussian, Byelorussia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('bg-BG', 'Bulgarian, Bulgaria');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('bn-BD', 'Bengali, Bangladesh');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('bn-IN', 'Bengali, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('bo-CN', 'Tibetan, China');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ca-ES', 'Catalan, Spain');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('cb-PH', 'Cebuano, Philippines');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('cs-CZ', 'Czech, Czech Republic');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('cy-GB', 'Welsh, United Kingdom');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('da-DK', 'Danish, Denmark');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('de-AT', 'German, Austria');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('de-CH', 'German, Switzerland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('de-DE', 'German, Germany');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('de-LI', 'German, Liechtenstein');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('de-LU', 'German, Luxembourg');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('el-CY', 'Greek, Cyprus');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('el-GR', 'Greek, Greece');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-AU', 'English, Australia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-BZ', 'English, Belize');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-CA', 'English, Canada');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-CB', 'English, Caribbean');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-GB', 'English, United Kingdom');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-ID', 'English, Indonesia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-IE', 'English, Ireland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-IN', 'English, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-JM', 'English, Jamaica');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-MT', 'English, Malta');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-NZ', 'English, New Zealand');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-PH', 'English, Philippines');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-RH', 'English, Zimbabwe');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-SG', 'English, Singapore');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-TT', 'English, Trinidad and Tobago');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-US', 'English, United States');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('en-ZA', 'English, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-AR', 'Spanish, Argentina');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-BO', 'Spanish, Bolivia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-CL', 'Spanish, Chile');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-CO', 'Spanish, Colombia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-CR', 'Spanish, Costa Rica');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-DO', 'Spanish, Dominican Republic');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-EC', 'Spanish, Ecuador');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-ES', 'Spanish, Spain');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-GT', 'Spanish, Guatemala');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-HN', 'Spanish, Honduras');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-LA', 'Spanish, Latin America');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-MX', 'Spanish, Mexico');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-NI', 'Spanish, Nicaragua');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-PA', 'Spanish, Panama');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-PE', 'Spanish, Peru');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-PR', 'Spanish, Puerto Rico');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-PY', 'Spanish, Paraguay');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-SV', 'Spanish, El Salvador');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-US', 'Spanish, United States');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-UY', 'Spanish, Uruguay');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('es-VE', 'Spanish, Venezuela');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('et-EE', 'Estonian, Estonia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('eu-ES', 'Basque, Spain');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fa-AF', 'Persian-Dari, Afghanistan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fa-IR', 'Farsi, Iran');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fi-FI', 'Finnish, Finland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fj-FJ', 'Fijian, Fiji');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fo-FO', 'Faroese, Faroe Islands');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-BE', 'French, Belgium');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-CA', 'French, Canada');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-CH', 'French, Switzerland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-FR', 'French, France');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-LU', 'French, Luxembourg');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-MC', 'French, Monaco');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-NC', 'French, New Caledonia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fr-PF', 'French, French Polynesia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fy-NL', 'Frisian, Netherlands');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ga-IE', 'Irish Gaelic, Ireland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('gd-GB', 'Scottish Gaelic, United Kingdom');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('gl-ES', 'Galician, Spain');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('gs-ES', 'Gascon, Spain');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('gu-IN', 'Gujarati, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('he-IL', 'Hebrew, Israel');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('hi-IN', 'Hindi, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('hr-HR', 'Croatian, Croatia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('hu-HU', 'Hungarian, Hungary');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('hy-AM', 'Armenian, Armenia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('id-ID', 'Indonesian, Indonesia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('is-IS', 'Icelandic, Iceland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('it-CH', 'Italian, Switzerland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('it-IT', 'Italian, Italy');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ja-JP', 'Japanese, Japan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ka-GE', 'Georgian, Georgia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('km-KH', 'Khmer, Cambodia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('kn-IN', 'Kannada, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ko-KR', 'Korean, South Korea');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ko-KP', 'Korean, North Korea');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ku-TR', 'Kurdish, Turkey');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('lb-LU', 'Luxembourgish, Luxembourg');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ln-CD', 'Lingala, The Democratic Republic Of Congo');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('lo-LA', 'Lao, Laos');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('lt-LT', 'Lithuanian, Lithuania');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('lv-LV', 'Latvian, Latvia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('mg-MG', 'Malagasy, Madagascar');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('mk-MK', 'Macedonian, Macedonia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ml-IN', 'Malayalam, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('mn-MN', 'Mongolian, Mongolia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('mo-MD', 'Moldovan, Moldova');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('mr-IN', 'Marathi, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ms-BX', 'Malay, Brunei Darussalam');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ms-MY', 'Malay, Malaysia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('mt-MT', 'Maltese, Malta');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('my-MM', 'Burmese, MyanMar (Burma)');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('nb-NO', 'Norwegian Bokmal, Norway');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('nd-ZA', 'North Ndebele, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ne-NP', 'Nepali, Nepal');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('nl-BE', 'Dutch, Belgium');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('nl-NL', 'Dutch, Netherlands');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('no-NO', 'Norwegian (Nynorsk), Norway');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('nr-ZA', 'South Ndebele, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ns-ZA', 'Nothern Sotho, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ny-MW', 'Nyanja, Malawi');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('oc-FR', 'Occitan, France');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('om-ET', 'Oromo, Ethiopia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('or-IN', 'Oriya, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('pa-IN', 'Punjabi (Gurmukha), India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('pa-PK', 'Punjabi (Shahmukhi), Pakistan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('pl-PL', 'Polish, Poland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ps-AF', 'Pashto, Afghanistan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('pt-BR', 'Portuguese, Brazil');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('pt-PT', 'Portuguese, Portugal');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('qu-BO', 'Quechua, Bolivia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('qu-EU', 'Quechua, Ecuador');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ro-RO', 'Romanian, Romania');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ru-RU', 'Russian, Russia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('rw-RW', 'Kinyarwanda, Rwanda');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sh-YU', 'Serbo_Croatian, Yugoslavia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('si-LK', 'Sinhalese, Sri Lanka');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sk-SK', 'Slovak, Slovakia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sl-SL', 'Slovenian, Slovenia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('so-SO', 'Somali, Somalia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sq-AL', 'Albanian, Albania');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sr-BA', 'Serbian, Bosnia and Herzegovina');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sr-CS', 'Serbian, Serbia and Montenegro');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sr-ME', 'Serbian, Montenegro');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sr-YU', 'Serbian (Cyrillic), Yugoslavia');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ss-ZA', 'Swati, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('st-ZA', 'Southern Sotho, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('su-SU', 'Sudanese, Sudan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sv-FI', 'Swedish, Finland');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sv-SE', 'Swedish, Sweden');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('sw-KE', 'Swahili, Kenya');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('tg-TJ', 'Tajik, Tajikistan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('th-TH', 'Thai, Thailand');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ta-IN', 'Tamil, India');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('tl-PH', 'Tagalog, Philippines');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('tn-ZA', 'Tswana, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('tr-TR', 'Turkish, Turkey');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ts-ZA', 'Tsonga, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('uk-UA', 'Ukrainian, Ukraine');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ur-PK', 'Urdu (Pakistan), Pakistan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ve-ZA', 'Venda, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('vi-VN', 'Vietnamese, Vietnam');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('uz-UZ', 'Uzbek, Uzbekistan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('xh-ZA', 'Xhosa, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('zh-CN', 'Chinese (Simplified), China');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('zh-HK', 'Chinese, Hong Kong');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('zh-SG', 'Chinese (Simplified), Singapore');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('zh-TW', 'Chinese (Traditional), Taiwan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('zh-XM', 'Chinese, Macau');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('zu-ZA', 'Zulu, South Africa');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('kk-KZ', 'Kazakh, Kazakhstan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ru-KZ', 'Russian, Kazakhstan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ky-KG', 'Kirghiz, Kyrgyzstan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ru-KG', 'Russian, Kyrgyzstan');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ru-UA', 'Russian, Ukraine');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ru-BY', 'Russian, Belarus');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('fj-PH', 'Fijian, Philippines');
INSERT INTO  `globallink_locale` ( `gl_locale_code`, `gl_locale_desc`) VALUES ('ms-SG', 'Malay, Singapore');


--
-- Insert field data for non product types
--

INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('cms/page', 'title', 'Page title', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('cms/page', 'content_heading', 'Content Heading', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('cms/page', 'content', 'Content', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('cms/page', 'meta_keywords', 'Meta Keywords', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('cms/page', 'meta_description', 'Meta Description', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('cms/block', 'content', 'Content', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('catalog/category', 'name', 'Name', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('catalog/category', 'description', 'Description', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('catalog/category', 'meta_title', 'Page Title', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('catalog/category', 'meta_keywords', 'Meta Keywords', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('catalog/category', 'meta_description', 'Meta Description', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('adminhtml/email_template', 'template_subject', 'Template Subject', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('adminhtml/email_template', 'template_text', 'Template Content', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('newsletter/template', 'template_subject', 'Template Subject', 1);
INSERT INTO `globallink_field` (`object_type`, `field_code`, `field_name`, `include_in_translation`) VALUES('newsletter/template', 'template_text', 'Template Content', 1);

");


// Insert Attribute Set data for Products
$entityTypeId = Mage::getModel('eav/entity')
        ->setType('catalog_product')
        ->getTypeId();

$attributeSetCollection = Mage::getModel('eav/entity_attribute_set')
        ->getCollection()
        ->setEntityTypeFilter($entityTypeId);

foreach ($attributeSetCollection as $_attributeSet) {
  $arr = $_attributeSet->getData();
  $attribute_set_id = $arr['attribute_set_id'];

  $attributes = Mage::getModel('catalog/product_attribute_api')->items($attribute_set_id);
  $attr_arr = array();
  foreach ($attributes as $attr) {
    $attribute = Mage::getModel('eav/entity_attribute')->load($attr['attribute_id']);
    if ($attribute->getIsVisible() && !$attribute->getIsGlobal()) {
      if ($attribute->getFrontendInput() == 'text' || $attribute->getFrontendInput() == 'textarea') {
        $attr_arr[$attr['attribute_id']] = array('attribute_set_id' => $attribute_set_id, 'attribute_id' => $attr['attribute_id'],
            'attribute_code' => $attribute->getAttributeCode(), 'attribute_type' => $attribute->getFrontendInput(), 'attribute_name' => $attribute->getFrontendLabel());
      }
    }
  }
  $insert_arr = array();
  foreach ($attr_arr as $_att_id => $_attribute) {
    $insert_arr[$_att_id]['attribute'] = $_attribute;
    $insert_arr[$_att_id]['translatable'] = 1;
  }
  Mage::helper("globallink")->gl_debug("[" . __FILE__ . "][" . __LINE__ . "][" . __METHOD__ . "][Attribute Array]", $attr_arr);
  Mage::helper("globallink")->delete_and_update_attribute_set_config($attribute_set_id, $insert_arr);
}

$installer->endSetup();

