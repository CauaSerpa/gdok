<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "mark-all-read") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $now = date('Y-m-d H:i:s');

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Atualiza no banco de dados para marcar como lida
                $stmt = $conn->prepare("UPDATE tb_notifications SET is_read = 1, read_in = ? WHERE user_id = ?");
                if ($stmt->execute([$now, $_SESSION['user_id']])) {
                    // Commit na transação
                    $conn->commit();

                    echo json_encode(['status' => 'success', 'title' => 'Sucesso', 'message' => 'Todas as notificações foram marcadas como lidas.']);
                } else {
                    // Rollback em caso de erro
                    $conn->rollBack();

                    echo json_encode(['status' => 'error', 'title' => 'Sucesso', 'message' => 'Erro ao marcar todas as notificações como lidas.']);
                }
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao marcar todas as notificações como lidas: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao marcar todas as notificações como lidas', 'error' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
            exit;
        }
    } else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "mark-as-read") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $notificationId = intval($_POST['id']);
            $now = date('Y-m-d H:i:s');

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Atualiza no banco de dados para marcar como lida
                $stmt = $conn->prepare("UPDATE tb_notifications SET is_read = 1, read_in = ? WHERE id = ? AND user_id = ?");
                if ($stmt->execute([$now, $notificationId, $_SESSION['user_id']])) {
                    // Commit na transação
                    $conn->commit();

                    echo json_encode(['status' => 'success', 'title' => 'Sucesso', 'message' => 'Notificação marcada como lida com sucesso.']);
                } else {
                    // Rollback em caso de erro
                    $conn->rollBack();

                    echo json_encode(['status' => 'error', 'title' => 'Sucesso', 'message' => 'Erro ao marcar como lida.']);
                }
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao marcar como lida: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao marcar como lida', 'error' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }