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

        $department = isset($_GET['department']) ? intval($_GET['department']) : null;

        // SQL Base
        $sql = "SELECT id, name FROM tb_sending_categories WHERE user_id = :user_id";

        // Caso tenha $department adiciona na consulta
        if ($department) {
            $sql .= " AND department_id = :department";
        }

        // Valores padrões
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);

        // Caso tenha $department passa os valores
        if ($department) {
            $stmt->bindValue(':department', $department, PDO::PARAM_INT);
        }

        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'categories' => $categories]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    exit;
}
