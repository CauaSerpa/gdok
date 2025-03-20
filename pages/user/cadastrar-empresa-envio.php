<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Cadastrar Empresa de Envio</h4>
        </div>

        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>empresas-envios">Empresas de Envio</a></li>
                <li class="breadcrumb-item active">Cadastrar Empresa de Envios</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Cadastro de Empresas de Envios</h5>
                </div><!-- end card header -->

                <div class="card-body">

                    <form id="companyForm">

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome da Empresa</label>
                            <input class="form-control" name="name" type="text" id="name" maxlength="120" placeholder="Digite o Nome da Empresa" required>
                        </div>

                        <!-- Logo da Empresa -->
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo da Empresa</label>
                            <input class="form-control" name="logo" type="file" id="logo" accept=".jpg,.png,.pdf,.doc,.docx,.xls,.xlsx,.pfx,.p12">
                        </div>

                        <div class="row">

                            <div class="col-md-6">

                                <!-- Login -->
                                <div class="mb-3">
                                    <label for="login" class="form-label">
                                        E-mail para Login da Empresa
                                        <i class="mdi mdi-help-circle text-muted" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Esse será o e-mail usado para acessar o painel da empresa.">
                                        </i>
                                    </label>
                                    <input class="form-control" name="login" type="email" id="login" maxlength="120" placeholder="Digite o E-mail para Login da Empresa" required>
                                </div>

                            </div>

                            <div class="col-md-6">

                                <!-- Responsável -->
                                <div class="mb-3">
                                    <label for="responsible" class="form-label">Responsável</label>
                                    <input class="form-control" name="responsible" type="text" id="responsible" maxlength="100" placeholder="Digite o Nome do Responsável" required>
                                </div>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-5">

                                <!-- Phone -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefone</label>
                                    <input class="form-control" name="phone" type="tel" id="phone" maxlength="15" placeholder="Digite o Telefone" onkeyup="handlePhone(event)" required>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" name="notify_phone" type="checkbox" role="switch" id="notify_phone">
                                        <label class="form-check-label" for="notify_phone">
                                            Notificar cliente via WhatsApp  
                                            <i class="mdi mdi-help-circle text-muted" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="O cliente receberá um aviso no WhatsApp sobre o vencimento dos documentos.">
                                            </i>
                                        </label>
                                    </div>

                                </div>

                            </div>

                            <div class="col-md-7">

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input class="form-control" name="email" type="email" id="email" maxlength="120" placeholder="Digite o E-mail" required>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" name="notify_email" type="checkbox" role="switch" id="notify_email">
                                        <label class="form-check-label" for="notify_email">
                                            Notificar cliente via e-mail  
                                            <i class="mdi mdi-help-circle text-muted" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                data-bs-title="O cliente receberá um aviso por e-mail sobre o vencimento dos documentos.">
                                            </i>
                                        </label>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="d-flex align-items-center justify-content-between">

                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>empresas-envios" class="btn btn-light">Voltar</a>

                            <div>

                                <button class="btn btn-primary" id="btnSubmit" type="submit">Cadastrar</button>
                                <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                    <span role="status">Carregando...</span>
                                </button>

                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adicionar máscaras e validação -->
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    // Verifica o texto inicial ao carregar a página
    verificarTextoUF();
    verificarTextoCidade();

    // Verifica novamente sempre que há uma alteração no Select2
    $('#uf').on('change', function () {
        verificarTextoUF();
    });

    // Verifica novamente sempre que há uma alteração no Select2
    $('#cidade').on('change', function () {
        verificarTextoCidade();
    });

    function verificarTextoUF() {
        const container = $('#select2-uf-container');
        const textoAtual = container.text().trim(); // Remove espaços em branco extras

        if (textoAtual.toLowerCase() !== '' && textoAtual.toLowerCase() !== 'Selecione um estado') {
            $('#uf').addClass("is-valid").removeClass("is-invalid");
        }
    }

    function verificarTextoCidade() {
        const container = $('#select2-cidade-container');
        const textoAtual = container.text().trim(); // Remove espaços em branco extras

        if (textoAtual.toLowerCase() !== '' && textoAtual.toLowerCase() !== 'Selecione uma cidade') {
            $('#cidade').addClass("is-valid").removeClass("is-invalid");
        }
    }
});
</script>

