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
                            <h3 class="text-dark fs-20 fw-medium mb-2">Recuperar Senha</h3>
                            <p class="text-dark text-capitalize fs-14 mb-0">Digite o e-mail da sua conta abaixo para recuperar sua senha.</p>
                        </div>

                        <div class="pt-0">
                            <form id="recoveryForm" class="my-4">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input class="form-control" name="email" type="email" id="email" placeholder="Digite seu E-mail" required>
                                </div>

                                <div class="form-group mb-0 row">
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button class="btn btn-primary" id="btnSubmit" type="submit"> Recuperar Senha </button>
                                            <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                <span role="status">Carregando...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="text-center text-muted">
                                <p class="mb-0">Você se lembrou? <a class='text-primary ms-2 fw-medium' href='<?= INCLUDE_PATH_AUTH; ?>'>Voltar ao Login</a></p>
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
    $("#recoveryForm").validate({
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            email: {
                required: "Por favor, insira seu e-mail.",
                email: "Por favor, insira um e-mail válido."
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
            // Impede o envio padrão do formulário
            event.preventDefault(); 

            // Define os botões como variáveis
            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            // Desativa o botão de login e ativa o loader
            btnSubmit.addClass("d-none").prop("disabled", true);
            btnLoader.removeClass("d-none");

            // Cria o formData a partir dos dados do formulário
            var formData = new FormData(form);

            // Adiciona um novo campo
            formData.append("action", "recover-password");

            // Envia o formulário via AJAX
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/auth/recover-password.php', // URL do script PHP que processa o login
                type: 'POST',
                data: formData,
                processData: false, // Impede que o jQuery processe os dados
                contentType: false, // Impede que o jQuery defina o Content-Type
                success: function(response) {
                    // Se a resposta for sucesso, redireciona para a dashboard ou página inicial
                    if(response.status === 'success') {
                        // Caso contrário, exibe a mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#recoveryForm").before('<div class="alert alert-primary">' + response.message + '</div>');
                    } else {
                        // Caso contrário, exibe a mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#recoveryForm").before('<div class="alert alert-danger">' + response.message + '</div>');
                    }

                    // Reativa o botão e oculta o loader
                    btnSubmit.removeClass("d-none").prop("disabled", false);
                    btnLoader.addClass("d-none");
                },
                error: function() {
                    console.error("Erro no AJAX:", status, error);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#recoveryForm").before('<div class="alert alert-danger">Ocorreu um erro, tente novamente mais tarde.</div>');

                    // Reativa o botão e oculta o loader
                    btnSubmit.removeClass("d-none").prop("disabled", false);
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>