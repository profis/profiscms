ALTER TABLE `pc_config` ADD `site` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ckey` ;







-- Table structure for table `pc_forms`
-- 

CREATE TABLE `pc_forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `form_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  `ip` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_forms`
-- 


-- 
-- Dumping data for table `pc_variables`
-- 

INSERT INTO `pc_variables` (`vkey`, `controller`, `site`, `ln`, `value`) VALUES
('form_submitted_field_name', 'forms', 0, 'en', '%s:'),
('form_submitted_field_name', 'forms', 0, 'lt', '%s:'),
('form_submitted_field_name', 'forms', 0, 'ru', '%s:'),
('form_submitted_file', 'forms', 0, 'en', 'File attachment: %s'),
('form_submitted_file', 'forms', 0, 'lt', 'Pridėtas failas: %s'),
('form_submitted_file', 'forms', 0, 'ru', 'Приложенный файл: %s'),
('form_submitted_heading', 'forms', 0, 'en', 'Hello!'),
('form_submitted_heading', 'forms', 0, 'lt', 'Sveiki!'),
('form_submitted_heading', 'forms', 0, 'ru', 'Здравствуйте!'),
('form_submitted_subject', 'forms', 0, 'en', 'Form has been submitted (%s)'),
('form_submitted_subject', 'forms', 0, 'lt', 'Pateikta forma (%s)'),
('form_submitted_subject', 'forms', 0, 'ru', 'Заполнена форма (%s)'),
('form_submitted_text', 'forms', 0, 'en', 'The form “%s” on page “%s” has been submitted. Below are the contents.'),
('form_submitted_text', 'forms', 0, 'lt', 'Užpildyta tinklapyje „%2$s“ esanti forma „%1$s“. Žemiau – pateiktas jos turinys.'),
('form_submitted_text', 'forms', 0, 'ru', 'На странице «%s» заполнена форма «%s». Ниже – её содержимое.'),
('form_field_required', 'forms', 0, 'en', 'This field is required.'),
('form_field_required', 'forms', 0, 'lt', 'Šis laukas privalomas.'),
('form_field_required', 'forms', 0, 'ru', 'Это поле обязательное.'),
('form_file_too_big', 'forms', 0, 'en', 'The file you chose to upload is too big.'),
('form_file_too_big', 'forms', 0, 'lt', 'Failas, kurį pasirinkote įkelti, yra per didelis.'),
('form_file_too_big', 'forms', 0, 'ru', 'Файл, вами выбраный для закачки, слишком большой.'),
('form_file_upload_error', 'forms', 0, 'en', 'The file was not uploaded successfully. Please try again or concact the website administrator.'),
('form_file_upload_error', 'forms', 0, 'lt', 'Failo įkelti nepavyko. Pabandykite dar kartą arba susisiekite su svetainės administratoriumi.'),
('form_file_upload_error', 'forms', 0, 'ru', 'Файл незагружен. Попробуйте еще раз или свяжитесь с администратором сайта.');