<?php
    session_start();
    include('./../../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "block-user") {
        if (isset($_SESSION['user_id'])) {
            $admin_id = $_SESSION['user_id'];

            $user_id = $_POST['user'];
            $status = $_POST['status'];

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Verifica se e um administrador
                $permission = getUserPermission($admin_id, $conn);
                if ($permission['role'] !== 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Usuário não tem permissão.']);
                    exit;
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Atualiza a senha no banco de dados
                $stmt = $conn->prepare("UPDATE tb_users SET status = ? WHERE id = ?");
                $stmt->execute([$status, $user_id]);

                // Commit na transação
                $conn->commit();

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'success', 'message' => 'Usuário bloqueado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Usuário bloqueado com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao bloquear o usuário: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao bloquear o usuário.', 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }