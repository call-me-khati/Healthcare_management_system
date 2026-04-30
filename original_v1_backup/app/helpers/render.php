<?php
// app/helpers/render.php
require_once __DIR__ . '/../helpers/auth.php';

function renderPage(string $pageTitle, string $viewFile, array $data = []): void {
    extract($data, EXTR_SKIP);
    include __DIR__ . '/../views/shared/header.php';
    include $viewFile;
    include __DIR__ . '/../views/shared/footer.php';
}
