-- ============================================================
-- Проєкт «МійДім ОСББ»
-- Демонстраційні початкові дані (seed.sql)
-- Пароль для admin@mydom.ua: admin123
-- Пароль для мешканців/персоналу: 123456
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Будинок
INSERT INTO `buildings` (`id`, `name`, `address`, `edrpou`, `iban`, `bank_name`, `created_at`)
VALUES (1, 'ОСББ «Затишний Дім»', 'м. Київ, вул. Героїв Дніпра, 42', '38947211', 'UA213223130000026007233566123', 'АТ «Укрексімбанк»', NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. Користувачі
-- Хеш для 'admin123': $2y$10$wSD0t18X7/gp4bGF.DfyJ.SpXKMthaEfkbBF6NfUON9zCd3EpFveG
-- Хеш для '123456':   $2y$10$MjfkIRcm8pZ34OXh1OPFBe75Jm06a/IaibXWTIzkcDy4eMnxFp6gW
INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `full_name`, `phone`, `telegram_token`, `is_active`, `created_at`) VALUES
(1, 'admin@mydom.ua', '$2y$10$wSD0t18X7/gp4bGF.DfyJ.SpXKMthaEfkbBF6NfUON9zCd3EpFveG', 'admin', 'Коваленко Сергій Васильович (Голова)', '+380501112233', 'tg_token_admin_001', 1, NOW()),
(2, 'accountant@mydom.ua', '$2y$10$MjfkIRcm8pZ34OXh1OPFBe75Jm06a/IaibXWTIzkcDy4eMnxFp6gW', 'accountant', 'Мельник Тетяна Петрівна (Бухгалтер)', '+380502223344', 'tg_token_acc_002', 1, NOW()),
(3, 'master@mydom.ua', '$2y$10$MjfkIRcm8pZ34OXh1OPFBe75Jm06a/IaibXWTIzkcDy4eMnxFp6gW', 'master', 'Шевченко Богдан Олегович (Майстер)', '+380503334455', 'tg_token_master_003', 1, NOW()),
(4, 'ivan@test.ua', '$2y$10$MjfkIRcm8pZ34OXh1OPFBe75Jm06a/IaibXWTIzkcDy4eMnxFp6gW', 'resident', 'Іванченко Іван Іванович', '+380504445566', 'tg_token_ivan_004', 1, NOW()),
(5, 'olena@test.ua', '$2y$10$MjfkIRcm8pZ34OXh1OPFBe75Jm06a/IaibXWTIzkcDy4eMnxFp6gW', 'resident', 'Ковальчук Олена Вікторівна', '+380505556677', 'tg_token_olena_005', 1, NOW()),
(6, 'petro@test.ua', '$2y$10$MjfkIRcm8pZ34OXh1OPFBe75Jm06a/IaibXWTIzkcDy4eMnxFp6gW', 'resident', 'Бондар Петро Олексійович', '+380506667788', 'tg_token_petro_006', 1, NOW());

-- 3. Квартири
INSERT INTO `apartments` (`id`, `building_id`, `number`, `entrance`, `floor`, `area_m2`, `rooms`, `residents_count`, `account_number`, `notes`) VALUES
(1, 1, 1, 1, 1, 52.40, 2, 2, 'OSBB-0001', 'Під’їзд 1'),
(2, 1, 2, 1, 1, 44.10, 1, 1, 'OSBB-0002', 'Під’їзд 1'),
(3, 1, 3, 1, 2, 68.90, 3, 3, 'OSBB-0003', 'Під’їзд 1'),
(4, 1, 4, 1, 2, 54.00, 2, 2, 'OSBB-0004', 'Під’їзд 1'),
(5, 1, 5, 1, 3, 72.50, 3, 4, 'OSBB-0005', 'Під’їзд 1'),
(6, 1, 6, 1, 3, 43.80, 1, 2, 'OSBB-0006', 'Під’їзд 1'),
(7, 1, 7, 2, 1, 51.00, 2, 2, 'OSBB-0007', 'Під’їзд 2'),
(8, 1, 8, 2, 1, 42.50, 1, 1, 'OSBB-0008', 'Під’їзд 2'),
(9, 1, 9, 2, 2, 67.20, 3, 3, 'OSBB-0009', 'Під’їзд 2'),
(10, 1, 10, 2, 2, 55.30, 2, 2, 'OSBB-0010', 'Під’їзд 2'),
(11, 1, 11, 2, 3, 73.00, 3, 3, 'OSBB-0011', 'Під’їзд 2'),
(12, 1, 12, 2, 3, 45.00, 1, 1, 'OSBB-0012', 'Під’їзд 2');

