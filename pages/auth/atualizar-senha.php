<?php
    if (isset($token)) {
        // Verifica o token
        $stmt = $conn->prepare("SELECT * FROM tb_password_resets WHERE token = ? AND expiration_date > NOW()");
        $stmt->execute([$token]);

        if (!$stmt->rowCount()) {
            // Defina a mensagem de erro na sessão
            $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Token inválido ou expirado.');
            $_SESSION['msg'] = $message;

            // Redireciona o usuário para recuperar-senha
            header("Location: " . INCLUDE_PATH_AUTH . "recuperar-senha");
            exit;
        }
    } else {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Token não fornecido.');
        $_SESSION['msg'] = $message;

        // Redireciona o usuário para recuperar-senha
        header("Location: " . INCLUDE_PATH_AUTH . "recuperar-senha");
        exit;
    }
?>

<div class="col-xl-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card p-3 mb-0">
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

                        <div class="auth-title-section mb-3 text-center"> 
                            <h3 class="text-dark fs-20 fw-medium mb-2">Atualizar Senha</h3>
                            <p class="text-dark text-capitalize fs-14 mb-0">Digite e confirme sua nova senha para atualizar.</p>
                        </div>

                        <div class="pt-0">
                            <form id="updatePasswordForm" class="my-4">
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <input class="form-control" name="password" type="password" id="password" placeholder="Digite sua nova senha" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                    <input class="form-control" name="confirm_password" type="password" id="confirm_password" placeholder="Confirme sua nova senha" required>
                                </div>

                                <div class="form-group mb-0 row">
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button class="btn btn-primary" id="btnSubmit" type="submit"> Atualizar Senha </button>
                                            <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                <span role="status">Carregando...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="text-center text-muted">
                                <p class="mb-0">Lembrou da senha? <a class='text-primary ms-2 fw-medium' href='<?= INCLUDE_PATH_AUTH; ?>'>Voltar ao Login</a></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Validação do formulário com jQuery
    $("#updatePasswordForm").validate({
        rules: {
            password: {
                required: true,
                minlength: 5
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            }
        },
        messages: {
            password: {
                required: "Por favor, insira sua nova senha.",
                minlength: "A senha deve ter pelo menos 5 caracteres."
            },
            confirm_password: {
                required: "Por favor, confirme sua nova senha.",
                equalTo: "As senhas não coincidem."
            }
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.next("label"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        submitHandler: function(form) {
            event.preventDefault(); // Impede o envio padrão do formulário

            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            // Desativa o botão de login e ativa o loader
            btnSubmit.addClass("d-none").prop("disabled", true);
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "update-password");
            formData.append("token", "<?= $token; ?>");

            // Envia o formulário via AJAX
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/auth/update-password.php', // URL do script PHP que processa a atualização
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.status === 'success') {
                        window.location.href = '<?= INCLUDE_PATH_AUTH; ?>';
                    } else {
                        $(".alert").remove();
                        $("#updatePasswordForm").before('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    btnSubmit.removeClass("d-none").prop("disabled", false);
                    btnLoader.addClass("d-none");
                },
                error: function() {
                    $(".alert").remove();
                    $("#updatePasswordForm").before('<div class="alert alert-danger">Ocorreu um erro, tente novamente mais tarde.</div>');
                    btnSubmit.removeClass("d-none").prop("disabled", false);
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>