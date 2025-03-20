<?php
    if (isset($_SESSION['finalize_registration_user_id']) && !empty($_SESSION['finalize_registration_user_id'])) {
        // Preparar a consulta para verificar se o usuário faz parte de algum escritório
        $stmt = $conn->prepare("
            SELECT u.*, o.name AS office_name, o.document AS office_document
            FROM tb_users u
            LEFT JOIN tb_office_users ou ON ou.user_id = u.id
            LEFT JOIN tb_offices o ON o.id = ou.office_id
            WHERE u.id = ?
            LIMIT 1
        ");

        // Executar a consulta passando o ID do usuário da sessão
        $stmt->execute([$_SESSION['finalize_registration_user_id']]);

        // Verificar se o usuário foi encontrado e está vinculado a um escritório
        if ($stmt->rowCount()) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se o usuário está vinculado a um escritório, você pode acessar os dados do escritório
            if (!empty($user['office_name']) && !empty($user['office_document'])) {
                $_SESSION['user_id'] = $_SESSION['finalize_registration_user_id'];
                $_SESSION['email'] = $_SESSION['finalize_registration_email'];

                unset($_SESSION['finalize_registration_user_id']);
                unset($_SESSION['finalize_registration_email']);

                header('Location: ' . INCLUDE_PATH_DASHBOARD);
                exit;
            } else if ($permission['role'] == 3) {
                $_SESSION['user_id'] = $_SESSION['finalize_registration_user_id'];
                $_SESSION['email'] = $_SESSION['finalize_registration_email'];

                unset($_SESSION['finalize_registration_user_id']);
                unset($_SESSION['finalize_registration_email']);

                header('Location: ' . INCLUDE_PATH_DASHBOARD);
                exit;
            }
        } else {
            // Se o usuário não for encontrado, redireciona para login ou erro
            session_destroy();
            session_start();
            $_SESSION['msg'] = array(
                'status' => 'error',
                'alert' => 'danger',
                'title' => 'Erro',
                'message' => 'Usuário não encontrado. Realize login novamente.'
            );
            header('Location: ' . INCLUDE_PATH_AUTH);
            exit;
        }
    } else {
        // Se o usuário não for encontrado, redireciona para login ou erro
        session_destroy();
        session_start();
        $_SESSION['msg_login'] = array(
            'status' => 'error',
            'alert' => 'danger',
            'title' => 'Erro',
            'message' => 'Por favor, faça login para acessar essa página.'
        );
        header('Location: ' . INCLUDE_PATH_AUTH);
        exit;
    }
?>

<div class="col-xl-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card p-3">
                <div class="card-body">

                    <div class="mb-0 border-0 p-md-5 p-lg-0 p-4">
                        <div class="mb-4 p-0 text-center">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>" class="auth-logo">
                                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-dark.png" alt="logo-dark" class="mx-auto" height="50"/>
                            </a>
                        </div>

                        <!-- Exibição de mensagem de sucesso ou erro -->
                        <?php if (isset($_SESSION['msg'])): ?>
                            <div class="alert alert-<?= $_SESSION['msg']['alert']; ?>"><?= $_SESSION['msg']['message']; ?></div>
                        <?php unset($_SESSION['msg']); endif; ?>

                        <div class="auth-title-section mb-3 text-center">
                            <h3 class="text-dark fs-20 fw-medium mb-2">Olá, <?= $user['firstname']; ?>!</h3>
                            <p class="text-dark text-capitalize fs-14 mb-0">Estamos felizes em tê-lo conosco! Antes de começarmos, por favor, nos diga qual é a sua posição no escritório?</p>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="<?= INCLUDE_PATH_AUTH; ?>registrar-empresa" class="btn btn-primary btn-block">
                                <span>Sou Dono/Gestor</span>
                            </a>

                            <div class="saprator my-2"><span>Ou você é um colaborador do escritório?</span></div>

                            <a href="<?= INCLUDE_PATH_AUTH; ?>aguardar-convite" class="btn btn-secondary btn-block">
                                <span>Sou Funcionário</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>