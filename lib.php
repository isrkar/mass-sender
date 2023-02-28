<?php

declare(strict_types=1);

/**
 * @param array $input
 * @param int   $code
 *
 * @return string
 */
function jsonResponse(array $input, int $code = 200): string
{
    http_response_code($code);
    header('Content-type: application/json');
    return json_encode($input);
}

/**
 * @return string
 */
function generateListUniqueId(): string
{
    return uniqid() . '-' . uniqid();
}

/**
 * @param string $phone
 * @param string $name
 * @param string $text
 * @param string $message
 *
 * @return void
 */
function pushToQueue(string $phone, string $name, string $text, string $message): void
{
    return;
}
