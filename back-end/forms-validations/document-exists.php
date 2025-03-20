<?php
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['document']) && isset($_POST['action']) && $_POST['action'] === "document-exists") {
        $document = trim($_POST['document']);
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

        try {
            $sql = "SELECT id FROM tb_users WHERE document = :document";

            if (!empty($user_id)) {
                $sql .= " AND id != :user_id";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':document', $document, PDO::PARAM_STR);

            if (!empty($user_id)) {
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            }

            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                echo json_encode(['status' => 'exists']);
            } else {
                echo json_encode(['status' => 'available']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['document']) && isset($_POST['action']) && $_POST['action'] === "company-document-exists") {
        $document = trim($_POST['document']);

        try {
            $stmt = $conn->prepare("SELECT id FROM tb_offices WHERE document = ?");
            $stmt->execute([$document]);
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