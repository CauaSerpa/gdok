<?php
    include('./../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['document']) && isset($_POST['action']) && $_POST['action'] === "document-exists") {
        $document = trim($_POST['document']);
        $companyId = isset($_POST['company_id']) ? intval($_POST['company_id']) : null;

        try {
            $query = "SELECT id FROM tb_companies WHERE document = ?";
            $params = [$document];

            // Se estiver editando, exclui a empresa atual da verificação
            if ($companyId) {
                $query .= " AND id != ?";
                $params[] = $companyId;
            }

            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($company) {
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
