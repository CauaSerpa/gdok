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

                        <div class="auth-title-section mb-3 text-center"> 
                            <h3 class="text-dark fs-20 fw-medium mb-2">Bem-vindo</h3>
                            <p class="text-dark text-capitalize fs-14 mb-0">Se registre para acessar o <?= $project['name']; ?>.</p>
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
                            <form id="registrationForm" class="my-4">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-3">
                                            <label for="firstname" class="form-label">Primeiro Nome</label>
                                            <input class="form-control" name="firstname" type="text" id="firstname" placeholder="Digite seu Primeiro Nome" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-3">
                                            <label for="lastname" class="form-label">Sobrenome</label>
                                            <input class="form-control" name="lastname" type="text" id="lastname" placeholder="Digite seu Sobrenome" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input class="form-control" name="email" type="email" id="email" placeholder="Digite seu E-mail" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Telefone</label>
                                    <input class="form-control" name="phone" type="tel" id="phone" maxlength="15" placeholder="Digite seu Telefone" onkeyup="handlePhone(event)" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="document" class="form-label">CPF/CNPJ</label>
                                    <input class="form-control" name="document" type="text" id="document" maxlength="18" placeholder="Digite seu CPF ou CNPJ" onkeyup="handleCpfCnpj(event)" required>
                                </div>
    
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input class="form-control" name="password" type="password" id="password" placeholder="Digite sua Senha" required>
                                </div>

                                <div class="form-group d-flex mb-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input type="checkbox" name="agree" class="form-check-input" id="agree" required>
                                            <label class="form-check-label" for="agree">Eu concordo com os <a href="#" class="text-primary fw-medium">Termos e Condições</a></label>
                                        </div>
                                    </div><!--end col-->
                                </div>

                                <div class="form-group mb-0 row">
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button class="btn btn-primary" id="btnSubmit" type="submit">Registrar</button>
                                            <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                <span role="status">Carregando...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center text-muted mb-4">
                                <p class="mb-0">Já tem uma conta? <a class='text-primary ms-2 fw-medium' href='<?= INCLUDE_PATH_AUTH; ?>'>Acesse aqui</a></p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
const handlePhone = (event) => {
    let input = event.target
    input.value = phoneMask(input.value)
}

const phoneMask = (value) => {
    if (!value) return ""
    value = value.replace(/\D/g,'')
    value = value.replace(/(\d{2})(\d)/,"($1) $2")
    value = value.replace(/(\d)(\d{4})$/,"$1-$2")
    return value
}

function handleCpfCnpj(event) {
    var input = event.target;
    var value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

    if (value.length <= 11) {
        $(input).mask('000.000.000-00####');
    } else {
        $(input).mask('00.000.000/0000-00');
    }
}

