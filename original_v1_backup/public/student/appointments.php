<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/controllers/StudentController.php';
$c = new StudentController();
$_SERVER['REQUEST_METHOD'] === 'POST' ? $c->cancelAppointment() : $c->myAppointments();
