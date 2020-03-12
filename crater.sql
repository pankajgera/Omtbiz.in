/*
 Navicat Premium Data Transfer

 Source Server         : local
 Source Server Type    : MySQL
 Source Server Version : 50643
 Source Host           : localhost:3306
 Source Schema         : crater

 Target Server Type    : MySQL
 Target Server Version : 50643
 File Encoding         : 65001

 Date: 28/12/2019 16:11:47
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for addresses
-- ----------------------------
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_street_1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_street_2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country_id` int(10) unsigned DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_country_id_foreign` (`country_id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of addresses
-- ----------------------------
BEGIN;
INSERT INTO `addresses` VALUES (1, NULL, NULL, NULL, 'Jaipur', 'Rajasthan', 101, '302001', NULL, NULL, NULL, 1, '2019-12-27 16:38:50', '2019-12-27 16:38:50');
COMMIT;

-- ----------------------------
-- Table structure for companies
-- ----------------------------
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unique_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of companies
-- ----------------------------
BEGIN;
INSERT INTO `companies` VALUES (1, 'Cracker Digital', NULL, 'Rd3ZDw9IiawGQZDJAVRSsUNjNHtpskFGOeqhKN0f4uln8aIJSbNpC5k9Z4ff', '2019-12-27 16:38:50', '2019-12-27 16:38:50');
COMMIT;

-- ----------------------------
-- Table structure for company_settings
-- ----------------------------
DROP TABLE IF EXISTS `company_settings`;
CREATE TABLE `company_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_settings_company_id_foreign` (`company_id`),
  CONSTRAINT `company_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of company_settings
-- ----------------------------
BEGIN;
INSERT INTO `company_settings` VALUES (1, 'notification_email', 'info@crackerdigital.com', 1, '2019-12-27 16:38:50', '2019-12-27 16:38:50');
INSERT INTO `company_settings` VALUES (2, 'currency', '12', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (3, 'time_zone', 'UTC', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (4, 'language', 'en', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (5, 'carbon_date_format', 'd M Y', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (6, 'moment_date_format', 'DD MMM YYYY', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (7, 'fiscal_year', '3-2', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (8, 'invoice_auto_generate', 'YES', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (9, 'invoice_prefix', 'INV', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (10, 'estimate_prefix', 'EST', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (11, 'estimate_auto_generate', 'YES', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (12, 'payment_prefix', 'PAY', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (13, 'payment_auto_generate', 'YES', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (14, 'primary_text_color', '#5851D8', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (15, 'heading_text_color', '#595959', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (16, 'section_heading_text_color', '#040405', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (17, 'border_color', '#EAF1FB', 1, '2019-12-27 16:39:11', '2019-12-27 16:39:11');
INSERT INTO `company_settings` VALUES (18, 'body_text_color', '#595959', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (19, 'footer_text_color', '#595959', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (20, 'footer_total_color', '#5851D8', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (21, 'footer_bg_color', '#F9FBFF', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (22, 'date_text_color', '#A5ACC1', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (23, 'invoice_primary_color', '#5851D8', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (24, 'invoice_column_heading', '#55547A', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (25, 'invoice_field_label', '#55547A', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (26, 'invoice_field_value', '#040405', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (27, 'invoice_body_text', '#040405', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (28, 'invoice_description_text', '#595959', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
INSERT INTO `company_settings` VALUES (29, 'invoice_border_color', '#EAF1FB', 1, '2019-12-27 16:39:12', '2019-12-27 16:39:12');
COMMIT;

-- ----------------------------
-- Table structure for countries
-- ----------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phonecode` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `countries_id_index` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of countries
-- ----------------------------
BEGIN;
INSERT INTO `countries` VALUES (1, 'AF', 'Afghanistan', 93);
INSERT INTO `countries` VALUES (2, 'AL', 'Albania', 355);
INSERT INTO `countries` VALUES (3, 'DZ', 'Algeria', 213);
INSERT INTO `countries` VALUES (4, 'AS', 'American Samoa', 1684);
INSERT INTO `countries` VALUES (5, 'AD', 'Andorra', 376);
INSERT INTO `countries` VALUES (6, 'AO', 'Angola', 244);
INSERT INTO `countries` VALUES (7, 'AI', 'Anguilla', 1264);
INSERT INTO `countries` VALUES (8, 'AQ', 'Antarctica', 0);
INSERT INTO `countries` VALUES (9, 'AG', 'Antigua And Barbuda', 1268);
INSERT INTO `countries` VALUES (10, 'AR', 'Argentina', 54);
INSERT INTO `countries` VALUES (11, 'AM', 'Armenia', 374);
INSERT INTO `countries` VALUES (12, 'AW', 'Aruba', 297);
INSERT INTO `countries` VALUES (13, 'AU', 'Australia', 61);
INSERT INTO `countries` VALUES (14, 'AT', 'Austria', 43);
INSERT INTO `countries` VALUES (15, 'AZ', 'Azerbaijan', 994);
INSERT INTO `countries` VALUES (16, 'BS', 'Bahamas The', 1242);
INSERT INTO `countries` VALUES (17, 'BH', 'Bahrain', 973);
INSERT INTO `countries` VALUES (18, 'BD', 'Bangladesh', 880);
INSERT INTO `countries` VALUES (19, 'BB', 'Barbados', 1246);
INSERT INTO `countries` VALUES (20, 'BY', 'Belarus', 375);
INSERT INTO `countries` VALUES (21, 'BE', 'Belgium', 32);
INSERT INTO `countries` VALUES (22, 'BZ', 'Belize', 501);
INSERT INTO `countries` VALUES (23, 'BJ', 'Benin', 229);
INSERT INTO `countries` VALUES (24, 'BM', 'Bermuda', 1441);
INSERT INTO `countries` VALUES (25, 'BT', 'Bhutan', 975);
INSERT INTO `countries` VALUES (26, 'BO', 'Bolivia', 591);
INSERT INTO `countries` VALUES (27, 'BA', 'Bosnia and Herzegovina', 387);
INSERT INTO `countries` VALUES (28, 'BW', 'Botswana', 267);
INSERT INTO `countries` VALUES (29, 'BV', 'Bouvet Island', 0);
INSERT INTO `countries` VALUES (30, 'BR', 'Brazil', 55);
INSERT INTO `countries` VALUES (31, 'IO', 'British Indian Ocean Territory', 246);
INSERT INTO `countries` VALUES (32, 'BN', 'Brunei', 673);
INSERT INTO `countries` VALUES (33, 'BG', 'Bulgaria', 359);
INSERT INTO `countries` VALUES (34, 'BF', 'Burkina Faso', 226);
INSERT INTO `countries` VALUES (35, 'BI', 'Burundi', 257);
INSERT INTO `countries` VALUES (36, 'KH', 'Cambodia', 855);
INSERT INTO `countries` VALUES (37, 'CM', 'Cameroon', 237);
INSERT INTO `countries` VALUES (38, 'CA', 'Canada', 1);
INSERT INTO `countries` VALUES (39, 'CV', 'Cape Verde', 238);
INSERT INTO `countries` VALUES (40, 'KY', 'Cayman Islands', 1345);
INSERT INTO `countries` VALUES (41, 'CF', 'Central African Republic', 236);
INSERT INTO `countries` VALUES (42, 'TD', 'Chad', 235);
INSERT INTO `countries` VALUES (43, 'CL', 'Chile', 56);
INSERT INTO `countries` VALUES (44, 'CN', 'China', 86);
INSERT INTO `countries` VALUES (45, 'CX', 'Christmas Island', 61);
INSERT INTO `countries` VALUES (46, 'CC', 'Cocos (Keeling) Islands', 672);
INSERT INTO `countries` VALUES (47, 'CO', 'Colombia', 57);
INSERT INTO `countries` VALUES (48, 'KM', 'Comoros', 269);
INSERT INTO `countries` VALUES (49, 'CG', 'Congo', 242);
INSERT INTO `countries` VALUES (50, 'CD', 'Congo The Democratic Republic Of The', 242);
INSERT INTO `countries` VALUES (51, 'CK', 'Cook Islands', 682);
INSERT INTO `countries` VALUES (52, 'CR', 'Costa Rica', 506);
INSERT INTO `countries` VALUES (53, 'CI', 'Cote D Ivoire (Ivory Coast)', 225);
INSERT INTO `countries` VALUES (54, 'HR', 'Croatia (Hrvatska)', 385);
INSERT INTO `countries` VALUES (55, 'CU', 'Cuba', 53);
INSERT INTO `countries` VALUES (56, 'CY', 'Cyprus', 357);
INSERT INTO `countries` VALUES (57, 'CZ', 'Czech Republic', 420);
INSERT INTO `countries` VALUES (58, 'DK', 'Denmark', 45);
INSERT INTO `countries` VALUES (59, 'DJ', 'Djibouti', 253);
INSERT INTO `countries` VALUES (60, 'DM', 'Dominica', 1767);
INSERT INTO `countries` VALUES (61, 'DO', 'Dominican Republic', 1809);
INSERT INTO `countries` VALUES (62, 'TP', 'East Timor', 670);
INSERT INTO `countries` VALUES (63, 'EC', 'Ecuador', 593);
INSERT INTO `countries` VALUES (64, 'EG', 'Egypt', 20);
INSERT INTO `countries` VALUES (65, 'SV', 'El Salvador', 503);
INSERT INTO `countries` VALUES (66, 'GQ', 'Equatorial Guinea', 240);
INSERT INTO `countries` VALUES (67, 'ER', 'Eritrea', 291);
INSERT INTO `countries` VALUES (68, 'EE', 'Estonia', 372);
INSERT INTO `countries` VALUES (69, 'ET', 'Ethiopia', 251);
INSERT INTO `countries` VALUES (70, 'XA', 'External Territories of Australia', 61);
INSERT INTO `countries` VALUES (71, 'FK', 'Falkland Islands', 500);
INSERT INTO `countries` VALUES (72, 'FO', 'Faroe Islands', 298);
INSERT INTO `countries` VALUES (73, 'FJ', 'Fiji Islands', 679);
INSERT INTO `countries` VALUES (74, 'FI', 'Finland', 358);
INSERT INTO `countries` VALUES (75, 'FR', 'France', 33);
INSERT INTO `countries` VALUES (76, 'GF', 'French Guiana', 594);
INSERT INTO `countries` VALUES (77, 'PF', 'French Polynesia', 689);
INSERT INTO `countries` VALUES (78, 'TF', 'French Southern Territories', 0);
INSERT INTO `countries` VALUES (79, 'GA', 'Gabon', 241);
INSERT INTO `countries` VALUES (80, 'GM', 'Gambia The', 220);
INSERT INTO `countries` VALUES (81, 'GE', 'Georgia', 995);
INSERT INTO `countries` VALUES (82, 'DE', 'Germany', 49);
INSERT INTO `countries` VALUES (83, 'GH', 'Ghana', 233);
INSERT INTO `countries` VALUES (84, 'GI', 'Gibraltar', 350);
INSERT INTO `countries` VALUES (85, 'GR', 'Greece', 30);
INSERT INTO `countries` VALUES (86, 'GL', 'Greenland', 299);
INSERT INTO `countries` VALUES (87, 'GD', 'Grenada', 1473);
INSERT INTO `countries` VALUES (88, 'GP', 'Guadeloupe', 590);
INSERT INTO `countries` VALUES (89, 'GU', 'Guam', 1671);
INSERT INTO `countries` VALUES (90, 'GT', 'Guatemala', 502);
INSERT INTO `countries` VALUES (91, 'XU', 'Guernsey and Alderney', 44);
INSERT INTO `countries` VALUES (92, 'GN', 'Guinea', 224);
INSERT INTO `countries` VALUES (93, 'GW', 'Guinea-Bissau', 245);
INSERT INTO `countries` VALUES (94, 'GY', 'Guyana', 592);
INSERT INTO `countries` VALUES (95, 'HT', 'Haiti', 509);
INSERT INTO `countries` VALUES (96, 'HM', 'Heard and McDonald Islands', 0);
INSERT INTO `countries` VALUES (97, 'HN', 'Honduras', 504);
INSERT INTO `countries` VALUES (98, 'HK', 'Hong Kong S.A.R.', 852);
INSERT INTO `countries` VALUES (99, 'HU', 'Hungary', 36);
INSERT INTO `countries` VALUES (100, 'IS', 'Iceland', 354);
INSERT INTO `countries` VALUES (101, 'IN', 'India', 91);
INSERT INTO `countries` VALUES (102, 'ID', 'Indonesia', 62);
INSERT INTO `countries` VALUES (103, 'IR', 'Iran', 98);
INSERT INTO `countries` VALUES (104, 'IQ', 'Iraq', 964);
INSERT INTO `countries` VALUES (105, 'IE', 'Ireland', 353);
INSERT INTO `countries` VALUES (106, 'IL', 'Israel', 972);
INSERT INTO `countries` VALUES (107, 'IT', 'Italy', 39);
INSERT INTO `countries` VALUES (108, 'JM', 'Jamaica', 1876);
INSERT INTO `countries` VALUES (109, 'JP', 'Japan', 81);
INSERT INTO `countries` VALUES (110, 'XJ', 'Jersey', 44);
INSERT INTO `countries` VALUES (111, 'JO', 'Jordan', 962);
INSERT INTO `countries` VALUES (112, 'KZ', 'Kazakhstan', 7);
INSERT INTO `countries` VALUES (113, 'KE', 'Kenya', 254);
INSERT INTO `countries` VALUES (114, 'KI', 'Kiribati', 686);
INSERT INTO `countries` VALUES (115, 'KP', 'Korea North', 850);
INSERT INTO `countries` VALUES (116, 'KR', 'Korea South', 82);
INSERT INTO `countries` VALUES (117, 'KW', 'Kuwait', 965);
INSERT INTO `countries` VALUES (118, 'KG', 'Kyrgyzstan', 996);
INSERT INTO `countries` VALUES (119, 'LA', 'Laos', 856);
INSERT INTO `countries` VALUES (120, 'LV', 'Latvia', 371);
INSERT INTO `countries` VALUES (121, 'LB', 'Lebanon', 961);
INSERT INTO `countries` VALUES (122, 'LS', 'Lesotho', 266);
INSERT INTO `countries` VALUES (123, 'LR', 'Liberia', 231);
INSERT INTO `countries` VALUES (124, 'LY', 'Libya', 218);
INSERT INTO `countries` VALUES (125, 'LI', 'Liechtenstein', 423);
INSERT INTO `countries` VALUES (126, 'LT', 'Lithuania', 370);
INSERT INTO `countries` VALUES (127, 'LU', 'Luxembourg', 352);
INSERT INTO `countries` VALUES (128, 'MO', 'Macau S.A.R.', 853);
INSERT INTO `countries` VALUES (129, 'MK', 'Macedonia', 389);
INSERT INTO `countries` VALUES (130, 'MG', 'Madagascar', 261);
INSERT INTO `countries` VALUES (131, 'MW', 'Malawi', 265);
INSERT INTO `countries` VALUES (132, 'MY', 'Malaysia', 60);
INSERT INTO `countries` VALUES (133, 'MV', 'Maldives', 960);
INSERT INTO `countries` VALUES (134, 'ML', 'Mali', 223);
INSERT INTO `countries` VALUES (135, 'MT', 'Malta', 356);
INSERT INTO `countries` VALUES (136, 'XM', 'Man (Isle of)', 44);
INSERT INTO `countries` VALUES (137, 'MH', 'Marshall Islands', 692);
INSERT INTO `countries` VALUES (138, 'MQ', 'Martinique', 596);
INSERT INTO `countries` VALUES (139, 'MR', 'Mauritania', 222);
INSERT INTO `countries` VALUES (140, 'MU', 'Mauritius', 230);
INSERT INTO `countries` VALUES (141, 'YT', 'Mayotte', 269);
INSERT INTO `countries` VALUES (142, 'MX', 'Mexico', 52);
INSERT INTO `countries` VALUES (143, 'FM', 'Micronesia', 691);
INSERT INTO `countries` VALUES (144, 'MD', 'Moldova', 373);
INSERT INTO `countries` VALUES (145, 'MC', 'Monaco', 377);
INSERT INTO `countries` VALUES (146, 'MN', 'Mongolia', 976);
INSERT INTO `countries` VALUES (147, 'MS', 'Montserrat', 1664);
INSERT INTO `countries` VALUES (148, 'MA', 'Morocco', 212);
INSERT INTO `countries` VALUES (149, 'MZ', 'Mozambique', 258);
INSERT INTO `countries` VALUES (150, 'MM', 'Myanmar', 95);
INSERT INTO `countries` VALUES (151, 'NA', 'Namibia', 264);
INSERT INTO `countries` VALUES (152, 'NR', 'Nauru', 674);
INSERT INTO `countries` VALUES (153, 'NP', 'Nepal', 977);
INSERT INTO `countries` VALUES (154, 'AN', 'Netherlands Antilles', 599);
INSERT INTO `countries` VALUES (155, 'NL', 'Netherlands The', 31);
INSERT INTO `countries` VALUES (156, 'NC', 'New Caledonia', 687);
INSERT INTO `countries` VALUES (157, 'NZ', 'New Zealand', 64);
INSERT INTO `countries` VALUES (158, 'NI', 'Nicaragua', 505);
INSERT INTO `countries` VALUES (159, 'NE', 'Niger', 227);
INSERT INTO `countries` VALUES (160, 'NG', 'Nigeria', 234);
INSERT INTO `countries` VALUES (161, 'NU', 'Niue', 683);
INSERT INTO `countries` VALUES (162, 'NF', 'Norfolk Island', 672);
INSERT INTO `countries` VALUES (163, 'MP', 'Northern Mariana Islands', 1670);
INSERT INTO `countries` VALUES (164, 'NO', 'Norway', 47);
INSERT INTO `countries` VALUES (165, 'OM', 'Oman', 968);
INSERT INTO `countries` VALUES (166, 'PK', 'Pakistan', 92);
INSERT INTO `countries` VALUES (167, 'PW', 'Palau', 680);
INSERT INTO `countries` VALUES (168, 'PS', 'Palestinian Territory Occupied', 970);
INSERT INTO `countries` VALUES (169, 'PA', 'Panama', 507);
INSERT INTO `countries` VALUES (170, 'PG', 'Papua new Guinea', 675);
INSERT INTO `countries` VALUES (171, 'PY', 'Paraguay', 595);
INSERT INTO `countries` VALUES (172, 'PE', 'Peru', 51);
INSERT INTO `countries` VALUES (173, 'PH', 'Philippines', 63);
INSERT INTO `countries` VALUES (174, 'PN', 'Pitcairn Island', 0);
INSERT INTO `countries` VALUES (175, 'PL', 'Poland', 48);
INSERT INTO `countries` VALUES (176, 'PT', 'Portugal', 351);
INSERT INTO `countries` VALUES (177, 'PR', 'Puerto Rico', 1787);
INSERT INTO `countries` VALUES (178, 'QA', 'Qatar', 974);
INSERT INTO `countries` VALUES (179, 'RE', 'Reunion', 262);
INSERT INTO `countries` VALUES (180, 'RO', 'Romania', 40);
INSERT INTO `countries` VALUES (181, 'RU', 'Russia', 70);
INSERT INTO `countries` VALUES (182, 'RW', 'Rwanda', 250);
INSERT INTO `countries` VALUES (183, 'SH', 'Saint Helena', 290);
INSERT INTO `countries` VALUES (184, 'KN', 'Saint Kitts And Nevis', 1869);
INSERT INTO `countries` VALUES (185, 'LC', 'Saint Lucia', 1758);
INSERT INTO `countries` VALUES (186, 'PM', 'Saint Pierre and Miquelon', 508);
INSERT INTO `countries` VALUES (187, 'VC', 'Saint Vincent And The Grenadines', 1784);
INSERT INTO `countries` VALUES (188, 'WS', 'Samoa', 684);
INSERT INTO `countries` VALUES (189, 'SM', 'San Marino', 378);
INSERT INTO `countries` VALUES (190, 'ST', 'Sao Tome and Principe', 239);
INSERT INTO `countries` VALUES (191, 'SA', 'Saudi Arabia', 966);
INSERT INTO `countries` VALUES (192, 'SN', 'Senegal', 221);
INSERT INTO `countries` VALUES (193, 'RS', 'Serbia', 381);
INSERT INTO `countries` VALUES (194, 'SC', 'Seychelles', 248);
INSERT INTO `countries` VALUES (195, 'SL', 'Sierra Leone', 232);
INSERT INTO `countries` VALUES (196, 'SG', 'Singapore', 65);
INSERT INTO `countries` VALUES (197, 'SK', 'Slovakia', 421);
INSERT INTO `countries` VALUES (198, 'SI', 'Slovenia', 386);
INSERT INTO `countries` VALUES (199, 'XG', 'Smaller Territories of the UK', 44);
INSERT INTO `countries` VALUES (200, 'SB', 'Solomon Islands', 677);
INSERT INTO `countries` VALUES (201, 'SO', 'Somalia', 252);
INSERT INTO `countries` VALUES (202, 'ZA', 'South Africa', 27);
INSERT INTO `countries` VALUES (203, 'GS', 'South Georgia', 0);
INSERT INTO `countries` VALUES (204, 'SS', 'South Sudan', 211);
INSERT INTO `countries` VALUES (205, 'ES', 'Spain', 34);
INSERT INTO `countries` VALUES (206, 'LK', 'Sri Lanka', 94);
INSERT INTO `countries` VALUES (207, 'SD', 'Sudan', 249);
INSERT INTO `countries` VALUES (208, 'SR', 'Suriname', 597);
INSERT INTO `countries` VALUES (209, 'SJ', 'Svalbard And Jan Mayen Islands', 47);
INSERT INTO `countries` VALUES (210, 'SZ', 'Swaziland', 268);
INSERT INTO `countries` VALUES (211, 'SE', 'Sweden', 46);
INSERT INTO `countries` VALUES (212, 'CH', 'Switzerland', 41);
INSERT INTO `countries` VALUES (213, 'SY', 'Syria', 963);
INSERT INTO `countries` VALUES (214, 'TW', 'Taiwan', 886);
INSERT INTO `countries` VALUES (215, 'TJ', 'Tajikistan', 992);
INSERT INTO `countries` VALUES (216, 'TZ', 'Tanzania', 255);
INSERT INTO `countries` VALUES (217, 'TH', 'Thailand', 66);
INSERT INTO `countries` VALUES (218, 'TG', 'Togo', 228);
INSERT INTO `countries` VALUES (219, 'TK', 'Tokelau', 690);
INSERT INTO `countries` VALUES (220, 'TO', 'Tonga', 676);
INSERT INTO `countries` VALUES (221, 'TT', 'Trinidad And Tobago', 1868);
INSERT INTO `countries` VALUES (222, 'TN', 'Tunisia', 216);
INSERT INTO `countries` VALUES (223, 'TR', 'Turkey', 90);
INSERT INTO `countries` VALUES (224, 'TM', 'Turkmenistan', 7370);
INSERT INTO `countries` VALUES (225, 'TC', 'Turks And Caicos Islands', 1649);
INSERT INTO `countries` VALUES (226, 'TV', 'Tuvalu', 688);
INSERT INTO `countries` VALUES (227, 'UG', 'Uganda', 256);
INSERT INTO `countries` VALUES (228, 'UA', 'Ukraine', 380);
INSERT INTO `countries` VALUES (229, 'AE', 'United Arab Emirates', 971);
INSERT INTO `countries` VALUES (230, 'GB', 'United Kingdom', 44);
INSERT INTO `countries` VALUES (231, 'US', 'United States', 1);
INSERT INTO `countries` VALUES (232, 'UM', 'United States Minor Outlying Islands', 1);
INSERT INTO `countries` VALUES (233, 'UY', 'Uruguay', 598);
INSERT INTO `countries` VALUES (234, 'UZ', 'Uzbekistan', 998);
INSERT INTO `countries` VALUES (235, 'VU', 'Vanuatu', 678);
INSERT INTO `countries` VALUES (236, 'VA', 'Vatican City State (Holy See)', 39);
INSERT INTO `countries` VALUES (237, 'VE', 'Venezuela', 58);
INSERT INTO `countries` VALUES (238, 'VN', 'Vietnam', 84);
INSERT INTO `countries` VALUES (239, 'VG', 'Virgin Islands (British)', 1284);
INSERT INTO `countries` VALUES (240, 'VI', 'Virgin Islands (US)', 1340);
INSERT INTO `countries` VALUES (241, 'WF', 'Wallis And Futuna Islands', 681);
INSERT INTO `countries` VALUES (242, 'EH', 'Western Sahara', 212);
INSERT INTO `countries` VALUES (243, 'YE', 'Yemen', 967);
INSERT INTO `countries` VALUES (244, 'YU', 'Yugoslavia', 38);
INSERT INTO `countries` VALUES (245, 'ZM', 'Zambia', 260);
INSERT INTO `countries` VALUES (246, 'ZW', 'Zimbabwe', 263);
COMMIT;

-- ----------------------------
-- Table structure for currencies
-- ----------------------------
DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `symbol` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `precision` int(11) NOT NULL,
  `thousand_separator` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `decimal_separator` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `swap_currency_symbol` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of currencies
-- ----------------------------
BEGIN;
INSERT INTO `currencies` VALUES (1, 'US Dollar', 'USD', '$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (2, 'British Pound', 'GBP', '£', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (3, 'Euro', 'EUR', '€', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (4, 'South African Rand', 'ZAR', 'R', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (5, 'Danish Krone', 'DKK', 'kr', 2, '.', ',', 1, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (6, 'Israeli Shekel', 'ILS', 'NIS ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (7, 'Swedish Krona', 'SEK', 'kr', 2, '.', ',', 1, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (8, 'Kenyan Shilling', 'KES', 'KSh ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (9, 'Kuwaiti Dinar', 'KWD', 'KWD ', 3, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (10, 'Canadian Dollar', 'CAD', 'C$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (11, 'Philippine Peso', 'PHP', 'P ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (12, 'Indian Rupee', 'INR', '₹', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (13, 'Australian Dollar', 'AUD', '$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (14, 'Singapore Dollar', 'SGD', 'S$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (15, 'Norske Kroner', 'NOK', 'kr', 2, '.', ',', 1, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (16, 'New Zealand Dollar', 'NZD', '$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (17, 'Vietnamese Dong', 'VND', '₫', 0, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (18, 'Swiss Franc', 'CHF', 'Fr.', 2, '\'', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (19, 'Guatemalan Quetzal', 'GTQ', 'Q', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (20, 'Malaysian Ringgit', 'MYR', 'RM', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (21, 'Brazilian Real', 'BRL', 'R$', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (22, 'Thai Baht', 'THB', '฿', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (23, 'Nigerian Naira', 'NGN', '₦', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (24, 'Argentine Peso', 'ARS', '$', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (25, 'Bangladeshi Taka', 'BDT', 'Tk', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (26, 'United Arab Emirates Dirham', 'AED', 'DH ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (27, 'Hong Kong Dollar', 'HKD', 'HK$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (28, 'Indonesian Rupiah', 'IDR', 'Rp', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (29, 'Mexican Peso', 'MXN', '$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (30, 'Egyptian Pound', 'EGP', 'E£', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (31, 'Colombian Peso', 'COP', '$', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (32, 'West African Franc', 'XOF', 'CFA ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (33, 'Chinese Renminbi', 'CNY', 'RMB ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (34, 'Rwandan Franc', 'RWF', 'RF ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (35, 'Tanzanian Shilling', 'TZS', 'TSh ', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (36, 'Netherlands Antillean Guilder', 'ANG', 'NAƒ', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (37, 'Trinidad and Tobago Dollar', 'TTD', 'TT$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (38, 'East Caribbean Dollar', 'XCD', 'EC$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (39, 'Ghanaian Cedi', 'GHS', '‎GH₵', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (40, 'Bulgarian Lev', 'BGN', 'Лв.', 2, ' ', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (41, 'Aruban Florin', 'AWG', 'Afl. ', 2, ' ', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (42, 'Turkish Lira', 'TRY', 'TL ', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (43, 'Romanian New Leu', 'RON', 'RON', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (44, 'Croatian Kuna', 'HRK', 'kn', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (45, 'Saudi Riyal', 'SAR', '‎SِAR', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (46, 'Japanese Yen', 'JPY', '¥', 0, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (47, 'Maldivian Rufiyaa', 'MVR', 'Rf', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (48, 'Costa Rican Colón', 'CRC', '₡', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (49, 'Pakistani Rupee', 'PKR', 'Rs ', 0, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (50, 'Polish Zloty', 'PLN', 'zł', 2, ' ', ',', 1, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (51, 'Sri Lankan Rupee', 'LKR', 'LKR', 2, ',', '.', 1, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (52, 'Czech Koruna', 'CZK', 'Kč', 2, ' ', ',', 1, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (53, 'Uruguayan Peso', 'UYU', '$', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (54, 'Namibian Dollar', 'NAD', '$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (55, 'Tunisian Dinar', 'TND', '‎د.ت', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (56, 'Russian Ruble', 'RUB', '₽', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (57, 'Mozambican Metical', 'MZN', 'MT', 2, '.', ',', 1, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (58, 'Omani Rial', 'OMR', 'ر.ع.', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (59, 'Ukrainian Hryvnia', 'UAH', '₴', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (60, 'Macanese Pataca', 'MOP', 'MOP$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (61, 'Taiwan New Dollar', 'TWD', 'NT$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (62, 'Dominican Peso', 'DOP', 'RD$', 2, ',', '.', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (63, 'Chilean Peso', 'CLP', '$', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `currencies` VALUES (64, 'Serbian Dinar', 'RSD', 'RSD', 2, '.', ',', 0, '2019-12-27 16:36:20', '2019-12-27 16:36:20');
COMMIT;

-- ----------------------------
-- Table structure for estimate_items
-- ----------------------------
DROP TABLE IF EXISTS `estimate_items`;
CREATE TABLE `estimate_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `discount_val` bigint(20) unsigned DEFAULT NULL,
  `price` bigint(20) unsigned NOT NULL,
  `tax` bigint(20) unsigned NOT NULL,
  `total` bigint(20) unsigned NOT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `estimate_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estimate_items_item_id_foreign` (`item_id`),
  KEY `estimate_items_estimate_id_foreign` (`estimate_id`),
  KEY `estimate_items_company_id_foreign` (`company_id`),
  CONSTRAINT `estimate_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estimate_items_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estimate_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for estimate_templates
-- ----------------------------
DROP TABLE IF EXISTS `estimate_templates`;
CREATE TABLE `estimate_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `view` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of estimate_templates
-- ----------------------------
BEGIN;
INSERT INTO `estimate_templates` VALUES (1, 'Template 1', 'estimate1', '/assets/img/PDF/Template1.png', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `estimate_templates` VALUES (2, 'Template 2', 'estimate2', '/assets/img/PDF/Template2.png', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `estimate_templates` VALUES (3, 'Template 3', 'estimate3', '/assets/img/PDF/Template3.png', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
COMMIT;

-- ----------------------------
-- Table structure for estimates
-- ----------------------------
DROP TABLE IF EXISTS `estimates`;
CREATE TABLE `estimates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `estimate_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `estimate_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reference_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tax_per_item` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `discount_per_item` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `discount_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount_val` bigint(20) unsigned DEFAULT NULL,
  `sub_total` bigint(20) unsigned NOT NULL,
  `total` bigint(20) unsigned NOT NULL,
  `tax` bigint(20) unsigned NOT NULL,
  `unique_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `estimate_template_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estimates_user_id_foreign` (`user_id`),
  KEY `estimates_estimate_template_id_foreign` (`estimate_template_id`),
  KEY `estimates_company_id_foreign` (`company_id`),
  CONSTRAINT `estimates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estimates_estimate_template_id_foreign` FOREIGN KEY (`estimate_template_id`) REFERENCES `estimate_templates` (`id`),
  CONSTRAINT `estimates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for expense_categories
-- ----------------------------
DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_categories_company_id_foreign` (`company_id`),
  CONSTRAINT `expense_categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for expenses
-- ----------------------------
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expense_date` date NOT NULL,
  `attachment_receipt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` bigint(20) unsigned NOT NULL,
  `notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expense_category_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  KEY `expenses_company_id_foreign` (`company_id`),
  CONSTRAINT `expenses_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for invoice_items
-- ----------------------------
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` bigint(20) unsigned NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `discount_val` bigint(20) unsigned NOT NULL,
  `tax` bigint(20) unsigned NOT NULL,
  `total` bigint(20) unsigned NOT NULL,
  `invoice_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `invoice_items_item_id_foreign` (`item_id`),
  KEY `invoice_items_company_id_foreign` (`company_id`),
  CONSTRAINT `invoice_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for invoice_templates
-- ----------------------------
DROP TABLE IF EXISTS `invoice_templates`;
CREATE TABLE `invoice_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `view` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of invoice_templates
-- ----------------------------
BEGIN;
INSERT INTO `invoice_templates` VALUES (1, 'Template 1', 'invoice1', '/assets/img/PDF/Template1.png', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `invoice_templates` VALUES (2, ' Template 2', 'invoice2', '/assets/img/PDF/Template2.png', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `invoice_templates` VALUES (3, 'Template 3', 'invoice3', '/assets/img/PDF/Template3.png', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
COMMIT;

-- ----------------------------
-- Table structure for invoices
-- ----------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reference_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `paid_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tax_per_item` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `discount_per_item` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `discount_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `discount_val` bigint(20) unsigned DEFAULT NULL,
  `sub_total` bigint(20) unsigned NOT NULL,
  `total` bigint(20) unsigned NOT NULL,
  `tax` bigint(20) unsigned NOT NULL,
  `due_amount` bigint(20) unsigned NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  `unique_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoice_template_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoices_invoice_template_id_foreign` (`invoice_template_id`),
  KEY `invoices_user_id_foreign` (`user_id`),
  KEY `invoices_company_id_foreign` (`company_id`),
  CONSTRAINT `invoices_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_invoice_template_id_foreign` FOREIGN KEY (`invoice_template_id`) REFERENCES `invoice_templates` (`id`),
  CONSTRAINT `invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for items
-- ----------------------------
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price` bigint(20) unsigned NOT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_company_id_foreign` (`company_id`),
  CONSTRAINT `items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for media
-- ----------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `collection_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `manipulations` text COLLATE utf8_unicode_ci NOT NULL,
  `custom_properties` text COLLATE utf8_unicode_ci NOT NULL,
  `responsive_images` text COLLATE utf8_unicode_ci NOT NULL,
  `order_column` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of migrations
-- ----------------------------
BEGIN;
INSERT INTO `migrations` VALUES (1, '2014_10_11_071840_create_companies_table', 1);
INSERT INTO `migrations` VALUES (2, '2014_10_11_125754_create_currencies_table', 1);
INSERT INTO `migrations` VALUES (3, '2014_10_12_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (4, '2014_10_12_100000_create_password_resets_table', 1);
INSERT INTO `migrations` VALUES (5, '2016_05_13_060834_create_settings_table', 1);
INSERT INTO `migrations` VALUES (6, '2017_04_11_081227_create_items_table', 1);
INSERT INTO `migrations` VALUES (7, '2017_04_11_140447_create_invoice_templates_table', 1);
INSERT INTO `migrations` VALUES (8, '2017_04_12_090759_create_invoices_table', 1);
INSERT INTO `migrations` VALUES (9, '2017_04_12_091015_create_invoice_items_table', 1);
INSERT INTO `migrations` VALUES (10, '2017_05_04_141701_create_estimate_templates_table', 1);
INSERT INTO `migrations` VALUES (11, '2017_05_05_055609_create_estimates_table', 1);
INSERT INTO `migrations` VALUES (12, '2017_05_05_073927_create_notifications_table', 1);
INSERT INTO `migrations` VALUES (13, '2017_05_06_173745_create_countries_table', 1);
INSERT INTO `migrations` VALUES (14, '2017_10_02_123501_create_estimate_items_table', 1);
INSERT INTO `migrations` VALUES (15, '2017_12_02_204902_create_permission_tables', 1);
INSERT INTO `migrations` VALUES (16, '2018_11_02_133825_create_ expense_categories_table', 1);
INSERT INTO `migrations` VALUES (17, '2018_11_02_133956_create_expenses_table', 1);
INSERT INTO `migrations` VALUES (18, '2019_08_30_072639_create_addresses_table', 1);
INSERT INTO `migrations` VALUES (19, '2019_09_03_135234_create_payments_table', 1);
INSERT INTO `migrations` VALUES (20, '2019_09_14_120124_create_media_table', 1);
INSERT INTO `migrations` VALUES (21, '2019_09_21_052540_create_tax_types_table', 1);
INSERT INTO `migrations` VALUES (22, '2019_09_21_052548_create_taxes_table', 1);
INSERT INTO `migrations` VALUES (23, '2019_09_26_145012_create_company_settings_table', 1);
INSERT INTO `migrations` VALUES (24, '2016_06_01_000001_create_oauth_auth_codes_table', 2);
INSERT INTO `migrations` VALUES (25, '2016_06_01_000002_create_oauth_access_tokens_table', 2);
INSERT INTO `migrations` VALUES (26, '2016_06_01_000003_create_oauth_refresh_tokens_table', 2);
INSERT INTO `migrations` VALUES (27, '2016_06_01_000004_create_oauth_clients_table', 2);
INSERT INTO `migrations` VALUES (28, '2016_06_01_000005_create_oauth_personal_access_clients_table', 2);
COMMIT;

-- ----------------------------
-- Table structure for model_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for model_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` int(10) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for oauth_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `oauth_access_tokens`;
CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of oauth_access_tokens
-- ----------------------------
BEGIN;
INSERT INTO `oauth_access_tokens` VALUES ('5fff406ec7cef18e7dc286ab465cb9217c0646f8d722ef257939ef68253528003b65772fb2c6376c', 1, 2, NULL, '[]', 0, '2019-12-27 16:43:23', '2019-12-27 16:43:23', '2020-12-27 16:43:23');
INSERT INTO `oauth_access_tokens` VALUES ('788e0e8b89ac0fab0ea0a94d1c77a2ff0a4df160a2249d8ccfc8a1d26408158b36b8d4ede0aec1da', 1, 1, 'password', '[]', 0, '2019-12-27 16:39:23', '2019-12-27 16:39:23', '2020-12-27 16:39:23');
INSERT INTO `oauth_access_tokens` VALUES ('88f8f66c7eff24d34bbfee5ed3361493704d01d79d87a6e784fe5f1ac7f02b7a52c9e3182b73e323', 1, 2, NULL, '[]', 0, '2019-12-27 16:42:05', '2019-12-27 16:42:05', '2020-12-27 16:42:05');
COMMIT;

-- ----------------------------
-- Table structure for oauth_auth_codes
-- ----------------------------
DROP TABLE IF EXISTS `oauth_auth_codes`;
CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `scopes` text COLLATE utf8_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for oauth_clients
-- ----------------------------
DROP TABLE IF EXISTS `oauth_clients`;
CREATE TABLE `oauth_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of oauth_clients
-- ----------------------------
BEGIN;
INSERT INTO `oauth_clients` VALUES (1, NULL, 'Crater Personal Access Client', 'E4sQaoX3xB1Q0CE3ok7U2wKgRsLRE7aHSQCpK2HZ', 'http://localhost', 1, 0, 0, '2019-12-27 16:39:19', '2019-12-27 16:39:19');
INSERT INTO `oauth_clients` VALUES (2, NULL, 'Crater Password Grant Client', 'iZT0eiWEmTGklbul48bKDjqfJSwINV8jyPfjoBf2', 'http://localhost', 0, 1, 0, '2019-12-27 16:39:19', '2019-12-27 16:39:19');
COMMIT;

-- ----------------------------
-- Table structure for oauth_personal_access_clients
-- ----------------------------
DROP TABLE IF EXISTS `oauth_personal_access_clients`;
CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_personal_access_clients_client_id_index` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of oauth_personal_access_clients
-- ----------------------------
BEGIN;
INSERT INTO `oauth_personal_access_clients` VALUES (1, 1, '2019-12-27 16:39:19', '2019-12-27 16:39:19');
COMMIT;

-- ----------------------------
-- Table structure for oauth_refresh_tokens
-- ----------------------------
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of oauth_refresh_tokens
-- ----------------------------
BEGIN;
INSERT INTO `oauth_refresh_tokens` VALUES ('b78c0d1c3fced8c324fce6f0d964bb96c8887fc1d4bf6090a68fe9f11345e931f2338cc4e9ebcc2c', '88f8f66c7eff24d34bbfee5ed3361493704d01d79d87a6e784fe5f1ac7f02b7a52c9e3182b73e323', 0, '2020-12-27 16:42:06');
INSERT INTO `oauth_refresh_tokens` VALUES ('ce14be404cb2865225733098e46f5e4c24027386ed56f86b70d6a19440cb5b4a5fd7029d7ff48e23', '5fff406ec7cef18e7dc286ab465cb9217c0646f8d722ef257939ef68253528003b65772fb2c6376c', 0, '2020-12-27 16:43:23');
COMMIT;

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payments
-- ----------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_mode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_date` date NOT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `amount` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `invoice_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_user_id_foreign` (`user_id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_company_id_foreign` (`company_id`),
  CONSTRAINT `payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for role_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of roles
-- ----------------------------
BEGIN;
INSERT INTO `roles` VALUES (1, 'admin', 'api', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `roles` VALUES (2, 'contact', 'api', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
INSERT INTO `roles` VALUES (3, 'staff', 'api', '2019-12-27 16:36:20', '2019-12-27 16:36:20');
COMMIT;

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of settings
-- ----------------------------
BEGIN;
INSERT INTO `settings` VALUES (1, 'profile_complete', 'COMPLETED', '2019-12-27 16:36:20', '2019-12-27 16:39:11');
INSERT INTO `settings` VALUES (2, 'version', '2.1.0', '2019-12-27 16:39:12', '2019-12-27 16:39:12');
COMMIT;

-- ----------------------------
-- Table structure for tax_types
-- ----------------------------
DROP TABLE IF EXISTS `tax_types`;
CREATE TABLE `tax_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(5,2) NOT NULL,
  `compound_tax` tinyint(4) NOT NULL DEFAULT '0',
  `collective_tax` tinyint(4) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tax_types_company_id_foreign` (`company_id`),
  CONSTRAINT `tax_types_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for taxes
-- ----------------------------
DROP TABLE IF EXISTS `taxes`;
CREATE TABLE `taxes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tax_type_id` int(10) unsigned NOT NULL,
  `invoice_id` int(10) unsigned DEFAULT NULL,
  `estimate_id` int(10) unsigned DEFAULT NULL,
  `invoice_item_id` int(10) unsigned DEFAULT NULL,
  `estimate_item_id` int(10) unsigned DEFAULT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` bigint(20) unsigned NOT NULL,
  `percent` decimal(5,2) NOT NULL,
  `compound_tax` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taxes_tax_type_id_foreign` (`tax_type_id`),
  KEY `taxes_invoice_id_foreign` (`invoice_id`),
  KEY `taxes_estimate_id_foreign` (`estimate_id`),
  KEY `taxes_invoice_item_id_foreign` (`invoice_item_id`),
  KEY `taxes_estimate_item_id_foreign` (`estimate_item_id`),
  KEY `taxes_item_id_foreign` (`item_id`),
  KEY `taxes_company_id_foreign` (`company_id`),
  CONSTRAINT `taxes_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `taxes_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `taxes_estimate_item_id_foreign` FOREIGN KEY (`estimate_item_id`) REFERENCES `estimate_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `taxes_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `taxes_invoice_item_id_foreign` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `taxes_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `taxes_tax_type_id_foreign` FOREIGN KEY (`tax_type_id`) REFERENCES `tax_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `github_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enable_portal` tinyint(1) DEFAULT NULL,
  `currency_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_currency_id_foreign` (`currency_id`),
  KEY `users_company_id_foreign` (`company_id`),
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of users
-- ----------------------------
BEGIN;
INSERT INTO `users` VALUES (1, 'Cracker', 'info@crackerdigital.com', NULL, '$2y$10$1FSgBrnsCWIhHhzQN/kOxOVidHSRlzMeX10KwlIM2XtIMlgwAYjD6', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2019-12-27 16:36:20', '2019-12-27 16:38:50');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
