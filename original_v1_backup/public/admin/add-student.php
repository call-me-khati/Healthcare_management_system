<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/controllers/AdminController.php';
$c = new AdminController();
$_SERVER['REQUEST_METHOD'] === 'POST' ? $c->handleAddStudent() : $c->showAddStudent();
