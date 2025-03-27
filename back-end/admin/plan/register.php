<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-plan") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            // Recebe os dados do formulário
            $planName = $_POST['planName'] ?? '';
            $planDescription = $_POST['planDescription'] ?? '';

            $planPrice = $_POST['price'] ?? 0;
            $planPrice = str_replace('.', '', $planPrice);
            $planPrice = str_replace(',', '.', $planPrice);

            $billingPeriod = $_POST['billingPeriod'] ?? '';
            $accessibleModules = isset($_POST['accessibleModules']) ? $_POST['accessibleModules'] : [];
            $defaultPlan = isset($_POST['defaultPlan']) ? 1 : 0;
            $publicPlan = isset($_POST['publicPlan']) ? 1 : 0;
            $activePlan = isset($_POST['activePlan']) ? 1 : 0;

            // Converte o array de módulos para JSON para armazenamento
            $accessibleModulesJson = json_encode($accessibleModules);

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Verifica se e um administrador
                $permission = getUserPermission($user_id, $conn);
                if ($permission['role'] !== 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Usuário não tem permissão.']);
                    exit;
                }

                // Inicia transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    INSERT INTO tb_plans 
                    (plan_name, plan_description, plan_price, billing_period, accessible_modules, default_plan, public_plan, active_plan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $planName,
                    $planDescription,
                    $planPrice,
                    $billingPeriod,
                    $accessibleModulesJson,
                    $defaultPlan,
                    $publicPlan,
                    $activePlan
                ]);

                // Commit na transação
                $conn->commit();

                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Plano cadastrado com sucesso.'
                ]);
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();
                error_log("Erro ao cadastrar plano: " . $e->getMessage());
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Erro ao cadastrar plano.', 
                    'error' => $e->getMessage()
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Usuário não autenticado.'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Método de requisição inválido.'
        ]);
        exit;
    }