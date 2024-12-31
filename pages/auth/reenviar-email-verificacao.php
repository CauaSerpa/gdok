<?php
    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    // Verifique se o usuário está logado e se a variável session existe
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
        $_SESSION['msg'] = "Você precisa estar logado para reenviar o e-mail de confirmação.";
        header("Location: " . INCLUDE_PATH_DASHBOARD . "login.php");
        exit();
    }

    // Variáveis de sessão
    $user_id = $_SESSION['user_id']; // ID do usuário
    $email = $_SESSION['email']; // E-mail do usuário

    // Gerar um novo token para o e-mail de confirmação
    $active_token = generateToken();

    // Consulta para atualizar o active_token na tabela tb_users
    $query = "UPDATE tb_users SET active_token = :active_token WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':active_token', $active_token);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        // Enviar o link de verificação por e-mail
        $verification_link = INCLUDE_PATH_AUTH . "confirmar-email/" . $active_token;
        $subject = "Bem-vindo ao " . $project['name'];
        $content = array(
            "layout" => "verify-email", 
            "content" => array("name" => $name, "link" => $verification_link)
        );

        // Função para enviar e-mail (você deve ter implementado a função sendMail)
        sendMail($name, $email, $subject, $content);

        $_SESSION['msg'] = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'O e-mail de confirmação foi reenviado com sucesso. Verifique sua caixa de entrada!');
    } else {
        $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Houve um erro ao tentar atualizar o token. Tente novamente.');
    }

    // Redireciona de volta para a página de confirmação
    header("Location: " . INCLUDE_PATH_AUTH . "verificar-email");
    exit();
?>