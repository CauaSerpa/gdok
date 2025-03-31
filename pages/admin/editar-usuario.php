<?php
    // Verificar se o token foi fornecido
    if (!isset($token) || empty($token)) {
        // Redirecionar para a página de tipos de documentos com uma mensagem de erro
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Token inválido ou ausente. Não foi possível carregar o usuário.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'usuarios');
        exit;
    }

    include('back-end/admin/user/functions.php');

    // Validar o token (por exemplo, verificar se existe no banco de dados)
    $user_id = $token;
    $user = getUserByToken($user_id, $conn); // Suponha que essa função pegue a categoria pelo token

    // Se não encontrar a categoria com o token, redirecionar
    if (!$user) {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Usuário não encontrado.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'usuarios');
        exit;
    }
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Editar Usuário / <?= $user['firstname']; ?></h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>usuarios">Usuários</a></li>
                <li class="breadcrumb-item active">Editar Plano</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body pt-0">
            <ul class="nav nav-underline border-bottom pt-2" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active p-2" id="setting_tab" data-bs-toggle="tab" href="#profile_setting" role="tab">
                        <span class="d-block d-sm-none"><i class="mdi mdi-information"></i></span>
                        <span class="d-none d-sm-block">Edição do Perfil</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link p-2" id="profile_security_tab" data-bs-toggle="tab" href="#profile_security" role="tab">
                        <span class="d-block d-sm-none"><i class="mdi mdi-information"></i></span>
                        <span class="d-none d-sm-block">Edição de Login</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content text-muted bg-white">

                <div class="tab-pane active show pt-4" id="profile_setting" role="tabpanel">
                    <div class="row">

                        <div class="row">
                            <div class="col-lg-6 col-xl-6">
                                <div class="card border mb-0">

                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h4 class="card-title mb-0">Informações pessoais</h4>
                                            </div><!--end col-->
                                        </div>
                                    </div>

                                    <div class="card-body">

                                        <form id="profileForm">

                                            <div class="row">

                                                <div class="col-md-6">

                                                    <div class="form-group mb-3 row">
                                                        <label class="form-label">Primeiro Nome</label>
                                                        <div class="col-lg-12 col-xl-12">
                                                            <input class="form-control" name="firstname" type="text" id="firstname" value="<?= $user['firstname']; ?>" maxlength="120" placeholder="Digite seu Primeiro Nome" required>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-6">

                                                    <div class="form-group mb-3 row">
                                                        <label class="form-label">Sobrenome</label>
                                                        <div class="col-lg-12 col-xl-12">
                                                            <input class="form-control" name="lastname" type="text" id="lastname" value="<?= $user['lastname']; ?>" maxlength="120" placeholder="Digite seu Sobrenome" required>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>


                                            <div class="row">

                                                <div class="col-md-7">

                                                    <div class="form-group mb-3 row">
                                                        <label class="form-label">E-mail</label>
                                                        <div class="col-lg-12 col-xl-12">
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                                                <input class="form-control" name="email" type="email" id="email" value="<?= $user['email']; ?>" placeholder="Digite seu E-mail" disabled readonly>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-5">

                                                    <div class="form-group mb-3 row">
                                                        <label class="form-label">CPF/CNPJ</label>
                                                        <div class="col-lg-12 col-xl-12">
                                                            <input class="form-control" name="document" type="text" id="document" value="<?= $user['document']; ?>" maxlength="18" placeholder="Digite seu CPF ou CNPJ" onkeyup="handleCpfCnpj(event)" required>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="row">

                                                <div class="col-md-5">

                                                    <div class="form-group mb-3 row">
                                                        <label class="form-label">Telefone</label>
                                                        <div class="col-lg-12 col-xl-12">
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="mdi mdi-phone-outline"></i></span>
                                                                <input class="form-control" name="phone" type="tel" id="phone" value="<?= $user['phone']; ?>" maxlength="15" placeholder="Digite seu Telefone" onkeyup="handlePhone(event)" required>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="form-group row">
                                                <div class="col-lg-12 col-xl-12">
                                                    <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar</button>
                                                    <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                        <span role="status">Carregando...</span>
                                                    </button>
                                                </div>
                                            </div>

                                        </form>

                                    </div><!--end card-body-->
                                </div>
                            </div>

                        </div>
                    </div>
                </div> <!-- end profile setting -->

                <div class="tab-pane pt-4" id="profile_security" role="tabpanel">
                    <div class="row">

                        <div class="col-lg-6 col-xl-6 mb-3">
                            <div class="card border mb-0">

                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="card-title mb-0">Alterar Senha</h4>
                                        </div><!--end col-->
                                    </div>
                                </div>

                                <div class="card-body mb-0">

                                    <form id="passwordForm">

                                        <div class="form-group mb-3 row">
                                            <label for="new_password" class="form-label">Nova Senha</label>
                                            <div class="col-lg-12 col-xl-6">
                                                <input class="form-control" name="new_password" type="password" id="new_password" placeholder="Digite sua Nova Senha" required>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3 row">
                                            <label for="confirm_password" class="form-label">Confirmar Senha</label>
                                            <div class="col-lg-12 col-xl-6">
                                                <input class="form-control" name="confirm_password" type="password" id="confirm_password" placeholder="Confirme sua Nova Senha" required>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-12 col-xl-6">
                                                <button class="btn btn-primary" id="btnSubmit" type="submit">Alterar Senha</button>
                                                <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                    <span role="status">Carregando...</span>
                                                </button>
                                            </div>
                                        </div>

                                    </form>

                                </div><!--end card-body-->
                            </div>
                        </div>

                        <div class="col-lg-6 col-xl-6 mb-3">
                            <div class="card border mb-0">

                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="card-title mb-0"><?= ($user['active_status'] == 0 && !empty($user['active_token'])) ? "Validar E-mail" : "Alterar E-mail"; ?></h4>
                                        </div><!--end col-->
                                    </div>
                                </div>

                                <div class="card-body mb-0">

                                    <form id="updateEmailForm">

                                        <div class="form-group mb-3 row">
                                            <label class="form-label">Alterar E-mail</label>
                                            <div class="col-lg-12 col-xl-12">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-email-edit"></i></span>
                                                    <input class="form-control" name="new_email" type="email" id="new_email" placeholder="Digite o Novo E-mail" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-12 col-xl-12">
                                                <button class="btn btn-primary" id="btnSubmit" type="submit">Alterar E-mail</button>
                                                <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                    <span role="status">Carregando...</span>
                                                </button>
                                            </div>
                                        </div>

                                    </form>

                                </div><!--end card-body-->
                            </div>
                        </div>

                        <div class="col-lg-6 col-xl-6 mb-3">
                            <div class="card border mb-0">

                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="card-title mb-0">Bloquear Usuário</h4>
                                        </div><!--end col-->
                                    </div>
                                </div>

                                <div class="card-body mb-0">

                                    <form id="blockUserForm">

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" name="block_user" type="checkbox" id="block_user" role="switch" <?= ($user['status'] == 0) ? "checked" : ""; ?>>
                                            <label for="block_user" class="form-label">Bloquear usuário</label>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-12 col-xl-6">
                                                <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar</button>
                                                <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                    <span role="status">Carregando...</span>
                                                </button>
                                            </div>
                                        </div>

                                    </form>

                                </div><!--end card-body-->
                            </div>
                        </div>

                    </div>
                </div> <!-- end security -->

            </div> <!-- Tab panes -->
        </div>
    </div>
