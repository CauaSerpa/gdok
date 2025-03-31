<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $totalRecords = 0;

    echo json_encode([
        'draw'            => $draw ?? 1,
        'recordsTotal'    => $totalRecords ?? 0,
        'recordsFiltered' => empty($search) ? $totalRecords : $stmt->rowCount(),
        'data'            => $data ?? []
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    exit;
}