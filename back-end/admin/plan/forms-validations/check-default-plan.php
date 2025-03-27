<?php
    session_start();
    include('./../../../../config.php');
    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        try {
            $billingPeriod = isset($_GET['billingPeriod']) ? $_GET['billingPeriod'] : '';
            $planId = isset($_GET['planId']) ? $_GET['planId'] : null;
            if (empty($billingPeriod)) {
                throw new Exception("Período de cobrança não informado.");
            }
            
            // Consulta para verificar se já existe um plano padrão para o período informado
            $sql = "SELECT id, plan_name FROM tb_plans WHERE billing_period = ? AND default_plan = 1";

            if ($planId) {
                $sql .= " AND id != ?";
            }

            $stmt = $conn->prepare($sql);
            if ($planId) {
                $stmt->execute([$billingPeriod, $planId]);
            } else {
                $stmt->execute([$billingPeriod]);
            }
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'exists' => true,
                    'message' => 'O plano "'.$row['plan_name'].'", já está definido como padrão para o período de cobrança selecionado.'
                ]);
            } else {
                echo json_encode(['exists' => false]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'exists' => false,
                'message' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'exists' => false,
            'message' => 'Método de requisição inválido.'
        ]);
    }