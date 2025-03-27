<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

// Verifica se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];

    $stmt = $conn->prepare("
        SELECT 
            u.id, 
            u.firstname, 
            u.lastname, 
            o.name AS office_name, 
            u.email, 
            u.document, 
            u.phone, 
            u.active_status, 
            u.created_at 
        FROM tb_users u 
        LEFT JOIN tb_office_users ou ON ou.user_id = u.id
        LEFT JOIN tb_offices o ON o.id = ou.office_id
        WHERE u.id = ? 
    ");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $name = $user['firstname'] . " " . $user['lastname'];

        $status = $user['active_status'] ? 'Ativo' : 'Inativo';

        $userData = [
            "name" => $name,
            "document" => $user['document'],
            "office" => $user['office_name'],
            "email" => $user['email'],
            "phone" => $user['phone'],
            'status' => $status,
            'created_at' => date("d/m/Y", strtotime($user['created_at'])),
        ];

        echo json_encode([
            'status' => 'success',
            'data' => $userData
        ]);
    } else {
        return false;
    }
}