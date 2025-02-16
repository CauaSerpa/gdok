<?php
    if (isset($_SESSION['finalize_registration_user_id']) && !empty($_SESSION['finalize_registration_user_id'])) {
        // Consulta no banco de dados
        $stmt = $conn->prepare("SELECT * FROM tb_users WHERE id = ? AND active_status = 0");
        $stmt->execute([$_SESSION['finalize_registration_user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se o e-mail foi ativado
        if (!$user) {
            header('Location: ' . INCLUDE_PATH_DASHBOARD);
            exit;
        }
    } else {
        header('Location: ' . INCLUDE_PATH_DASHBOARD);
        exit;
    }
?>

<div class="col-md-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card p-3 mb-0 mb-0">
                <div class="card-body">
                    <div class="mb-0 border-0 p-md-5 p-lg-0 p-4">

                        <div class="mb-4 p-0 text-center">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>" class="auth-logo">
                                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-dark.png" alt="logo-dark" class="mx-auto" height="50" />
                            </a>
                        </div>

                        <!-- Exibição de mensagem de sucesso ou erro -->
                        <?php if (isset($_SESSION['msg'])): ?>
                            <div class="alert alert-<?= $_SESSION['msg']['alert']; ?>"><?= $_SESSION['msg']['message']; ?></div>
                        <?php unset($_SESSION['msg']); endif; ?>

                        <div class="auth-title-section mb-3 text-center mt-2">
                            <h3 class="text-dark fs-20 fw-medium mb-2">Confirmação de Email</h3>
                            <p class="text-muted fs-15">Por favor, verifique seu e-mail para confirmação. <br>Clique no link do e-mail para verificar sua conta</p>
                        </div>

                        <div class="text-center">
                            <a href="<?= INCLUDE_PATH_AUTH; ?>reenviar-email-verificacao" class="btn btn-primary mt-3" type="submit">Reenviar Confirmação</a>
                        </div>

                        <div class="text-center">
                            <a href="<?= INCLUDE_PATH_AUTH; ?>tipo-de-usuario" class="btn btn-link mt-0">Pular Etapa</a>
                        </div>

                        <div class="text-center pt-4">
                            <div class="maintenance-img">
                                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/svg/confirmation-email.svg" height="200" alt="svg-logo">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>