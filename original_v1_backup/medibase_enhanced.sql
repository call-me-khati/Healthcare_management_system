SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;


CREATE DATABASE IF NOT EXISTS `medibase_uni`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE `medibase_uni`;


CREATE TABLE `user` (
  `user_id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`      VARCHAR(150) NOT NULL DEFAULT '',
  `gender`         ENUM('Male','Female','Other') DEFAULT NULL,
  `date_of_birth`  DATE DEFAULT NULL,
  `email`          VARCHAR(254) NOT NULL,
  `address`        TEXT DEFAULT NULL,
  `phone`          VARCHAR(20) DEFAULT NULL,
  `department`     VARCHAR(100) DEFAULT NULL,
  `password_hash`  VARCHAR(255) NOT NULL,
  `user_type`      ENUM('admin','doctor','nurse','student') NOT NULL DEFAULT 'student',
  `must_change_pwd` TINYINT(1) NOT NULL DEFAULT 1,
  `is_first_login`  TINYINT(1) NOT NULL DEFAULT 0,
  `profile_complete` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_user_email` (`email`),
  KEY `idx_user_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `doctors` (
  `doctor_id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`            INT UNSIGNED NOT NULL,
  `employee_id`        VARCHAR(50) NOT NULL,
  `full_name`          VARCHAR(150) DEFAULT NULL,
  `specialization`     VARCHAR(150) DEFAULT NULL,
  `department`         VARCHAR(100) DEFAULT NULL,
  `consultation_fee`   DECIMAL(10,2) DEFAULT NULL,
  `contact_number`     VARCHAR(20) DEFAULT NULL,
  `gender`             ENUM('Male','Female','Other') DEFAULT NULL,
  `availability_status` ENUM('Available','Unavailable','On Leave') NOT NULL DEFAULT 'Available',
  `bio`                TEXT DEFAULT NULL,
  `address`            TEXT DEFAULT NULL,
  `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`doctor_id`),
  UNIQUE KEY `uq_doctors_user`     (`user_id`),
  UNIQUE KEY `uq_doctors_employee` (`employee_id`),
  KEY `idx_doctors_name`   (`full_name`),
  KEY `idx_doctors_spec`   (`specialization`),
  KEY `idx_doctors_status` (`availability_status`),
  CONSTRAINT `fk_doctors_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nurses` (
  `nurse_id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `employee_id`    VARCHAR(50) NOT NULL,
  `full_name`      VARCHAR(150) DEFAULT NULL,
  `department`     VARCHAR(100) DEFAULT NULL,
  `contact_number` VARCHAR(20) DEFAULT NULL,
  `gender`         ENUM('Male','Female','Other') DEFAULT NULL,
  `address`        TEXT DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nurse_id`),
  UNIQUE KEY `uq_nurses_user`     (`user_id`),
  UNIQUE KEY `uq_nurses_employee` (`employee_id`),
  KEY `idx_nurses_name` (`full_name`),
  CONSTRAINT `fk_nurses_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `students` (
  `student_id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED NOT NULL,
  `student_uid`      VARCHAR(30) DEFAULT NULL COMMENT 'University-issued unique ID',
  `full_name`        VARCHAR(150) NOT NULL DEFAULT '',
  `email`            VARCHAR(254) NOT NULL,
  `course`           VARCHAR(150) DEFAULT NULL,
  `year_level`       VARCHAR(20) DEFAULT NULL,
  `contact_number`   VARCHAR(20) DEFAULT NULL,
  `blood_group`      VARCHAR(5) DEFAULT NULL,
  `emergency_contact_name`  VARCHAR(150) DEFAULT NULL,
  `emergency_contact_phone` VARCHAR(20) DEFAULT NULL,
  `medical_history`  TEXT DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `uq_students_user`  (`user_id`),
  UNIQUE KEY `uq_students_email` (`email`),
  UNIQUE KEY `uq_students_uid`   (`student_uid`),
  KEY `idx_students_name` (`full_name`),
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `allergy` (
  `allergy_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  PRIMARY KEY (`allergy_id`),
  UNIQUE KEY `uq_allergy_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `allergy` (`name`) VALUES
('Penicillin'),('Aspirin'),('Ibuprofen'),('Sulfa drugs'),('Latex'),
('Peanuts'),('Tree nuts'),('Shellfish'),('Eggs'),('Milk'),
('Soy'),('Wheat/Gluten'),('Bee stings'),('Dust mites'),('Cat dander'),('Dog dander');

CREATE TABLE `patient_allergy_info` (
  `student_id` INT UNSIGNED NOT NULL,
  `allergy_id` INT UNSIGNED NOT NULL,
  `level`      ENUM('Mild','Moderate','Severe','Life-threatening') NOT NULL DEFAULT 'Mild',
  PRIMARY KEY (`student_id`,`allergy_id`),
  KEY `fk_pai_allergy` (`allergy_id`),
  CONSTRAINT `fk_pai_student`  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pai_allergy`  FOREIGN KEY (`allergy_id`) REFERENCES `allergy`  (`allergy_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `availability` (
  `availability_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id`       INT UNSIGNED NOT NULL,
  `day_of_week`     ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time`      TIME NOT NULL,
  `end_time`        TIME NOT NULL,
  `slot_duration`   INT NOT NULL DEFAULT 10 COMMENT 'Minutes per slot',
  `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`availability_id`),
  UNIQUE KEY `uq_avail_slot` (`doctor_id`,`day_of_week`,`start_time`),
  KEY `idx_avail_day` (`day_of_week`),
  CONSTRAINT `fk_avail_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `appointments` (
  `appointment_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id`       INT UNSIGNED NOT NULL,
  `doctor_id`        INT UNSIGNED NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `reason`           TEXT DEFAULT NULL,
  `priority`         ENUM('Normal','Urgent','Emergency') NOT NULL DEFAULT 'Normal',
  `status`           ENUM('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `notes`            TEXT DEFAULT NULL COMMENT 'Doctor notes after consultation',
  `queue_number`     INT DEFAULT NULL,
  `is_followup`      TINYINT(1) NOT NULL DEFAULT 0,
  `followup_ref_id`  INT UNSIGNED DEFAULT NULL COMMENT 'Reference to original consultation',
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  KEY `idx_appts_student` (`student_id`),
  KEY `idx_appts_doctor`  (`doctor_id`),
  KEY `idx_appts_date`    (`appointment_date`),
  KEY `idx_appts_status`  (`status`),
  CONSTRAINT `fk_appts_doctor`  FOREIGN KEY (`doctor_id`)  REFERENCES `doctors`  (`doctor_id`)  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_appts_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `consultations` (
  `consultation_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id`    INT UNSIGNED NOT NULL,
  `doctor_id`         INT UNSIGNED NOT NULL,
  `student_id`        INT UNSIGNED NOT NULL,
  `consultation_date` DATE NOT NULL,
  `diagnosis`         TEXT DEFAULT NULL,
  `consultation_notes` TEXT DEFAULT NULL,
  `follow_up_date`    DATE DEFAULT NULL,
  `follow_up_notes`   TEXT DEFAULT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`consultation_id`),
  KEY `fk_consult_appt`    (`appointment_id`),
  KEY `fk_consult_doctor`  (`doctor_id`),
  KEY `fk_consult_student` (`student_id`),
  CONSTRAINT `fk_consult_appt`    FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_consult_doctor`  FOREIGN KEY (`doctor_id`)      REFERENCES `doctors`      (`doctor_id`)      ON DELETE CASCADE,
  CONSTRAINT `fk_consult_student` FOREIGN KEY (`student_id`)     REFERENCES `students`     (`student_id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `medicines` (
  `medicine_id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `medicine_name`  VARCHAR(200) NOT NULL,
  `generic_name`   VARCHAR(200) DEFAULT NULL,
  `category`       VARCHAR(100) DEFAULT NULL,
  `quantity`       INT NOT NULL DEFAULT 0,
  `unit`           VARCHAR(30) DEFAULT 'tablet',
  `expiry_date`    DATE DEFAULT NULL,
  `low_stock_threshold` INT NOT NULL DEFAULT 10,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`medicine_id`),
  KEY `idx_med_name`   (`medicine_name`),
  KEY `idx_med_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `medicines` (`medicine_name`,`generic_name`,`category`,`quantity`,`unit`,`expiry_date`,`low_stock_threshold`) VALUES
('Paracetamol 500mg','Acetaminophen','Analgesic',200,'tablet','2027-06-01',20),
('Amoxicillin 250mg','Amoxicillin','Antibiotic',150,'capsule','2027-03-01',15),
('Metformin 500mg','Metformin HCl','Antidiabetic',100,'tablet','2027-08-01',10),
('Amlodipine 5mg','Amlodipine besylate','Antihypertensive',80,'tablet','2027-07-01',10),
('Atorvastatin 10mg','Atorvastatin calcium','Statin',90,'tablet','2027-09-01',10),
('Omeprazole 20mg','Omeprazole','PPI',120,'capsule','2027-05-01',15),
('Salbutamol Inhaler','Albuterol','Bronchodilator',30,'inhaler','2027-04-01',5),
('Cetirizine 10mg','Cetirizine HCl','Antihistamine',160,'tablet','2027-10-01',20),
('Metronidazole 400mg','Metronidazole','Antibiotic',100,'tablet','2027-11-01',10),
('Azithromycin 500mg','Azithromycin','Antibiotic',60,'tablet','2027-02-01',8);

CREATE TABLE `prescriptions` (
  `prescription_id`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `consultation_id`  INT UNSIGNED NOT NULL,
  `medicine_id`      INT UNSIGNED NOT NULL,
  `dosage_amount`    DECIMAL(8,2) DEFAULT NULL,
  `dosage_unit`      VARCHAR(30) DEFAULT NULL,
  `frequency`        VARCHAR(80) DEFAULT NULL,
  `duration`         VARCHAR(80) DEFAULT NULL,
  `instruction`      TEXT DEFAULT NULL,
  `dispensed`        TINYINT(1) NOT NULL DEFAULT 0,
  `dispensed_at`     DATETIME DEFAULT NULL,
  PRIMARY KEY (`prescription_id`),
  KEY `idx_rx_consult`  (`consultation_id`),
  KEY `idx_rx_medicine` (`medicine_id`),
  CONSTRAINT `fk_rx_consult`  FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rx_medicine` FOREIGN KEY (`medicine_id`)    REFERENCES `medicines`     (`medicine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `lab_tests` (
  `lab_test_id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `consultation_id` INT UNSIGNED NOT NULL,
  `student_id`      INT UNSIGNED NOT NULL,
  `doctor_id`       INT UNSIGNED NOT NULL,
  `test_name`       VARCHAR(200) NOT NULL,
  `request_date`    DATE NOT NULL,
  `status`          ENUM('Requested','In Progress','Done','Completed','Cancelled') NOT NULL DEFAULT 'Requested',
  `result`          TEXT DEFAULT NULL,
  `result_date`     DATE DEFAULT NULL,
  `patient_done_at` DATETIME DEFAULT NULL COMMENT 'When patient clicked done',
  `notes`           TEXT DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lab_test_id`),
  KEY `fk_lab_consult` (`consultation_id`),
  KEY `fk_lab_student` (`student_id`),
  KEY `fk_lab_doctor`  (`doctor_id`),
  CONSTRAINT `fk_lab_consult` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lab_student` FOREIGN KEY (`student_id`)      REFERENCES `students`      (`student_id`)      ON DELETE CASCADE,
  CONSTRAINT `fk_lab_doctor`  FOREIGN KEY (`doctor_id`)       REFERENCES `doctors`       (`doctor_id`)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `follow_ups` (
  `followup_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `consultation_id`  INT UNSIGNED NOT NULL,
  `student_id`       INT UNSIGNED NOT NULL,
  `doctor_id`        INT UNSIGNED NOT NULL,
  `followup_date`    DATE NOT NULL,
  `notes`            TEXT DEFAULT NULL,
  `status`           ENUM('Scheduled','Booked','Completed','Missed') NOT NULL DEFAULT 'Scheduled',
  `appointment_id`   INT UNSIGNED DEFAULT NULL COMMENT 'Booked follow-up appointment',
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`followup_id`),
  KEY `fk_fu_consult`  (`consultation_id`),
  KEY `fk_fu_student`  (`student_id`),
  KEY `fk_fu_doctor`   (`doctor_id`),
  KEY `fk_fu_appt`     (`appointment_id`),
  CONSTRAINT `fk_fu_consult` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fu_student` FOREIGN KEY (`student_id`)      REFERENCES `students`      (`student_id`)      ON DELETE CASCADE,
  CONSTRAINT `fk_fu_doctor`  FOREIGN KEY (`doctor_id`)       REFERENCES `doctors`       (`doctor_id`)       ON DELETE CASCADE,
  CONSTRAINT `fk_fu_appt`    FOREIGN KEY (`appointment_id`)  REFERENCES `appointments`  (`appointment_id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `notifications` (
  `notification_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `type`            ENUM('appointment','lab_test','medicine','followup','reminder','system') NOT NULL DEFAULT 'system',
  `title`           VARCHAR(200) NOT NULL,
  `message`         TEXT NOT NULL,
  `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
  `related_id`      INT UNSIGNED DEFAULT NULL COMMENT 'ID of related entity',
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_notif_user`   (`user_id`),
  KEY `idx_notif_unread` (`user_id`,`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `feedback` (
  `feedback_id`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id`   INT UNSIGNED NOT NULL,
  `type`         ENUM('Feedback','Complaint','Suggestion') NOT NULL DEFAULT 'Feedback',
  `subject`      VARCHAR(250) NOT NULL,
  `message`      TEXT NOT NULL,
  `status`       ENUM('Open','Under Review','Resolved','Closed') NOT NULL DEFAULT 'Open',
  `admin_notes`  TEXT DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`),
  KEY `fk_fb_student` (`student_id`),
  CONSTRAINT `fk_fb_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `audit_log` (
  `log_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(100) NOT NULL,
  `table_name`  VARCHAR(60) DEFAULT NULL,
  `record_id`   INT UNSIGNED DEFAULT NULL,
  `old_value`   TEXT DEFAULT NULL,
  `new_value`   TEXT DEFAULT NULL,
  `ip_address`  VARCHAR(45) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_audit_user`   (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_date`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `user` (`full_name`,`email`,`password_hash`,`user_type`,`must_change_pwd`,`is_first_login`,`profile_complete`,`gender`,`department`) VALUES
('System Administrator','admin@medibase.bd','$2y$12$wUVS38eTnKF/hZA.LchDmuG0ridcd.Hu5JV1sGrUhB4QhTdsaxLPK','admin',0,0,1,'Male','Administration');

-- Seed doctors
INSERT INTO `user` (`full_name`,`email`,`password_hash`,`user_type`,`must_change_pwd`,`is_first_login`,`profile_complete`,`gender`,`department`) VALUES
('Dr. Ayesha Rahman','dr.ayesha@medibase.bd','$2y$12$wUVS38eTnKF/hZA.LchDmuG0ridcd.Hu5JV1sGrUhB4QhTdsaxLPK','doctor',0,0,1,'Female','Cardiology'),
('Dr. Tanvir Islam','dr.tanvir@medibase.bd','$2y$12$wUVS38eTnKF/hZA.LchDmuG0ridcd.Hu5JV1sGrUhB4QhTdsaxLPK','doctor',0,0,1,'Male','Neurology'),
('Dr. Mita Sultana','dr.mita@medibase.bd','$2y$12$wUVS38eTnKF/hZA.LchDmuG0ridcd.Hu5JV1sGrUhB4QhTdsaxLPK','doctor',0,0,1,'Female','Endocrinology');

INSERT INTO `doctors` (`user_id`,`employee_id`,`full_name`,`specialization`,`department`,`consultation_fee`,`gender`,`bio`) VALUES
(2,'DOC-001','Dr. Ayesha Rahman','Cardiology','Cardiology',500.00,'Female','Senior cardiologist with 15 years of experience.'),
(3,'DOC-002','Dr. Tanvir Islam','Neurology','Neurology',600.00,'Male','Specialist in neurological disorders.'),
(4,'DOC-003','Dr. Mita Sultana','Endocrinology','Endocrinology',550.00,'Female','Expert in diabetes and hormonal conditions.');

-- Availability (10-min slots)
INSERT INTO `availability` (`doctor_id`,`day_of_week`,`start_time`,`end_time`,`slot_duration`) VALUES
(1,'Sunday','09:00:00','13:00:00',10),(1,'Monday','09:00:00','13:00:00',10),(1,'Wednesday','09:00:00','13:00:00',10),
(2,'Tuesday','10:00:00','15:00:00',10),(2,'Thursday','10:00:00','15:00:00',10),(2,'Saturday','10:00:00','13:00:00',10),
(3,'Monday','11:00:00','16:00:00',10),(3,'Tuesday','11:00:00','16:00:00',10),(3,'Thursday','11:00:00','16:00:00',10);

-- Seed nurse
INSERT INTO `user` (`full_name`,`email`,`password_hash`,`user_type`,`must_change_pwd`,`is_first_login`,`profile_complete`,`gender`,`department`) VALUES
('Nurse Salma Begum','nurse.salma@medibase.bd','$2y$12$wUVS38eTnKF/hZA.LchDmuG0ridcd.Hu5JV1sGrUhB4QhTdsaxLPK','nurse',0,0,1,'Female','ICU');
INSERT INTO `nurses` (`user_id`,`employee_id`,`full_name`,`department`,`gender`) VALUES
(5,'NUR-001','Nurse Salma Begum','ICU','Female');

-- Seed students
INSERT INTO `user` (`full_name`,`email`,`password_hash`,`user_type`,`must_change_pwd`,`is_first_login`,`profile_complete`,`gender`,`department`) VALUES
('Farida Hossain','farida@student.uni','$2y$12$wUVS38eTnKF/hZA.LchDmuG0ridcd.Hu5JV1sGrUhB4QhTdsaxLPK','student',0,0,1,'Female','CSE'),
('Sabbir Ahmed','sabbir@student.uni','$2y$12$wUVS38eTnKF/hZA.LchDmuG0ridcd.Hu5JV1sGrUhB4QhTdsaxLPK','student',0,0,1,'Male','EEE');

INSERT INTO `students` (`user_id`,`student_uid`,`full_name`,`email`,`course`,`year_level`,`contact_number`,`blood_group`) VALUES
(6,'STU-2024-001','Farida Hossain','farida@student.uni','BSc Computer Science','Year 2','+8801766666666','B+'),
(7,'STU-2024-002','Sabbir Ahmed','sabbir@student.uni','BSc Electrical Eng.','Year 3','+8801799999999','O+');

-- Default password for all accounts: Admin@1234

COMMIT;
