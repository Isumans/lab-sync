CREATE TABLE `online_booking_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day_group` enum('mon_fri','sat','sun') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_patients` int(11) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slot` (`day_group`, `start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
