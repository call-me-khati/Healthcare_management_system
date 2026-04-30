<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/controllers/DoctorController.php';
(new DoctorController())->toggleStatus();