</div>

<!-- Adicionar máscaras e validação -->
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>

<!-- Formulario para editar perfil -->
<script>
function handlePhone(event) {
    var input = event.target;
    var value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

    if (value.length <= 10) {
        $(input).mask('(00) 0000-0000#');
    } else {
        $(input).mask('(00) 00000-0000');
    }
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
    // Adicionar validação personalizada para o e-mail
    $.validator.addMethod("documentExists", function(value, element) {
        let isValid = false;
        if (value) {
            $.ajax({
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/settings/profile/forms-validations/document-exists.php",
                type: "POST",
                data: { action: 'document-exists', document: value, user_id: <?= $user['id']; ?> },
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
    $("#profileForm").validate({
        rules: {
            firstname: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
            lastname: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
            document: {
                required: true,
                minlength: 14,
                cpfcnpj: true,
                documentExists: true,
            },
            phone: {
                required: true,
                minlength: 14,
            },
        },
        messages: {
            firstname: {
                required: "Por favor, insira seu primeiro nome.",
                minlength: "Seu primeiro nome deve ter pelo menos 2 caracteres.",
                maxlength: "Seu primeiro nome pode ter no máximo 120 caracteres.",
            },
            lastname: {
                required: "Por favor, insira seu primeiro nome.",
                minlength: "Seu primeiro nome deve ter pelo menos 2 caracteres.",
                maxlength: "Seu primeiro nome pode ter no máximo 120 caracteres.",
            },
            document: {
                required: "Por favor, insira seu CPF/CNPJ",
                minlength: "Seu documento deve ter pelo menos 14 caracteres",
                cpfcnpj: "Por favor, insira um CPF ou CNPJ válido",
                documentExists: "Este CPF/CNPJ já está cadastrado.",
            },
            phone: {
                required: "Por favor, insira seu telefone",
                minlength: "Seu telefone deve ter pelo menos 14 caracteres",
            },
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.prop("type") === "radio") {
                error.insertAfter(element.closest(".options"));
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
        submitHandler: function (form) {
            event.preventDefault();

            var btnSubmit = $("#profileForm #btnSubmit");
            var btnLoader = $("#profileForm #btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("user", <?= $user['id']; ?>);
            formData.append("action", "update-profile");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/user/profile/update.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        location.reload();
                    } else {
                        $(".alert").remove();
                        $("#profileForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#profileForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>

<!-- Formulario para alterar senha -->
<script>
    $(document).ready(function () {
        // Validação do Formulário
        $("#passwordForm").validate({
            rules: {
                new_password: {
                    required: true,
                    minlength: 5
                },
                confirm_password: {
                    required: true,
                    equalTo: "#new_password"
                }
            },
            messages: {
                new_password: {
                    required: "Por favor, insira sua nova senha.",
                    minlength: "A nova senha deve ter pelo menos 5 caracteres."
                },
                confirm_password: {
                    required: "Por favor, confirme sua nova senha.",
                    equalTo: "A confirmação da senha deve ser igual à nova senha."
                }
            },
            errorElement: "em",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).addClass("is-valid").removeClass("is-invalid");
            },
            submitHandler: function (form) {
                event.preventDefault();

                var btnSubmit = $("#passwordForm #btnSubmit");
                var btnLoader = $("#passwordForm #btnLoader");

                btnSubmit.prop("disabled", true).addClass("d-none");
                btnLoader.removeClass("d-none");

                var formData = new FormData(form);
                formData.append("user", <?= $user['id']; ?>);
                formData.append("action", "update-password");

                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/user/security/password/update.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === "success") {
                            location.reload();
                        } else {
                            $(".alert").remove();
                            $("#passwordForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        }
                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro no AJAX:", status, error);

                        $(".alert").remove();
                        $("#passwordForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Validação e envio do formulário
        $("#updateEmailForm").validate({
            rules: {
                new_email: { required: true, email: true },
            },
            messages: {
                new_email: { required: "Por favor, insira seu e-mail.", email: "Insira um e-mail válido." },
            },
            errorElement: "em",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).addClass("is-valid").removeClass("is-invalid");
            },
            submitHandler: function (form, event) {
                event.preventDefault();

                const btnSubmit = $("#updateEmailForm #btnSubmit");
                const btnLoader = $("#updateEmailForm #btnLoader");
                const formData = new FormData(form);

                btnSubmit.prop("disabled", true).addClass("d-none");
                btnLoader.removeClass("d-none");

                formData.append("user", <?= $user['id']; ?>);
                formData.append("action", "update-email");

                $.ajax({
                    url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/user/security/email/update-email.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === "success") {
                            location.reload();
                        } else {
                            $(".alert").remove();
                            $("#updateEmailForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        }
                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    },
                    error: function () {
                        showAlert("danger", "Ocorreu um erro, tente novamente mais tarde.");
                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    },
                });
            },
        });
    });
</script>

<script>
$(document).ready(function () {
    // Validação do formulário (aqui não há regras específicas, mas podemos validar se necessário)
    $("#blockUserForm").validate({
        submitHandler: function (form, event) {
            event.preventDefault();

            var btnSubmit = $("#blockUserForm #btnSubmit");
            var btnLoader = $("#blockUserForm #btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            // Coleta os dados do formulário
            var formData = new FormData(form);
            // Supondo que a variável $user['id'] contém o ID do usuário (ajuste conforme necessário)
            formData.append("user", <?= $user['id']; ?>);
            formData.append("action", "block-user");

            // Se o checkbox estiver marcado, define status = 0 (bloqueado)
            // Caso contrário, define status = 1 (ou outro valor representando "ativo")
            var status = $("#block_user").is(":checked") ? 0 : 1;
            formData.append("status", status);

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/user/security/block/block.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        $(".alert").remove();
                        $("#blockUserForm").before('<div class="alert alert-primary alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    } else {
                        $(".alert").remove();
                        $("#blockUserForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function () {
                    showAlert("danger", "Ocorreu um erro, tente novamente mais tarde.");
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
            });
        }
    });
});
</script>