CREATE TABLE `pending_tasks` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT '0',
  `request` varbinary(2048) NOT NULL,
  `created` datetime NOT NULL,
  `finished` datetime DEFAULT '2000-01-01 000000',
  `result_json` varchar(255) DEFAULT NULL,
  `task_type_id` int UNSIGNED NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;;
