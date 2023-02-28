<?php

declare(strict_types=1);

require_once 'config.php';

/** @var PDO $db */

$request = file_get_contents('php://input');
$body    = json_decode($request, true);

if (true === empty($body['listId'])) {
    $listId = generateListUniqueId();
} else {
    $listId = $body['listId'];
}

if (true === empty($body['title'])) {
    echo jsonResponse(['errors' => ['Не передан параметр title.']], 422);
    return;
}

if (true === empty($body['text'])) {
    echo jsonResponse(['errors' => ['Не передан параметр text.']], 422);
    return;
}

$sql = <<<SQL
SELECT id
     , users.phone
     , users.name
FROM users
LEFT JOIN lists ON lists.user_id = users.id 
               AND lists.list_id = :listId
WHERE lists.user_id is null
SQL;

$stmt = $db->prepare($sql);
$stmt->bindParam(':listId', $listId);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = <<<SQL
INSERT INTO lists(list_id, user_id) VALUES(:list_id, :user_id)
SQL;

foreach ($rows as $row) {
    pushToQueue($row['phone'], $row['name'], $body['title'], $body['text']);
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $row['id']);
    $stmt->bindParam(':list_id', $listId);
    $stmt->execute();
}

echo jsonResponse(
    [
        'listId' => $listId,
    ]
);
return;
