<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
$c = new AuthController();
$_SERVER['REQUEST_METHOD'] === 'POST' ? $c->handleChangePassword() : $c->showChangePassword();
