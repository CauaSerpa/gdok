<?php
    session_start();
    include('./../../../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "validate-email") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $email = $_POST['email'];
            $active_token = generateToken();

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Formato de e-mail inválido.');
                }

                // Verifica a senha atual do usuário
                $stmt = $conn->prepare("SELECT firstname, email FROM tb_users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    throw new Exception("Usuário não encontrado.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Enviar e-mail de verificação
                $verification_link = INCLUDE_PATH_AUTH . "confirmar-email/" . $active_token;
                $subject = "Verifique seu e-mail";
                $content = array("layout" => "verify-update-email", "content" => array("firstname" => $user['firstname'], "link" => $verification_link));
                sendMail($user['firstname'], $email, $subject, $content);

                // Atualiza a senha no banco de dados
                $stmt = $conn->prepare("UPDATE tb_users SET email = ?, active_status = 0, active_token = ? WHERE id = ?");
                $stmt->execute([$email, $active_token, $user_id]);

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'message' => 'E-mail de confirmação reenviado com sucesso.']);
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao alterar o e-mail: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao reenviar e-mail de confirmação.', 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }