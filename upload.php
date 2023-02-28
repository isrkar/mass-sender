<?php

declare(strict_types=1);

require_once 'config.php';

/** @var PDO $db */

if (false === isset($_FILES['file'])) {
    echo jsonResponse(
        [
            'errors' => [
                'В запросе не передан файл.',
            ],
        ],
        422
    );
    return;
}

$fileResource = fopen($_FILES['file']['tmp_name'], 'r');

if ($fileResource === false) {
    echo jsonResponse(
        [
            'errors' => [
                'Ошибка открытия загруженного файла.',
            ],
        ],
        500
    );
    return;
}

$paramsTpl = <<<SQL
(:phone, :name)
SQL;

$sql = <<<SQL
INSERT INTO users (phone, name) VALUES 
SQL;

$batch = [];

while ($row = fgetcsv($fileResource)) {
    $batch[] = [
        'name'  => trim($row[1]),
        'phone' => trim($row[0]),
    ];

    // накопили пачку или дошли до конца файла и есть что-то в пачке
    if (
        count($batch) > 100
        || (
            ftell($fileResource) >= $_FILES['file']['size']
            && false === empty($batch)
        )
    ) {
        $params = [];
        foreach ($batch as $i => $item) {
            $params[] = str_replace(
                [':phone', ':name'],
                [':phone' . $i, ':name' . $i],
                $paramsTpl
            );
        }

        $preparedSql = $sql . implode(',', $params);

        $stmt = $db->prepare($preparedSql);

        foreach ($batch as $i => $item) {
            $stmt->bindParam(':phone' . $i, $item['phone']);
            $stmt->bindParam(':name' . $i, $item['name']);
        }

        $stmt->execute();

        $batch = [];
    }
}

echo jsonResponse(
    ['success' => true]
);
return;
