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
        $stmt = $conn->prepare("SELECT * FROM tb_users WHERE email = ?");
        $stmt->execute([$email]);

        // Usando fetch para obter o resultado
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Se a ativação foi feita, salva o usuário na sessão
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

            // Verifica se o e-mail foi ativado
            if ($user['active_status'] == 0) {
                // E-mail não ativado, redireciona para página de verificação
                $_SESSION['msg'] = array('status' => 'success', 'alert' => 'primary', 'title' => 'Erro', 'message' => 'Por favor, verifique seu e-mail para ativar sua conta.', 'redirect' => INCLUDE_PATH_AUTH . 'verificar-email');
                echo json_encode(['status' => 'success', 'redirect' => INCLUDE_PATH_AUTH . 'verificar-email']);
                exit();
            } else {
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