-- 4. Прив'язка мешканців до квартир
INSERT INTO `residents` (`id`, `user_id`, `apartment_id`, `is_owner`, `ownership_share`, `is_primary_contact`) VALUES
(1, 4, 1, 1, 100.00, 1), -- Іван -> Кв. 1
(2, 5, 2, 1, 100.00, 1), -- Олена -> Кв. 2
(3, 6, 3, 1, 100.00, 1); -- Петро -> Кв. 3

-- 5. Типи лічильників
INSERT INTO `meter_types` (`id`, `code`, `name`, `unit`, `icon`, `default_tariff`) VALUES
(1, 'water_cold', 'Холодне водопостачання', 'м³', 'droplet', 30.38),
(2, 'water_hot', 'Гаряче водопостачання', 'м³', 'flame', 97.89),
(3, 'electricity', 'Електроенергія', 'кВт·год', 'zap', 4.32),
(4, 'heating', 'Опалення', 'Гкал', 'thermometer', 1654.41);

-- 6. Лічильники квартир
INSERT INTO `apartment_meters` (`id`, `apartment_id`, `meter_type_id`, `serial_number`, `initial_reading`, `installed_at`, `next_verification_date`) VALUES
(1, 1, 1, 'KV-CW-1001', 124.500, '2022-01-15', '2026-01-15'),
(2, 1, 2, 'KV-HW-1002', 45.200, '2022-01-15', '2026-01-15'),
(3, 1, 3, 'KV-EL-1003', 1450.000, '2022-01-15', '2028-01-15'),
(4, 2, 1, 'KV-CW-2001', 89.100, '2022-02-10', '2026-02-10'),
(5, 2, 3, 'KV-EL-2002', 980.000, '2022-02-10', '2028-02-10');

-- 7. Показники лічильників (минулий та поточний місяць)
INSERT INTO `meter_readings` (`id`, `apartment_meter_id`, `reading_value`, `consumption`, `reading_date`, `submitted_by`, `source`) VALUES
(1, 1, 128.500, 4.000, '2026-10-25', 4, 'web'),
(2, 1, 133.000, 4.500, '2026-11-20', 4, 'telegram'),
(3, 2, 47.000, 1.800, '2026-10-25', 4, 'web'),
(4, 2, 49.000, 2.000, '2026-11-20', 4, 'telegram'),
(5, 3, 1580.000, 130.000, '2026-10-25', 4, 'web'),
(6, 3, 1720.000, 140.000, '2026-11-20', 4, 'telegram');

-- 8. Тарифи
INSERT INTO `tariffs` (`id`, `service_name`, `service_code`, `rate`, `unit`, `description`, `valid_from`, `is_active`) VALUES
(1, 'Утримання будинку та прибудинкової території', 'maintenance', 8.50, 'м²', 'Прибирання, обслуговування ліфтів, освітлення під’їздів', '2026-01-01', 1),
(2, 'Холодна вода та водовідведення', 'water_cold', 30.38, 'м³', 'Постачання КП Водоканал', '2026-01-01', 1),
(3, 'Гаряче водопостачання', 'water_hot', 97.89, 'м³', 'Підігрів та постачання', '2026-01-01', 1),
(4, 'Електроенергія', 'electricity', 4.32, 'кВт·год', 'Згідно з єдиним тарифом', '2026-01-01', 1),
(5, 'Резервний фонд (капремонт)', 'reserve_fund', 2.00, 'м²', 'Накопичувальний фонд ремонту покрівлі та мереж', '2026-01-01', 1);

-- 9. Рахунки
INSERT INTO `bills` (`id`, `apartment_id`, `bill_number`, `billing_year`, `billing_month`, `maintenance_amount`, `meters_amount`, `previous_debt`, `total_amount`, `paid_amount`, `status`, `due_date`) VALUES
(1, 1, 'BILL-2026-10-0001', 2026, 10, 550.20, 874.15, 0.00, 1424.35, 1424.35, 'paid', '2026-11-10'),
(2, 1, 'BILL-2026-11-0001', 2026, 11, 550.20, 937.29, 0.00, 1487.49, 0.00, 'unpaid', '2026-12-10'),
(3, 2, 'BILL-2026-11-0002', 2026, 11, 463.05, 412.30, 120.00, 995.35, 0.00, 'unpaid', '2026-12-10');

