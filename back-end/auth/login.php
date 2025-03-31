<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "login") {
        // Obtém os dados do formulário
        $email = $_POST['email'];
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;

        // Validação de e-mail e senha
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'E-mail inválido!']);
            exit();
        }

        // Consulta no banco de dados
        $stmt = $conn->prepare("
            SELECT u.*, o.id AS office_id
            FROM tb_users u
            LEFT JOIN tb_office_users ou ON ou.user_id = u.id
            LEFT JOIN tb_offices o ON o.id = ou.office_id
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);

        // Usando fetch para obter o resultado
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Se a ativação foi feita, salva o usuário na sessão
            $_SESSION['finalize_registration_user_id'] = $user['id'];
            $_SESSION['finalize_registration_email'] = $user['email'];

            // Verifica se o e-mail foi ativado
            if ($user['status'] == 0) {
                unset($_SESSION['finalize_registration_user_id']);
                unset($_SESSION['finalize_registration_email']);

                // Se a ativação foi feita, salva o usuário na sessão
                $_SESSION['blocked_user_id'] = $user['id'];
                $_SESSION['blocked_email'] = $user['email'];

                // E-mail não ativado, redireciona para página de verificação
                $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Sua conta foi bloqueada por um administrador, entre em contato com nosso suporte para mais informações.', 'redirect' => INCLUDE_PATH_AUTH . 'usuario-bloqueado');
                echo json_encode(['status' => 'success', 'redirect' => INCLUDE_PATH_AUTH . 'usuario-bloqueado']);
                exit();
            } else if ($user['active_status'] == 0) {
                // E-mail não ativado, redireciona para página de verificação
                $_SESSION['msg'] = array('status' => 'success', 'alert' => 'primary', 'title' => 'Erro', 'message' => 'Por favor, verifique seu e-mail para ativar sua conta.', 'redirect' => INCLUDE_PATH_AUTH . 'verificar-email');
                echo json_encode(['status' => 'success', 'redirect' => INCLUDE_PATH_AUTH . 'verificar-email']);
                exit();
            } else if (!isset($user['office_id']) && empty($user['office_id']) && $user['role'] !== 0) { // Verifica se o usuário está associado a uma empresa caso o usuario nao seja um administrador
                // Nao esta associado a nenhuma empresa, redireciona para página de tipo de usuario
                $_SESSION['msg'] = array('status' => 'success', 'alert' => 'primary', 'title' => 'Erro', 'message' => 'Por favor, finalize seu cadastro para acessar o ' . $project['name'] . '.', 'redirect' => INCLUDE_PATH_AUTH . 'tipo-de-usuario');
                echo json_encode(['status' => 'success', 'redirect' => INCLUDE_PATH_AUTH . 'tipo-de-usuario']);
                exit();
            } else {
                unset($_SESSION['finalize_registration_user_id']);
                unset($_SESSION['finalize_registration_email']);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                // Lógica de "lembrar de mim"
                if ($remember) {
                    // Cria um array com o user_id
                    $data = array('remember_me' => $user['id']);

                    // Codifica o array em JSON
                    $jsonData = json_encode($data);

                    // Codifica o JSON em Base64
                    $base64Data = base64_encode($jsonData);

                    // Define o cookie
                    setcookie("remember_me", $base64Data, time() + (86400 * 30), "/"); // Cookie válido por 30 dias
                }

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success']);
                exit();
            }
        } else {
            // Mensagem genérica para o usuário
            echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'E-mail ou senha incorretos!']);
            exit();
        }
    }
?>