<?php
    session_start();
    include('./../../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-password") {
        if (isset($_SESSION['user_id'])) {
            $admin_id = $_SESSION['user_id'];

            $user_id = $_POST['user'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

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

                // Verifica a senha atual do usuário
                $stmt = $conn->prepare("SELECT firstname, email, password FROM tb_users WHERE id = ?");
                $stmt->execute([$user_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$result) {
                    throw new Exception("Usuário não encontrado.");
                }

                // Verifica se a nova senha e a confirmação são iguais
                if ($new_password !== $confirm_password) {
                    echo json_encode(['status' => 'error', 'message' => 'A nova senha e a confirmação não coincidem.']);
                    exit;
                }

                // Hash da nova senha
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Iniciar transação
                $conn->beginTransaction();

                // Atualiza a senha no banco de dados
                $stmt = $conn->prepare("UPDATE tb_users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);

                // Commit na transação
                $conn->commit();

                // Enviar e-mail de verificação
                $subject = "Senha alterada por um administrador";
                $content = array("layout" => "admin-update-password", "content" => array("firstname" => $result['firstname'], "email" => $result['email'], "password" => $new_password));
                sendMail($result['firstname'], $result['email'], $subject, $content);

                echo json_encode(['status' => 'success', 'message' => 'Senha alterada com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Senha alterada com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao alterar a senha: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar a senha.', 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }