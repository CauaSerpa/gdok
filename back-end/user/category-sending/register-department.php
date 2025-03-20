<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-department-sending") {
    if (isset($_SESSION['user_id']) && isset($_POST['department_name'])) {
        $user_id = $_SESSION['user_id'];
        $department_name = $_POST['department_name'];

        try {
            $stmt = $conn->prepare("INSERT INTO tb_sending_departments (user_id, name) VALUES (?, ?)");
            $stmt->execute([$user_id, $department_name]);

            // Recuperar o ID da último depto de envio inserido
            $department_id = $conn->lastInsertId();

            // Retornar a resposta com o nome e o ID do novo depto de envio
            echo json_encode([
                'status' => 'success',
                'message' => 'Depto de envio criado com sucesso.',
                'data' => [
                    'id' => $department_id,
                    'name' => $department_name
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Dados insuficientes.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método inválido.']);
}