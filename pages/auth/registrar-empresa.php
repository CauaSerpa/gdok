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
        $_SESSION['msg'] = array(
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

                        <div class="auth-title-section mb-3 text-center"> 
                            <h3 class="text-dark fs-20 fw-medium mb-2">Cadastro da Empresa</h3>
                            <p class="text-dark text-capitalize fs-14 mb-0">Preencha os dados da empresa para acessar o <?= $project['name']; ?>.</p>
                        </div>

                        <div class="pt-0">
                            <form id="companyForm" class="my-4">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Nome da Empresa</label>
                                    <input class="form-control" name="name" type="text" id="name" placeholder="Digite o Nome da Empresa" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="document" class="form-label">CPF/CNPJ</label>
                                    <input class="form-control" name="document" type="text" id="document" maxlength="18" placeholder="Digite o CPF ou CNPJ da Empresa" onkeyup="handleCpfCnpj(event)" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Telefone</label>
                                    <input class="form-control" name="phone" type="tel" id="phone" maxlength="15" placeholder="Digite o Telefone da Empresa" onkeyup="handlePhone(event)" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">E-mail da Empresa</label>
                                    <input class="form-control" name="email" type="email" id="email" placeholder="Digite o E-mail da Empresa" required>
                                </div>

                                <h4 class="card-title">Endereço da Sede</h4>

                                <div class="form-group mb-3">
                                    <label for="cep" class="form-label">CEP</label>
                                    <input class="form-control" name="cep" type="text" id="cep" placeholder="Digite o CEP" required>
                                </div>

                                <div id="address_fields" class="d-none">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="form-group mb-3">
                                                <label for="address" class="form-label">Endereço</label>
                                                <input class="form-control" name="address" type="text" id="address" placeholder="Digite o Endereço" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group mb-2">
                                                <label for="number" class="form-label">Número</label>
                                                <input class="form-control" name="number" type="text" id="number" placeholder="Digite o Número" required>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" name="noNumber" type="checkbox" id="noNumber">
                                                <label class="form-check-label" for="noNumber">
                                                    Sem número
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label for="province" class="form-label">Bairro</label>
                                                <input class="form-control" name="province" type="text" id="province" placeholder="Digite o Bairro" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label for="complement" class="form-label">Complemento</label>
                                                <input class="form-control" name="complement" type="text" id="complement" placeholder="Digite o Complemento">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-9">
                                            <div class="form-group mb-3">
                                                <label for="city" class="form-label">Cidade</label>
                                                <input class="form-control" name="city" type="text" id="city" placeholder="Digite o Cidade" required>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-group mb-3">
                                                <label for="state" class="form-label">UF</label>
                                                <input class="form-control" name="state" type="text" id="state" placeholder="Digite o UF" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group d-flex mb-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input type="checkbox" name="agree" class="form-check-input" id="agree" required>
                                            <label class="form-check-label" for="agree">Eu concordo com os <a href="#" class="text-primary fw-medium">Termos e Condições</a></label>
                                        </div>
                                    </div>
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
    $('#cep').mask('00000-000');

    function buscarEndereco(cep) {
        cep = cep.replace(/\D/g, '');
        if (cep.length === 8) {
            $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                if (!data.erro) {
                    $('#address').val(data.logradouro);
                    $('#province').val(data.bairro);
                    $('#city').val(data.localidade);
                    $('#state').val(data.uf);
                    $('#address_fields').removeClass('d-none');
                    $('#cep').removeClass("is-invalid");
                    $('#cepError').remove();
                } else {
                    // Remove o erro se os campos forem corrigidos
                    $('#cep').addClass("is-invalid").removeClass("is-valid");
                    $('#cepError').remove();
                    $('<em id="cepError" class="error invalid-feedback">CEP não encontrado. Verifique o CEP digitado.</em>')
                        .insertAfter('#cep').show(); // Adiciona a mensagem de erro após o campo de data de término
                    $('#address_fields').removeClass('d-none');
                }
            }).fail(function() {
                // Remove o erro se os campos forem corrigidos
                $('#cep').addClass("is-invalid").removeClass("is-valid");
                $('#cepError').remove();
                $('<em id="cepError" class="error invalid-feedback">Erro ao buscar o CEP. Tente novamente.</em>')
                    .insertAfter('#cep').show(); // Adiciona a mensagem de erro após o campo de data de término
                $('#address_fields').removeClass('d-none');
            });
        } else {
            // Remove o erro se os campos forem corrigidos
            $('#cep').addClass("is-invalid").removeClass("is-valid");
            $('#cepError').remove();
            $('<em id="cepError" class="error invalid-feedback">CEP inválido. Digite um CEP com 8 dígitos.</em>')
                .insertAfter('#cep').show(); // Adiciona a mensagem de erro após o campo de data de término
        }
    }

    $('#cep').on('blur', function() {
        const cep = $(this).val();
        if (cep) {
            buscarEndereco(cep);
        }
    });

    $('#cep').on('keyup', function() {
        const cep = $(this).val().replace(/\D/g, '');
        if (cep.length === 8) {
            $(this).val(cep.replace(/(\d{5})(\d{3})/, '$1-$2'));
        }
    });

    $("#noNumber").change(function () {
        if ($(this).is(":checked")) {
            $("#number").val("0").prop("disabled", true);
            $("#number").rules("remove", "required");
        } else {
            $("#number").prop("disabled", false);
            $("#number").rules("add", {
                required: true,
                messages: {
                    required: "Por favor, insira o número"
                }
            });
        }
    });

    // Adicionar validação personalizada para o e-mail
    $.validator.addMethod("emailExists", function(value, element) {
        let isValid = false;
        if (value) {
            $.ajax({
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/forms-validations/email-exists.php", // URL do script PHP que verifica o e-mail no banco de dados
                type: "POST",
                data: { action: 'company-email-exists', email: value },
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
                data: { action: 'company-document-exists', document: value },
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
    $("#companyForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
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
            email: {
                required: true,
                email: true,
                emailExists: true,
            },
            cep: {
                required: true,
                minlength: 9,
            },
            address: {
                required: true,
                minlength: 2,
            },
            number: "required",
            province: {
                required: true,
                minlength: 2,
            },
            city: {
                required: true,
                minlength: 2,
            },
            state: {
                required: true,
                minlength: 2,
            },
            agree: "required",
        },
        messages: {
            name: {
                required: "Por favor, insira seu nome",
                minlength: "Seu nome deve ter pelo menos 2 caracteres",
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
            email: {
                required: "Por favor, insira seu e-mail",
                email: "Por favor, insira um e-mail válido",
                emailExists: "Este e-mail já está cadastrado.",
            },
            cep: {
                required: "Por favor, insira o CEP",
                minlength: "O CEP deve ter pelo menos 9 caracteres",
            },
            address: {
                required: "Por favor, insira o endereço",
                minlength: "O endereço deve ter pelo menos 2 caracteres",
            },
            number: "Por favor, insira o número",
            province: {
                required: "Por favor, insira o Bairro",
                minlength: "O bairro deve ter pelo menos 2 caracteres",
            },
            city: {
                required: "Por favor, insira a cidade",
                minlength: "A cidade deve ter pelo menos 2 caracteres",
            },
            state: {
                required: "Por favor, insira o UF",
                minlength: "O UF deve ter pelo menos 2 caracteres",
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
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/auth/register-company.php', // Substitua pelo URL do seu endpoint
                type: 'POST',
                data: formData,
                processData: false, // Impede que o jQuery processe os dados
                contentType: false, // Impede que o jQuery defina o Content-Type
                success: function(response) {
                    if (response.status == "success") {
                        // Sucesso na resposta do servidor
                        window.location.href = "<?= INCLUDE_PATH_AUTH; ?>";
                    } else {
                        console.error("Erro no AJAX:", status, xhr.responseText);

                        // Caso contrário, exibe a mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#companyForm").before('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function(xhr, status, errorThrown) {
                    console.error("Erro no AJAX:", status, xhr.responseText);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#companyForm").before('<div class="alert alert-danger">Ocorreu um erro, tente novamente mais tarde.</div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>