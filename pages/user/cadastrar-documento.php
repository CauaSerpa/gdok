<?php
    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar tipos de documentos cadastradas
    $stmt = $conn->prepare("SELECT id, name, advance_notification, personalized_advance_notification FROM tb_document_types WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $document_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .line-height {
        height: 20px;
        margin: 8px 0;
        width: 1px;
        background: black;
    }
</style>

<!-- Modal para Cadastro de Empresa (fica aberto sobre o modal de upload) -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="companyForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companyModalLabel">Cadastrar Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Nome da Empresa -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nome da Empresa*</label>
                    <input class="form-control" name="name" type="text" id="name" maxlength="120" placeholder="Digite o Nome da Empresa" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <!-- CPF/CNPJ -->
                        <div class="mb-3">
                            <label for="document" class="form-label">CPF/CNPJ*</label>
                            <input class="form-control" name="document" type="text" id="document" placeholder="Digite o CPF ou CNPJ" onkeyup="handleCpfCnpj(event)" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Responsável -->
                        <div class="mb-3">
                            <label for="responsible" class="form-label">Responsável*</label>
                            <input class="form-control" name="responsible" type="text" id="responsible" maxlength="100" placeholder="Digite o Nome do Responsável" required>
                        </div>
                    </div>
                </div>
                <!-- Telefone e E-mail -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefone*</label>
                    <input class="form-control" name="phone" type="tel" id="phone" maxlength="15" placeholder="Digite o Telefone" onkeyup="handlePhone(event)" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail*</label>
                    <input class="form-control" name="email" type="email" id="email" maxlength="120" placeholder="Digite o E-mail" required>
                </div>
                <!-- Estado (UF) e Cidade -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-grid">
                            <label for="uf" class="form-label">Estado (UF)*</label>
                            <select class="form-select" name="uf" id="uf" required>
                                <option value="" selected disabled>Selecione um estado</option>
                                <!-- Opções carregadas dinamicamente -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid">
                            <label for="cidade" class="form-label">Cidade*</label>
                            <select class="form-select" name="cidade" id="cidade" required>
                                <option value="" selected disabled>Selecione uma cidade</option>
                                <!-- Opções carregadas dinamicamente -->
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <!-- Note que este modal é apenas para cadastro, sem interferir no modal de upload -->
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Upload Documento</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos">Documentos</a></li>
                <li class="breadcrumb-item active">Upload Documento</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Upload de Documento</h5>
                </div><!-- end card header -->

                <div class="card-body">

                    <form id="documentForm">

                        <div class="row">

                            <div class="col-md-6">

                                <!-- Empresa -->
                                <div class="mb-3">
                                    <label for="company" class="form-label">Empresa*</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select w-50" name="company" id="company" required>
                                            <option value="" selected disabled>Selecione uma Empresa</option>
                                            <?php foreach ($companies as $company): ?>
                                                <option value="<?= htmlspecialchars($company['id']); ?>">
                                                    <?= htmlspecialchars($company['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <!-- <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-empresa" class="btn btn-link" onclick="return confirm('Você será redirecionado para a página de cadastro de empresa. Deseja continuar?');">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Empresa
                                        </a> -->
                                        <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#companyModal">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Empresa
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6">

                                <!-- Tipo do Documento -->
                                <div class="mb-3">
                                    <label for="document_type" class="form-label">Tipo do Documento*</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select w-50" name="document_type" id="document_type" required>
                                            <option value="" selected disabled>Selecione o Tipo do Documento</option>
                                            <?php foreach ($document_types as $document_type): ?>
                                                <option value="<?= htmlspecialchars($document_type['id']); ?>" 
                                                        data-advance-notification="<?= htmlspecialchars($document_type['advance_notification']); ?>"
                                                        data-personalized-advance-notification="<?= htmlspecialchars($document_type['personalized_advance_notification']); ?>">
                                                    <?= htmlspecialchars($document_type['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-tipo-documento" class="btn btn-link" onclick="return confirm('Você será redirecionado para a página de cadastro de tipo de documento. Deseja continuar?')">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Tipo de Documento
                                        </a>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Upload de Documento -->
                        <div class="mb-3">
                            <label for="document" class="form-label">Upload de Documento</label>
                            <input class="form-control" name="document" type="file" id="document" accept=".jpg,.png,.pdf,.doc,.docx,.xls,.xlsx,.pfx,.p12">
                        </div>

                        <div class="row">

                            <div class="col-md-6">

                                <!-- Nome do Documento -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome do Documento</label>
                                    <input class="form-control" name="name" type="text" id="name" maxlength="120" placeholder="Digite o Nome do Documento">
                                </div>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-3">

                                <div class="mb-3">
                                    <label for="expiration_date" class="form-label">Data de Validade*</label>
                                    <input class="form-control" name="expiration_date" type="date" id="expiration_date">
                                </div>

                            </div>

                            <div class="col-md-9">

                                <!-- Notificação Antecipada -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        Notificação Antecipada
                                        <i class="mdi mdi-help-circle text-muted" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Por padrão, será usada a configuração de notificação antecipada do tipo do documento.">
                                        </i>
                                    </label>

                                    <div class="radio-options d-flex">
                                        <div class="options">
                                            <input type="radio" class="btn-check" name="advance_notification" id="option1" autocomplete="off" value="7">
                                            <label class="btn" for="option1">7 dias</label>

                                            <input type="radio" class="btn-check" name="advance_notification" id="option2" autocomplete="off" value="15">
                                            <label class="btn" for="option2">15 dias</label>

                                            <input type="radio" class="btn-check" name="advance_notification" id="option3" autocomplete="off" value="30">
                                            <label class="btn" for="option3">30 dias</label>

                                            <input type="radio" class="btn-check" name="advance_notification" id="option4" autocomplete="off" value="45">
                                            <label class="btn" for="option4">45 dias</label>

                                            <input type="radio" class="btn-check" name="advance_notification" id="option5" autocomplete="off" value="90">
                                            <label class="btn" for="option5">90 dias</label>

                                            <input type="radio" class="btn-check" name="advance_notification" id="option6" autocomplete="off" value="personalized">
                                            <label class="btn" for="option6">Personalizado</label>

                                            <div id="personalized-input" class="mt-2" style="display: none;">
                                                <label for="personalized_days" class="form-label">Notificação Personalizada (dias):</label>
                                                <input type="number" class="form-control" id="personalized_days" name="personalized_days" placeholder="Informe o número de dias">
                                            </div>
                                        </div>

                                        <div id="advanceNotificationCustomize" class="d-none">
                                            <span class="line-height mx-2"></span>
                                            <div class="position-relative">
                                                <input class="form-control" name="personalized_advance_notification" type="number" id="personalized_advance_notification" placeholder="Digite o Período" style="width: 150px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Observação -->
                            <div class="mb-3">
                                <label for="observation" class="form-label">Observação</label>
                                <textarea class="form-control" name="observation" id="observation" rows="5" spellcheck="false"></textarea>
                            </div>

                        </div>

                        <div class="d-flex align-items-center justify-content-between">

                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos" class="btn btn-light">Voltar</a>

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
    $('#companyForm #uf').on('change', function () {
        verificarTextoUF();
    });

    // Verifica novamente sempre que há uma alteração no Select2
    $('#companyForm #cidade').on('change', function () {
        verificarTextoCidade();
    });

    function verificarTextoUF() {
        const container = $('#select2-uf-container');
        const textoAtual = container.text().trim(); // Remove espaços em branco extras

        if (textoAtual.toLowerCase() !== '' && textoAtual.toLowerCase() !== 'Selecione um estado') {
            $('#companyForm #uf').addClass("is-valid").removeClass("is-invalid");
        }
    }

    function verificarTextoCidade() {
        const container = $('#select2-cidade-container');
        const textoAtual = container.text().trim(); // Remove espaços em branco extras

        if (textoAtual.toLowerCase() !== '' && textoAtual.toLowerCase() !== 'Selecione uma cidade') {
            $('#companyForm #cidade').addClass("is-valid").removeClass("is-invalid");
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
        $('#companyForm #uf').select2({
            dropdownParent: $("#companyForm"),
            placeholder: 'Selecione um estado',
        });

        $('#companyForm #cidade').select2({
            dropdownParent: $("#companyForm"),
            placeholder: 'Selecione uma cidade',
        });

        // Carregar estados (UF) da API IBGE
        $.getJSON('https://servicodados.ibge.gov.br/api/v1/localidades/estados', function (data) {
            var estados = data.sort((a, b) => a.nome.localeCompare(b.nome));
            estados.forEach(function (estado) {
                $('#uf').append(new Option(estado.nome, estado.sigla));
            });
        });

        // Carregar cidades com base na UF selecionada
        $('#companyForm #uf').on('change', function () {
            var uf = $(this).val();
            $('#companyForm #cidade').empty().append(new Option('Selecione a Cidade', '', true, true));
            $.getJSON(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`, function (data) {
                data.sort((a, b) => a.nome.localeCompare(b.nome)).forEach(function (cidade) {
                    $('#companyForm #cidade').append(new Option(cidade.nome, cidade.nome));
                });
            });
        });

        // Adicionar validação personalizada para o e-mail
        $.validator.addMethod("documentExists", function(value, element) {
            let isValid = false;
            if (value) {
                $.ajax({
                    url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company/forms-validations/document-exists.php", // URL do script PHP que verifica o e-mail no banco de dados
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
                document: {
                    required: true,
                    minlength: 14,
                    cpfcnpj: true,
                    documentExists: true,
                },
                uf: "required",
                cidade: "required",
            },
            messages: {
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
                document: {
                    required: "Por favor, insira seu CPF/CNPJ",
                    minlength: "Seu documento deve ter pelo menos 14 caracteres",
                    cpfcnpj: "Por favor, insira um CPF ou CNPJ válido",
                    documentExists: "Este CPF/CNPJ já está cadastrado.",
                },
                uf: "Por favor, selecione um Estado (UF)",
                cidade: "Por favor, selecione uma cidade",
            },
            errorElement: "em",
            errorPlacement: function (error, element) {
                console.log(element.prop("type"));
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
                var btnSubmit = $("#companyForm #btnSubmit");
                var btnLoader = $("#companyForm #btnLoader");

                // Desabilitar botão submit e habilitar loader
                btnSubmit.prop("disabled", true).addClass("d-none");
                btnLoader.removeClass("d-none");

                // Cria um objeto FormData a partir do formulário
                var formData = new FormData(form);

                // Adiciona um novo campo
                formData.append("action", "register-company-modal");

                // Realiza o AJAX para enviar os dados
                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company/register.php', // Substitua pelo URL do seu endpoint
                    type: 'POST',
                    data: formData,
                    processData: false, // Impede que o jQuery processe os dados
                    contentType: false, // Impede que o jQuery defina o Content-Type
                    success: function (response) {
                        if (response.status == "success") {
                            // Adiciona a nova empresa ao select do modal de upload e define-a como selecionada
                            $('#company').append(new Option(response.company.name, response.company.id, true, true));
                            $('#companyModal').modal('hide');

                            // Caso contrário, exibe a mensagem de erro
                            $("#documentForm .alert").remove(); // Remove qualquer mensagem de erro anterior
                            $("#documentForm").before('<div class="alert alert-primary alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        } else {
                            // console.error("Erro no AJAX:", status, error);

                            // Caso contrário, exibe a mensagem de erro
                            $("#companyForm .alert").remove(); // Remove qualquer mensagem de erro anterior
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

<script>
$(document).ready(function () {
    $('#document_type').on('change', function () {
        // Obtém os valores do tipo de documento selecionado
        const advanceNotification = $(this).find(':selected').data('advance-notification');
        const personalizedAdvanceNotification = $(this).find(':selected').data('personalized-advance-notification');

        // Desmarca todos os botões de rádio
        $('input[name="advance_notification"]').prop('checked', false);

        if (advanceNotification === "personalized") {
            // Marca o botão "Personalizado" e exibe o campo de entrada
            $('#option6').prop('checked', true);
            $('#advanceNotificationCustomize').removeClass('d-none').addClass('d-flex');
            $(`input[name="personalized_advance_notification"]`).val(personalizedAdvanceNotification || ''); // Preenche o valor personalizado, se existir
        } else {
            // Marca o botão correspondente e oculta o campo de entrada
            $('#advanceNotificationCustomize').removeClass('d-flex').addClass('d-none');
            $(`input[name="advance_notification"][value="${advanceNotification}"]`).prop('checked', true);
        }
    });
});
</script>

<script>
    $(document).ready(function () {
        // Adiciona um valor ao campo hidden quando a opção personalizada for escolhida
        $('input[name="advance_notification"]').on('change', function () {
            if ($('#option6').is(':checked')) {
                $("#personalized_advance_notification").val("");
                $('#advanceNotificationCustomize').removeClass('d-none').addClass('d-flex');
            } else {
                $('#advanceNotificationCustomize').removeClass('d-flex').addClass('d-none');
                // Define um valor padrão para o campo quando não for personalizado
                $('input[name="advance_notification"]').val(this.value);
            }
        });
    });
</script>

<script>
$(document).ready(function() {
    $('#company').select2({
        placeholder: 'Selecione uma Empresa',
        allowClear: true,
    });

    // Validação do Formulário
    $("#documentForm").validate({
        rules: {
            company: {
                required: true,
            },
            document_type: {
                required: true,
            },
            name: {
                minlength: 1,
                maxlength: 120,
            },
            expiration_date: {
                required: true,
            },
            personalized_advance_notification: {
                required: function () {
                    return $("#option6").is(":checked");
                },
                min: 1,
            },
        },
        messages: {
            company: {
                required: "Por favor, selecione uma empresa.",
            },
            document_type: {
                required: "Por favor, selecione um tipo de documento.",
            },
            name: {
                minlength: "O nome deve ter pelo menos 2 caracteres.",
                maxlength: "O nome pode ter no máximo 120 caracteres.",
            },
            expiration_date: {
                required: "Por favor, selecione uma prioridade.",
            },
            personalized_advance_notification: {
                min: "O valor deve ser no mínimo 1.",
            },
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            console.log(element.prop("id"));
            if (element.prop("type") === "radio") {
                error.insertAfter(element.closest(".radio-options"));
            } else if (element.prop("type") === "select-one") {
                error.insertAfter(element.closest("div"));
            } else if (element.prop("type") === "select-one") {
                error.insertAfter(element);
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
        submitHandler: function (form, event) {
            event.preventDefault();

            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "register-document");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/register.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>documentos";
                    } else {
                        $(".alert").remove();
                        $("#documentTypeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#documentTypeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>