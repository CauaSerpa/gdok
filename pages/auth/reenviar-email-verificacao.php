<?php
    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    // Variáveis de sessão
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['finalize_registration_user_id']) ? $_SESSION['finalize_registration_user_id'] : null); // ID do usuário
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : (isset($_SESSION['finalize_registration_email']) ? $_SESSION['finalize_registration_email'] : null); // E-mail do usuário

    // Verifique se o usuário está logado e se a variável session existe
    if (empty($user_id) && empty($email)) {
        $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Você precisa estar logado para reenviar o e-mail de confirmação.');
        header("Location: " . INCLUDE_PATH_AUTH);
        exit();
    }

    // Consulta o usuário pelo e-mail
    $stmt = $conn->prepare("SELECT id, firstname FROM tb_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
            "content" => array("firstname" => $user['firstname'], "link" => $verification_link)
        );

        // Função para enviar e-mail (você deve ter implementado a função sendMail)
        sendMail($user['firstname'], $email, $subject, $content);

        $_SESSION['msg'] = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'O e-mail de confirmação foi reenviado com sucesso. Verifique sua caixa de entrada!');
    } else {
        $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Houve um erro ao tentar atualizar o token. Tente novamente.');
    }

    // Redireciona de volta para a página de confirmação
    header("Location: " . INCLUDE_PATH_AUTH . "verificar-email");
    exit();
?>