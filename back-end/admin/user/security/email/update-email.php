<?php
    session_start();
    include('./../../../../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-email") {
        if (isset($_SESSION['user_id'])) {
            $admin_id = $_SESSION['user_id'];

            $user_id = $_POST['user'];
            $new_email = $_POST['new_email'];
            $active_token = generateToken();

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

                if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Formato de e-mail inválido.');
                }

                // Verifica se o e-mail já existe na tabela
                $stmt = $conn->prepare("SELECT id FROM tb_users WHERE email = ?");
                $stmt->execute([$new_email]); // Certifique-se de passar o e-mail, não o user_id
                $emailExists = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($emailExists) > 0) { // Correção aqui
                    echo json_encode(['status' => 'error', 'message' => 'O e-mail inserido já está em uso.']);
                    exit;
                }

                // Verifica a senha atual do usuário
                $stmt = $conn->prepare("SELECT firstname, email FROM tb_users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    throw new Exception("Usuário não encontrado.");
                }

                // Verifica se a nova senha e a confirmação são iguais
                if ($user['email'] === $new_email) {
                    echo json_encode(['status' => 'error', 'message' => 'O novo e-mail deve ser diferente do seu e-mail atual.']);
                    exit;
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Enviar e-mail de verificação
                $verification_link = INCLUDE_PATH_AUTH . "confirmar-email/" . $active_token;
                $subject = "Verifique seu e-mail";
                $content = array("layout" => "verify-update-email", "content" => array("firstname" => $user['firstname'], "link" => $verification_link));
                sendMail($user['firstname'], $new_email, $subject, $content);

                // Atualiza a senha no banco de dados
                $stmt = $conn->prepare("UPDATE tb_users SET email = ?, active_status = 0, active_token = ? WHERE id = ?");
                $stmt->execute([$new_email, $active_token, $user_id]);

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'message' => 'E-mail alterado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'E-mail alterado com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao alterar o e-mail: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar o e-mail.', 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }