<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/controllers/DoctorController.php';
$c = new DoctorController();
$_SERVER['REQUEST_METHOD'] === 'POST' ? $c->updateAppointment() : $c->appointments();
