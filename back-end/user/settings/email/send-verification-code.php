<?php
    session_start();
    include('./../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "send-verification-code") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $new_email = $_POST['new_email'];

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Formato de e-mail inválido.');
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

                // Gerar código de verificação
                $verificationCode = rand(100000, 999999);

                // Salva o código de verificação
                $_SESSION['verification_code'] = $verificationCode;

                // Enviar o código de verificação para o e-mail do usuário (ou via SMS, etc.)
                $subject = "Seu código é $verificationCode";
                $content = array("layout" => "verification-code", "content" => array("firstname" => $user['firstname'], "code" => $verificationCode));
                sendMail($user['firstname'], $user['email'], $subject, $content);

                echo json_encode(['status' => 'success', 'message' => 'Código de verificação enviado para ' . $user['email'] . '. Verifique seu e-mail.']);
            } catch (Exception $e) {
                // Registrar erro em um log
                error_log("Erro ao enviar e-mail de verificação: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao enviar e-mail de verificação.', 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }