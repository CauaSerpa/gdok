<?php
// Verifique se o token foi passado via GET
if (!isset($token)) {
    $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Token inválido. Não foi possível ativar sua conta.');
    header("Location: " . INCLUDE_PATH_DASHBOARD);
    exit();
}

// Pegue o token da URL
$active_token = $token;

// Consulta para verificar o token e buscar o usuário
$query = "SELECT * FROM tb_users WHERE active_token = :active_token LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':active_token', $active_token);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Se o usuário existe, atualize o status para "ativo" e limpe o active_token
    $update_query = "UPDATE tb_users SET active_status = 1, active_token = NULL WHERE id = :user_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':user_id', $user['id']);

    if ($update_stmt->execute()) {
        // Verifica se existe a sessão de finalize_registration_user_id e finalize_registration_email
        if (isset($_SESSION['finalize_registration_user_id']) && isset($_SESSION['finalize_registration_email'])) {
            // Transfere para as variáveis de sessão principais
            $_SESSION['user_id'] = $_SESSION['finalize_registration_user_id'];
            $_SESSION['email'] = $_SESSION['finalize_registration_email'];

            // Remove as sessões de finalize_registration_user_id e finalize_registration_email
            unset($_SESSION['finalize_registration_user_id']);
            unset($_SESSION['finalize_registration_email']);
        }

        // Defina a sessão com a mensagem de sucesso
        $_SESSION['msg'] = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Conta ativada com sucesso! Você já pode acessar o painel.');

        // Redireciona o usuário para o painel
        header("Location: " . INCLUDE_PATH_AUTH);
        exit();
    } else {
        $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Houve um erro ao ativar sua conta. Tente novamente.');
        header("Location: " . INCLUDE_PATH_AUTH . "verificar-email");
        exit();
    }
} else {
    $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Token inválido ou expirado. Não foi possível ativar sua conta.');
    header("Location: " . INCLUDE_PATH_AUTH . "verificar-email");
    exit();
}
?>