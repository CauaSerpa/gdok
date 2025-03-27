<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-appearence-colors") {
        if (isset($_SESSION['user_id'])) {
            $user_id         = $_SESSION['user_id'];
            $bg_color        = $_POST['bg_color'];
            $header_color    = $_POST['header_color'];
            $sidebar_color   = $_POST['sidebar_color'];
            $text_color      = $_POST['text_color'];
            $button_color    = $_POST['button_color'];
            $hover_color     = $_POST['hover_color'];

            // Verifica se e um administrador
            $permission = getUserPermission($user_id, $conn);
            if ($permission['role'] !== 0) {
                echo json_encode(['status' => 'error', 'message' => 'Usuário não tem permissão.']);
                exit;
            }

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    UPDATE tb_template_appearance SET
                    bg_color = ?, header_color = ?, sidebar_color = ?, text_color = ?, button_color = ?, hover_color = ?
                    WHERE id = ?
                ");
                $stmt->execute([$bg_color, $header_color, $sidebar_color, $text_color, $button_color, $hover_color, 1]);

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Aparência atualizada com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Aparência atualizada com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao atualizar a aparência do template: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar a aparência do template', 'error' => $e->getMessage()]);
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