-- 10. Позиції рахунку
INSERT INTO `bill_items` (`bill_id`, `service_name`, `tariff_rate`, `unit`, `quantity`, `total_amount`) VALUES
(2, 'Утримання будинку (52.4 м²)', 8.50, 'м²', 52.400, 445.40),
(2, 'Резервний фонд (52.4 м²)', 2.00, 'м²', 52.400, 104.80),
(2, 'Холодне водопостачання', 30.38, 'м³', 4.500, 136.71),
(2, 'Гаряче водопостачання', 97.89, 'м³', 2.000, 195.78),
(2, 'Електроенергія', 4.32, 'кВт·год', 140.000, 604.80);

-- 11. Платежі
INSERT INTO `payments` (`id`, `bill_id`, `apartment_id`, `user_id`, `amount`, `payment_method`, `transaction_ref`, `notes`, `status`, `payment_date`) VALUES
(1, 1, 1, 4, 1424.35, 'qr_bank', 'TXN-9842103984', 'Оплата ЖКП за жовтень 2026', 'confirmed', '2026-11-05 14:20:00');

-- 12. Заявки на ремонт
INSERT INTO `requests` (`id`, `apartment_id`, `user_id`, `category`, `title`, `description`, `priority`, `status`, `assigned_to`, `resolution`) VALUES
(1, 1, 4, 'plumbing', 'Капає кран стояка у підвалі', 'Біля стояка першого під’їзду помітно вологу, потрібна заміна прокладки.', 'normal', 'in_progress', 3, 'Замовлено нову прокладку, заплановано заміну на п’ятницю.'),
(2, 2, 5, 'electrical', 'Не горить ліхтар над другим під’їздом', 'Ввечері темно, можливо перегоріла лампа чи датчик руху.', 'low', 'done', 3, 'Замінено LED-лампу 20W, датчик руху протестовано.');

-- 13. Новини
INSERT INTO `news` (`id`, `author_id`, `title`, `body`, `type`, `is_pinned`, `is_published`, `created_at`) VALUES
(1, 1, 'Планова промивка системи опалення', 'Шановні мешканці! У вівторок з 09:00 до 16:00 буде проводитись гідравлічне випробування системи опалення. Просимо перевірити крани на радіаторах.', 'warning', 1, 1, NOW()),
(2, 1, 'Встановлення нових поштових скриньок', 'У 1-му та 2-му під’їздах завершено монтаж нових секційних поштових скриньок. Ключі можна отримати у чергового або в голови ОСББ.', 'info', 0, 1, NOW());

-- 14. Опитування
INSERT INTO `polls` (`id`, `created_by`, `title`, `description`, `is_active`, `expires_at`) VALUES
(1, 1, 'Встановлення шлагбауму на в’їзді у двір', 'Пропонується встановити автоматичний шлагбаум з відкриттям за дзвінком/додатком для захисту від сторонніх авто.', 1, '2026-12-31');

INSERT INTO `poll_options` (`id`, `poll_id`, `option_text`) VALUES
(1, 1, 'Так, підтримую (за встановлення)'),
(2, 1, 'Ні, проти (зайві витрати)'),
(3, 1, 'Підтримую лише за умови цілодобового чергового');

INSERT INTO `poll_votes` (`poll_id`, `poll_option_id`, `user_id`, `voted_at`) VALUES
(1, 1, 4, NOW()),
(1, 1, 5, NOW());

-- 15. Витрати ОСББ
INSERT INTO `expenses` (`id`, `category`, `description`, `amount`, `expense_date`, `contractor`, `created_by`) VALUES
(1, 'Обслуговування', 'Технічне обслуговування двох ліфтів за жовтень', 2800.00, '2026-11-01', 'ТОВ «Отіс Ліфт»', 1),
(2, 'Матеріали', 'Придбання LED ламп для освітлення сходів', 1450.00, '2026-11-03', 'Епіцентр', 1),
(3, 'Прибирання', 'Послуги клінінгу під’їздів', 4000.00, '2026-11-05', 'ФОП Коваль', 1);

SET FOREIGN_KEY_CHECKS = 1;
