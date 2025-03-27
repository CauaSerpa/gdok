<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-plan") {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Verifica se o ID do plano foi informado
        $planId = $_POST['plan_id'] ?? null;
        if (!$planId) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID do plano não informado.'
            ]);
            exit;
        }

        // Recebe os dados do formulário
        $planName = $_POST['planName'] ?? '';
        $planDescription = $_POST['planDescription'] ?? '';

        $planPrice = $_POST['price'] ?? 0;
        // Converte o valor para formato numérico padrão
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
                UPDATE tb_plans SET
                    plan_name = ?,
                    plan_description = ?,
                    plan_price = ?,
                    billing_period = ?,
                    accessible_modules = ?,
                    default_plan = ?,
                    public_plan = ?,
                    active_plan = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $planName,
                $planDescription,
                $planPrice,
                $billingPeriod,
                $accessibleModulesJson,
                $defaultPlan,
                $publicPlan,
                $activePlan,
                $planId
            ]);

            // Commit na transação
            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Plano atualizado com sucesso.'
            ]);

            $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Plano atualizado com sucesso.');
            $_SESSION['msg'] = $message;
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();
            error_log("Erro ao atualizar plano: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao atualizar plano.',
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