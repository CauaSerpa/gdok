<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-category-sending") {
    if (isset($_SESSION['user_id']) && isset($_POST['category_name'])) {
        $user_id = $_SESSION['user_id'];
        $category_name = $_POST['category_name'];

        try {
            $stmt = $conn->prepare("INSERT INTO tb_sending_categories (user_id, name) VALUES (?, ?)");
            $stmt->execute([$user_id, $category_name]);

            // Recuperar o ID da última categoria inserida
            $category_id = $conn->lastInsertId();

            // Retornar a resposta com o nome e o ID da nova categoria
            echo json_encode([
                'status' => 'success',
                'message' => 'Categoria criada com sucesso.',
                'data' => [
                    'id' => $category_id,
                    'name' => $category_name
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