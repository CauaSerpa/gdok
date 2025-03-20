<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

// Verifica se o método de requisição é GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (!$conn) {
            throw new Exception("Conexão inválida com o banco de dados.");
        }

        // Obtém o ID do usuário logado da sessão
        $currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        if (!$currentUserId) {
            throw new Exception("Usuário não autenticado.");
        }

        $stmt = $conn->prepare("SELECT id, name FROM tb_sending_departments WHERE user_id = ?");
        $stmt->execute([$currentUserId]);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'departments' => $departments]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    exit;
}