<script>
const handlePhone = (event) => {
    let input = event.target;

    let numericValue = input.value.replace(/\D/g, '');
    input.value = input.value.replace(/\D/g, '').slice(0, 11);

    input.value = phoneMask(input.value);
};

const phoneMask = (value) => {
    if (!value) return "";
    value = value.replace(/\D/g, ''); // Remove caracteres não numéricos
    value = value.replace(/(\d{2})(\d)/, "($1) $2"); // Formata o DDD
    value = value.replace(/(\d)(\d{4})$/, "$1-$2"); // Formata o número
    return value;
};

function handleCpfCnpj(event) {
    var input = event.target;
    var value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

    if (value.length <= 11) {
        $(input).mask('000.000.000-00#####');
    } else {
        $(input).mask('00.000.000/0000-00');
    }
}

$(document).ready(function() {
    $('#uf').select2({
        placeholder: 'Selecione um estado',
        allowClear: true,
    });

    $('#cidade').select2({
        placeholder: 'Selecione uma cidade',
        allowClear: true,
    });

    // Carregar estados (UF) da API IBGE
    $.getJSON('https://servicodados.ibge.gov.br/api/v1/localidades/estados', function (data) {
        var estados = data.sort((a, b) => a.nome.localeCompare(b.nome));
        estados.forEach(function (estado) {
            $('#uf').append(new Option(estado.nome, estado.sigla));
        });
    });

    // Carregar cidades com base na UF selecionada
    $('#uf').on('change', function () {
        var uf = $(this).val();
        $('#cidade').empty().append(new Option('Selecione a Cidade', '', true, true));
        $.getJSON(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`, function (data) {
            data.sort((a, b) => a.nome.localeCompare(b.nome)).forEach(function (cidade) {
                $('#cidade').append(new Option(cidade.nome, cidade.nome));
            });
        });
    });

    // Adicionar validação personalizada para o e-mail
    $.validator.addMethod("documentExists", function(value, element) {
        let isValid = false;
        if (value) {
            $.ajax({
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company-sending/forms-validations/document-exists.php", // URL do script PHP que verifica o e-mail no banco de dados
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
    $("#companyForm").validate({
        rules: {
            login: {
                required: true,
                email: true,
            },
            name: {
                required: true,
                minlength: 2,
            },
            phone: {
                required: true,
                minlength: 14,
            },
            email: {
                required: true,
                email: true,
            },
            responsible: {
                required: true,
                minlength: 2,
            },
            uf: "required",
            cidade: "required",
        },
        messages: {
            email: {
                required: "Por favor, insira um e-mail para o login",
                email: "Por favor, insira um e-mail para o login válido",
            },
            name: {
                required: "Por favor, insira seu nome",
                minlength: "Seu nome deve ter pelo menos 2 caracteres",
            },
            phone: {
                required: "Por favor, insira seu telefone",
                minlength: "Seu telefone deve ter pelo menos 14 caracteres",
            },
            email: {
                required: "Por favor, insira um e-mail",
                email: "Por favor, insira um e-mail válido",
            },
            responsible: {
                required: "Por favor, insira um Responsável",
                minlength: "O nome do responsável deve ter pelo menos 2 caracteres",
            },
            uf: "Por favor, selecione um Estado (UF)",
            cidade: "Por favor, selecione uma cidade",
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.next("label"));
            } else if (element.prop("type") === "select-one") {
                error.insertAfter(element.next("span.select2"));
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
            formData.append("action", "register-company-sending");

            // Realiza o AJAX para enviar os dados
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company-sending/register.php', // Substitua pelo URL do seu endpoint
                type: 'POST',
                data: formData,
                processData: false, // Impede que o jQuery processe os dados
                contentType: false, // Impede que o jQuery defina o Content-Type
                success: function (response) {
                    if (response.status == "success") {
                        // Sucesso na resposta do servidor
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>empresas-envios";
                    } else {
                        // console.error("Erro no AJAX:", status, error);

                        // Caso contrário, exibe a mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#companyForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#companyForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>