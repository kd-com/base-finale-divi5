<?php
require_once("wp-config.php");

$expectedToken = "manoir_secret-tocken-kdcom-2025";
$token = $_GET['token'] ?? '';

if ($token !== $expectedToken) {
    http_response_code(403);
    die("Forbidden");
}

header("Content-Type: application/sql");
header('Content-Disposition: attachment; filename="dump.sql"');

$cmd = sprintf(
    'mysqldump --host=%s -u%s -p%s %s',
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_USER),
    escapeshellarg(DB_PASSWORD),
    escapeshellarg(DB_NAME)
);

passthru($cmd);