$(document).ready(function() {
    // // Máscara de CPF e CNPJ
    // $('#document').on('input', function() {
    //     var value = $(this).val().replace(/\D/g, ''); // Remove tudo o que não é número
    //     if (value.length <= 11) {
    //         // Máscara de CPF
    //         value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    //     } else {
    //         // Máscara de CNPJ
    //         value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    //     }
    //     // Limitar a entrada de caracteres a 18
    //     if (value.length > 18) {
    //         value = value.substring(0, 18);
    //     }
    //     $(this).val(value);
    // });

    // Adicionar validação personalizada para o e-mail
    $.validator.addMethod("emailExists", function(value, element) {
        let isValid = false;
        if (value) {
            $.ajax({
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/forms-validations/email-exists.php", // URL do script PHP que verifica o e-mail no banco de dados
                type: "POST",
                data: { action: 'email-exists', email: value },
                async: false, // Sincronizar para garantir a validação antes de prosseguir
                success: function(response) {
                    isValid = response.status === "available"; // Verifica se o e-mail está disponível
                },
                error: function() {
                    isValid = false;
                }
            });
        }
        return isValid;
    }, "Este e-mail já está cadastrado.");

    // Adicionar validação personalizada para o e-mail
    $.validator.addMethod("documentExists", function(value, element) {
        let isValid = false;
        if (value) {
            $.ajax({
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/forms-validations/document-exists.php", // URL do script PHP que verifica o e-mail no banco de dados
                type: "POST",
                data: { action: 'document-exists', document: value },
                async: false, // Sincronizar para garantir a validação antes de prosseguir
                success: function(response) {
                    isValid = response.status === "available"; // Verifica se o e-mail está disponível
                },
                error: function() {
                    isValid = false;
                }
            });
        }
        return isValid;
    }, "Este CPF/CNPJ já está cadastrado.");

    // Função para validar CPF ou CNPJ
    $.validator.addMethod("cpfcnpj", function(value, element) {
        value = value.replace(/\D/g, ''); // Remove tudo o que não for número
        if (value.length === 11) {
            return validarCPF(value);
        } else if (value.length === 14) {
            return validarCNPJ(value);
        }
        return false;
    }, "Por favor, insira um CPF ou CNPJ válido");

    function validarCPF(cpf) {
        let soma = 0;
        let resto;
        if (cpf === "00000000000" || cpf === "11111111111" || cpf === "22222222222" || cpf === "33333333333" || cpf === "44444444444" || cpf === "55555555555" || cpf === "66666666666" || cpf === "77777777777" || cpf === "88888888888" || cpf === "99999999999") {
            return false; // CPF inválido
        }
        // Valida CPF
        for (let i = 1; i <= 9; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;

        soma = 0;
        for (let i = 1; i <= 10; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        return resto === parseInt(cpf.substring(10, 11));
    }

    function validarCNPJ(cnpj) {
        // Remove qualquer caractere não numérico
        cnpj = cnpj.replace(/\D/g, '');

        // Verifica se o CNPJ tem 14 dígitos
        if (cnpj.length !== 14) {
            return false;
        }

        // CNPJ's inválidos conhecidos
        const cnpjsInvalidos = [
            "00000000000000", "11111111111111", "22222222222222", "33333333333333", 
            "44444444444444", "55555555555555", "66666666666666", "77777777777777", 
            "88888888888888", "99999999999999"
        ];

        if (cnpjsInvalidos.includes(cnpj)) {
            return false; // CNPJ inválido
        }

        // Valida primeiro dígito verificador
        let soma = 0;
        let peso = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for (let i = 0; i < 12; i++) {
            soma += parseInt(cnpj.charAt(i)) * peso[i];
        }
        let resto = soma % 11;
        if (resto < 2) {
            resto = 0;
        } else {
            resto = 11 - resto;
        }
        if (resto !== parseInt(cnpj.charAt(12))) {
            return false;
        }

        // Valida segundo dígito verificador
        soma = 0;
        peso = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for (let i = 0; i < 13; i++) {
            soma += parseInt(cnpj.charAt(i)) * peso[i];
        }
        resto = soma % 11;
        if (resto < 2) {
            resto = 0;
        } else {
            resto = 11 - resto;
        }
        return resto === parseInt(cnpj.charAt(13));
    }

    // Validação do Formulário
    $("#registrationForm").validate({
        rules: {
            firstname: {
                required: true,
                minlength: 2,
            },
            lastname: {
                required: true,
                minlength: 2,
            },
            email: {
                required: true,
                email: true,
                emailExists: true,
            },
            phone: {
                required: true,
                minlength: 14,
            },
            document: {
                required: true,
                minlength: 14,
                cpfcnpj: true,
                documentExists: true,
            },
            password: {
                minlength: 5,
                required: true,
            },
            agree: "required",
        },
        messages: {
            firstname: {
                required: "Por favor, insira seu nome",
                minlength: "Seu nome deve ter pelo menos 2 caracteres",
            },
            lastname: {
                required: "Por favor, insira seu nome",
                minlength: "Seu nome deve ter pelo menos 2 caracteres",
            },
            email: {
                required: "Por favor, insira seu e-mail",
                email: "Por favor, insira um e-mail válido",
                emailExists: "Este e-mail já está cadastrado.",
            },
            phone: {
                required: "Por favor, insira seu telefone",
                minlength: "Seu telefone deve ter pelo menos 14 caracteres",
            },
            document: {
                required: "Por favor, insira seu CPF/CNPJ",
                minlength: "Seu documento deve ter pelo menos 14 caracteres",
                cpfcnpj: "Por favor, insira um CPF ou CNPJ válido",
                documentExists: "Este CPF/CNPJ já está cadastrado.",
            },
            password: {
                required: "Por favor, insira uma senha",
                minlength: "Sua senha deve ter pelo menos 5 caracteres",
            },
            agree: "Você deve concordar com os termos e condições",
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

            // Desabilitar botão submit e habilitar loader
            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            // Cria um objeto FormData a partir do formulário
            var formData = new FormData(form);

            // Adiciona um novo campo
            formData.append("action", "register");

            // Realiza o AJAX para enviar os dados
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/auth/register.php', // Substitua pelo URL do seu endpoint
                type: 'POST',
                data: formData,
                processData: false, // Impede que o jQuery processe os dados
                contentType: false, // Impede que o jQuery defina o Content-Type
                success: function (response) {
                    if (response.status == "success") {
                        // Sucesso na resposta do servidor
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>verificar-email";
                    } else {
                        console.error("Erro no AJAX:", status, error);

                        // Caso contrário, exibe a mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#loginForm").before('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#loginForm").before('<div class="alert alert-danger">Ocorreu um erro, tente novamente mais tarde.</div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>