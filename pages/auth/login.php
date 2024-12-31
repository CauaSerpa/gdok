<?php
    if (isset($_SESSION['user_id'])) {
        header("Location: " . INCLUDE_PATH_DASHBOARD);
        exit();
    } elseif (isset($_COOKIE['remember_me']) && !isset($_SESSION['user_id'])) {
        // Decodifica o valor do cookie de Base64 para JSON
        $base64Data = $_COOKIE['remember_me'];
        $jsonData = base64_decode($base64Data);

        // Decodifica o JSON para um array
        $data = json_decode($jsonData, true);

        if (isset($data['remember_me'])) {
            $user_id = $data['remember_me'];

            // Consulta para verificar se o cookie existe no banco de dados
            $stmt = $conn->prepare("SELECT * FROM tb_users WHERE id = ?");
            $stmt->execute([$user_id]);

            if ($stmt->rowCount()) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                header("Location: " . INCLUDE_PATH_DASHBOARD);
                exit();
            }
        }
    }
?>

<div class="col-xl-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card p-3 mb-0">
                <div class="card-body">

                    <div class="mb-0 border-0 p-md-5 p-lg-0 p-4">
                        <div class="mb-4 p-0 text-center">
                            <a href="index.html" class="auth-logo">
                                <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-dark.png" alt="logo-dark" class="mx-auto" height="50" />
                            </a>
                        </div>

                        <!-- Exibição de mensagem de sucesso ou erro -->
                        <?php if (isset($_SESSION['msg'])): ?>
                            <div class="alert alert-<?= $_SESSION['msg']['alert']; ?>"><?= $_SESSION['msg']['message']; ?></div>
                        <?php unset($_SESSION['msg']); endif; ?>

                        <div class="auth-title-section mb-3 text-center"> 
                            <h3 class="text-dark fs-20 fw-medium mb-2">Bem-vindo de volta</h3>
                            <p class="text-dark text-capitalize fs-14 mb-0">Faça login para continuar no <?= $project['name']; ?>.</p>
                        </div>

                        <div class="row">
                            <div class="col-6 mt-2">
                                <a class="btn text-dark border fw-normal d-flex align-items-center justify-content-center"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 48 48" class="me-2">
                                        <path fill="#ffc107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917"/><path fill="#ff3d00" d="m6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C16.318 4 9.656 8.337 6.306 14.691"/><path fill="#4caf50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44"/>
                                        <path fill="#1976d2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917"/>
                                    </svg>
                                    <span>Google</span>
                                </a>
                            </div>

                            <div class="col-6 mt-2">
                                <a class="btn text-dark border fw-normal d-flex align-items-center justify-content-center"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 256 256" class="me-2">
                                        <path fill="#1877f2" d="M256 128C256 57.308 198.692 0 128 0S0 57.308 0 128c0 63.888 46.808 116.843 108 126.445V165H75.5v-37H108V99.8c0-32.08 19.11-49.8 48.348-49.8C170.352 50 185 52.5 185 52.5V84h-16.14C152.959 84 148 93.867 148 103.99V128h35.5l-5.675 37H148v89.445c61.192-9.602 108-62.556 108-126.445"/><path fill="#fff" d="m177.825 165l5.675-37H148v-24.01C148 93.866 152.959 84 168.86 84H185V52.5S170.352 50 156.347 50C127.11 50 108 67.72 108 99.8V128H75.5v37H108v89.445A129 129 0 0 0 128 256a129 129 0 0 0 20-1.555V165z"/>
                                    </svg>
                                    <span>Facebook</span>
                                </a>
                            </div>
                        </div>
                        
                        <div class="saprator my-4"><span>ou continue com o E-mail</span></div>

                        <div class="pt-0">
                            <form id="loginForm" class="my-4">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Endereço de E-mail</label>
                                    <input class="form-control" name="email" type="email" id="email" placeholder="Digite seu E-mail" required>
                                </div>
    
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input class="form-control" name="password" type="password" id="password" placeholder="Digite sua Senha" required>
                                </div>
    
                                <div class="form-group d-flex mb-3">
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="remember" class="form-check-input" id="remember" checked>
                                            <label class="form-check-label" for="remember">Lembrar de mim</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 text-end">
                                        <a class='text-muted fs-14' href='<?= INCLUDE_PATH_AUTH; ?>recuperar-senha'>Esqueceu a senha?</a>                             
                                    </div>
                                </div>
                                
                                <div class="form-group mb-0 row">
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button class="btn btn-primary" type="submit" id="btnSubmit">Entrar</button>
                                            <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                <span role="status">Carregando...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center text-muted mb-4">
                                <p class="mb-0">Não tem uma conta?<a class='text-primary ms-2 fw-medium' href='<?= INCLUDE_PATH_AUTH; ?>registro'>Cadastre-se</a></p>
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
    $("#loginForm").validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 5
            }
        },
        messages: {
            email: {
                required: "Por favor, insira seu e-mail.",
                email: "Por favor, insira um e-mail válido."
            },
            password: {
                required: "Por favor, insira sua senha.",
                minlength: "A senha deve ter pelo menos 5 caracteres."
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
            formData.append("action", "login");

            // Envia o formulário via AJAX
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/auth/login.php', // URL do script PHP que processa o login
                type: 'POST',
                data: formData,
                processData: false, // Impede que o jQuery processe os dados
                contentType: false, // Impede que o jQuery defina o Content-Type
                success: function(response) {
                    // Se a resposta for sucesso, redireciona para a dashboard ou página inicial
                    if(response.status === 'success') {
                        if (response.redirect) {
                            window.location.href = response.redirect; // Redireciona para a página inicial após login
                        } else {
                            window.location.href = '<?= INCLUDE_PATH_DASHBOARD; ?>'; // Redireciona para a página inicial após login
                        }
                    } else {
                        // Caso contrário, exibe a mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#loginForm").before('<div class="alert alert-danger">' + response.message + '</div>');
                    }

                    // Reativa o botão e oculta o loader
                    btnSubmit.removeClass("d-none").prop("disabled", false);
                    btnLoader.addClass("d-none");
                },
                error: function() {
                    console.error("Erro no AJAX:", status, error);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#loginForm").before('<div class="alert alert-danger">Ocorreu um erro, tente novamente mais tarde.</div>');

                    // Reativa o botão e oculta o loader
                    btnSubmit.removeClass("d-none").prop("disabled", false);
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>