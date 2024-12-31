<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update-password') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($token) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Dados incompletos.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM tb_password_resets WHERE token = :token AND expiration_date > NOW()");
            $stmt->execute(['token' => $token]);
            $reset = $stmt->fetch();
        
            if (!$reset) {
                throw new Exception('Token inválido ou expirado.');
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE tb_users SET password = :password WHERE email = :email");
            $stmt->execute(['password' => $hashedPassword, 'email' => $reset['email']]);

            $stmt = $conn->prepare("DELETE FROM tb_password_resets WHERE token = :token");
            $stmt->execute(['token' => $token]);

            $conn->prepare("DELETE FROM tb_password_resets WHERE expiration_date < NOW()")->execute();

            echo json_encode(['status' => 'success', 'message' => 'Senha atualizada com sucesso.']);

            // Defina a mensagem de sucesso na sessão
            $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Senha atualizada com sucesso! Por favor, faça login na sua conta.');
            $_SESSION['msg'] = $message;
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
?>