<?php
    session_unset();
    session_destroy();
    session_start();

    if (isset($_COOKIE['remember_me'])) {
        // Remove o cookie do navegador
        setcookie("remember_me", "", time() - 3600, "/");
    }
?>

<div class="col-md-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card p-3 mb-0">
                <div class="card-body">

                    <div class="mb-0 border-0 p-md-5 p-lg-0 p-4">
                        <div class="mb-4 p-0 text-center">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>" class="auth-logo">
                                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-dark.png" alt="logo-dark" class="mx-auto" height="50"/>
                            </a>
                        </div>

                        <div class="text-center auth-title-section">
                            <h3 class="text-dark fs-20 fw-medium mb-2">Você está desconectado</h3>
                            <p class="text-muted fs-15">Obrigado por usar o <?= $project['name']; ?></p>
                        </div>

                        <div class="text-center">
                            <a href="<?= INCLUDE_PATH_AUTH; ?>" class="btn btn-primary mt-3"> Login </a>
                        </div>

                        <div class="maintenance-img text-center pt-4">
                            <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/svg/logout.svg" height="200" alt="svg-logo">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>