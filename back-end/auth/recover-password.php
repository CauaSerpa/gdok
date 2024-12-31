<?php
session_start();
include('./../../config.php');

header('Content-Type: application/json');

function generateToken($length = 50) {
    return bin2hex(random_bytes($length));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'recover-password') {
    $email = trim($_POST['email']);
    
    // Validação de e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'E-mail inválido!']);
        exit();
    }

    try {
        // Inicia a transação
        $conn->beginTransaction();

        // Consulta o usuário pelo e-mail
        $stmt = $conn->prepare("SELECT id, firstname FROM tb_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'E-mail não encontrado!']);
            $conn->rollBack(); // Reverte a transação
            exit();
        }

        // Gera um token único
        $token = generateToken();
        $expireTime = date('Y-m-d H:i:s', strtotime('+1 day')); // Expira em 1 dia

        // Remove tokens anteriores para o mesmo e-mail (opcional, para evitar duplicados)
        $deleteStmt = $conn->prepare("DELETE FROM tb_password_resets WHERE email = ?");
        $deleteStmt->execute([$email]);

        // Insere o novo token na tabela `password_resets`
        $insertStmt = $conn->prepare("INSERT INTO tb_password_resets (email, token, expiration_date) VALUES (?, ?, ?)");
        $insertStmt->execute([$email, $token, $expireTime]);

        // Enviar e-mail de verificação
        $resetLink = INCLUDE_PATH_AUTH . "atualizar-senha/" . $token;
        $subject = "Recuperação de Senha";
        $content = array("layout" => "recup-password", "content" => array("firstname" => $user['firstname'], "link" => $resetLink));

        sendMail($user['firstname'], $email, $subject, $content);

        // Confirma a transação
        $conn->commit();
        echo json_encode(['status' => 'success', 'alert' => 'primary', 'message' => 'E-mail de recuperação enviado com sucesso!']);
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack(); // Reverte a transação em caso de exceção
        }
        echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'Erro ao processar a solicitação: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'Método de requisição inválido.']);
    exit;
}
?>
