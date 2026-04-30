<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/controllers/NurseController.php';
$c = new NurseController();
$_SERVER['REQUEST_METHOD'] === 'POST' ? $c->updateLabStatus() : $c->labTests();
