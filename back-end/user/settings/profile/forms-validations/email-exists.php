<?php
    include('./../../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['action']) && $_POST['action'] === "email-exists") {
        $email = trim($_POST['email']);
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

        try {
            $query = "SELECT id FROM tb_users WHERE email = ?";
            $params = [$email];

            // Se estiver editando, exclui a empresa atual da verificação
            if ($userId) {
                $query .= " AND id != ?";
                $params[] = $userId;
            }

            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                echo json_encode(['status' => 'exists']);
            } else {
                echo json_encode(['status' => 'available']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }