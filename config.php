<?php

$dsn = 'sqlite:main.db';

require_once 'lib.php';

try {
    $db = new PDO($dsn);
} catch (Throwable $e) {
    jsonResponse(['errors' => [$e->getMessage()]], 500);
    die();
}
