<?php
    $stmt = $conn->prepare("
        SELECT o.*, oa.id AS office_address_id, oa.*, ou.role
        FROM tb_users u
        JOIN tb_office_users ou ON ou.user_id = u.id
        JOIN tb_offices o ON o.id = ou.office_id
        JOIN tb_office_addresses oa ON o.id = oa.office_id AND type = 'headquarters'
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $office = $stmt->fetch(PDO::FETCH_ASSOC);

    $role = $office['role']; // Pega a função do usuário
    $isOwner = ($role === 'owner');
    $isManager = ($role === 'manager');
    $isEmployee = ($role === 'employee');

    // Define atributos com base no nível de acesso
    $readonly = $isEmployee ? 'readonly disabled' : ''; // Employee só pode ler
    $readonlyManager = ($isManager || $isEmployee) ? 'readonly disabled' : ''; // Manager e Employee não podem mudar email/CNPJ
?>

<div class="tab-pane pt-4" id="company_settings" role="tabpanel">
    <div class="row">

        <div class="row">
            <div class="col-lg-6 col-xl-6">
                <div class="card border">

                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title mb-0">Informações da Empresa</h4>
                            </div><!--end col-->
                        </div>
                    </div>

                    <div class="card-body">

                        <form id="officeForm">

                            <div class="form-group mb-3 row">
                                <label class="form-label">Nome da Empresa</label>
                                <div class="col-lg-12 col-xl-12">
                                    <input class="form-control" name="name" type="text" id="name" value="<?= $office['name']; ?>" maxlength="120" placeholder="Digite o Nome da Empresa" required <?= $readonly; ?>>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group mb-3 row">
                                        <label class="form-label">E-mail da Empresa</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                                <input class="form-control" name="email" type="email" id="email" value="<?= $office['email']; ?>" placeholder="Digite o E-mail da Empresa" <?= $readonlyManager; ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group mb-3 row">
                                        <label class="form-label">CPF/CNPJ</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <input class="form-control" name="document" type="text" id="document" value="<?= $office['document']; ?>" maxlength="18" placeholder="Digite o CPF ou CNPJ da Empresa" onkeyup="handleCpfCnpj(event)" required <?= $readonlyManager; ?>>
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
                                                <input class="form-control" name="phone" type="tel" id="phone" value="<?= $office['phone']; ?>" maxlength="15" placeholder="Digite o Telefone da Empresa" onkeyup="handlePhone(event)" required <?= $readonly; ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!$isEmployee): // Esconde o botão para Employees ?>
                                <div class="form-group row">
                                    <div class="col-lg-12 col-xl-12">
                                        <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar</button>
                                        <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                            <span role="status">Carregando...</span>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </form>

                    </div><!--end card-body-->
                </div>
            </div>

            <div class="col-lg-6 col-xl-6">
                <div class="card border">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title mb-0">Endereço</h4>
                            </div><!--end col-->
                        </div>
                    </div>

                    <div class="card-body">

                        <?php
                            function formatAddress($office) {
                                $addressParts = [];

                                if (!empty($office['address'])) {
                                    $addressParts[] = $office['address'];
                                }

                                if (!empty($office['number'])) {
                                    $addressParts[] = ", " . $office['number'];
                                }

                                if (!empty($office['province'])) {
                                    $addressParts[] = "- " . $office['province'];
                                }

                                if (!empty($office['cep'])) {
                                    $addressParts[] = "- " . $office['cep'];
                                }

                                if (!empty($office['city'])) {
                                    $addressParts[] = "- " . $office['city'];
                                }

                                if (!empty($office['state'])) {
                                    $addressParts[] = "/" . $office['state'];
                                }

                                return implode(' ', array_filter($addressParts, fn($value) => !empty(trim($value))));
                            }
                        ?>

                        <!-- <div class="d-flex justify-content-center">
                            <div id="map" style="width: 600px; height: 400px;"></div>
                        </div> -->
                        <!-- Endereço da Sede -->
                        <div class="text-end">
                            <p><strong>Endereço:</strong> <?= formatAddress($office); ?></p>
                        </div>

                        <style>
                            .jvm-tooltip {
                                background-color: #287F71 !important;
                            }
                        </style>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css">
                        <script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/js/jsvectormap.min.js"></script>
                        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/map/brasil.js"></script> <!-- Arquivo do mapa do Brasil -->

                        <script>
                            const estados = <?php echo json_encode([$office['state']]); ?>; // Converte o array PHP para JavaScript

                            const markers = [
                                { uf: 'AC', name: "Acre", coords: [74.5, 24] },
                                { uf: 'AL', name: "Alagoas", coords: [74.3, 175] },
                                { uf: 'AP', name: "Amapá", coords: [80.5, 106] },
                                { uf: 'AM', name: "Amazonas", coords: [77, 50] },
                                { uf: 'BA', name: "Bahia", coords: [72, 150] },
                                { uf: 'CE', name: "Ceará", coords: [77.5, 162] },
                                { uf: 'DF', name: "Distrito Federal", coords: [69.4, 125.3] },
                                { uf: 'ES', name: "Espírito Santo", coords: [65, 157] },
                                { uf: 'GO', name: "Goiás", coords: [68, 116] },
                                { uf: 'MA', name: "Maranhão", coords: [77, 135] },
                                { uf: 'MT', name: "Mato Grosso", coords: [71, 90] },
                                { uf: 'MS', name: "Mato Grosso do Sul", coords: [65, 94] },
                                { uf: 'MG', name: "Minas Gerais", coords: [66, 140] },
                                { uf: 'PA', name: "Pará", coords: [77, 99] },
                                { uf: 'PB', name: "Paraíba", coords: [75.7, 175] },
                                { uf: 'PR', name: "Paraná", coords: [59, 106] },
                                { uf: 'PE', name: "Pernambuco", coords: [75.1, 168] },
                                { uf: 'PI', name: "Piauí", coords: [76, 147] },
                                { uf: 'RJ', name: "Rio de Janeiro", coords: [62, 149] },
                                { uf: 'RN', name: "Rio Grande do Norte", coords: [76.7, 175] },
                                { uf: 'RS', name: "Rio Grande do Sul", coords: [51, 99] },
                                { uf: 'RO', name: "Rondônia", coords: [73, 59] },
                                { uf: 'RR', name: "Roraima", coords: [80.5, 64] },
                                { uf: 'SC', name: "Santa Catarina", coords: [56, 111] },
                                { uf: 'SP', name: "São Paulo", coords: [62, 120] },
                                { uf: 'SE', name: "Sergipe", coords: [73.4, 172] },
                                { uf: 'TO', name: "Tocantins", coords: [74, 123] }
                            ].filter(marker => estados.includes(marker.uf)); // Filtra os estados conforme $uf

                            const map = new jsVectorMap({
                                selector: "#map",
                                map: "brasil",
                                selectedRegions: estados.map(uf => `BR-${uf}`), // Adiciona somente os estados filtrados
                                regionsSelectable: true,
                                regionsSelectableOne: true,
                                markers: markers, // Usa apenas os estados filtrados
                                labels: {
                                    markers: {
                                        render(marker) {
                                            return marker.name || 'Não encontrado';
                                        }
                                    }
                                },
                                markerStyle: {
                                    initial: {
                                        fill: '#287F71', // Cor do marcador
                                        stroke: '#ffffff', // Cor da borda do marcador
                                    },
                                    hover: {
                                        fill: '#247266'
                                    }
                                },
                            });
                        </script>

                        <form id="officeAddressForm">

                            <div class="form-group mb-3 row">
                                <label class="form-label">CEP</label>
                                <div class="col-lg-4 col-xl-4">
                                    <input class="form-control" name="cep" type="text" id="cep" value="<?= $office['cep']; ?>" placeholder="Digite o CEP" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">

                                    <!-- Endereço -->
                                    <div class="form-group row">
                                        <label class="form-label">Endereço</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <input class="form-control" name="address" type="text" id="address" value="<?= $office['address']; ?>" placeholder="Digite o Endereço" required>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4">

                                    <!-- Número -->
                                    <div class="form-group row">
                                        <label class="form-label">Número</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <input class="form-control mb-2" name="number" type="text" id="number" value="<?= $office['number']; ?>" placeholder="Digite o Número" <?= $office['number'] == 0 ? 'disabled' : 'required'; ?>>

                                            <div class="form-check">
                                                <input class="form-check-input" name="noNumber" type="checkbox" id="noNumber" <?= $office['number'] == 0 ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="noNumber">
                                                    Sem número
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">

                                    <!-- Bairro -->
                                    <div class="form-group mb-3 row">
                                        <label class="form-label">Bairro</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <input class="form-control" name="province" type="text" id="province" value="<?= $office['province']; ?>" placeholder="Digite o Bairro" required>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">

                                    <!-- Complemento -->
                                    <div class="form-group mb-3 row">
                                        <label class="form-label">Complemento</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <input class="form-control" name="complement" type="text" id="complement" value="<?= $office['complement']; ?>" placeholder="Digite o Complemento">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">

                                    <!-- Cidade -->
                                    <div class="form-group mb-3 row">
                                        <label class="form-label">Cidade</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <input class="form-control" name="city" type="text" id="city" value="<?= $office['city']; ?>" placeholder="Digite a Cidade" required>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4">

                                    <!-- Estado (UF) -->
                                    <div class="form-group mb-3 row">
                                        <label class="form-label">Estado (UF)</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <input class="form-control" name="state" type="text" id="state" value="<?= $office['state']; ?>" placeholder="Digite o Estado (UF)" required>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <input type="hidden" name="office_address_id" value="<?= $office['office_address_id']; ?>">

                            <!-- Botão de Salvar -->
                            <div class="form-group row">
                                <div class="col-lg-12 col-xl-12">
                                    <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar Endereço</button>
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
</div> <!-- end company settings -->

<!-- Atualizar Informacoes da empresa -->
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
    $.validator.addMethod("officeDocumentExists", function(value, element) {
        let isValid = false;
        if (value) {
            $.ajax({
                url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/office/validations/document.php",
                type: "POST",
                data: { action: 'document-exists', document: value, office_id: <?= $office['id']; ?> },
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
    $("#officeForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
            email: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
            document: {
                required: true,
                minlength: 14,
                cpfcnpj: true,
                officeDocumentExists: true,
            },
            phone: {
                required: true,
                minlength: 14,
            },
        },
        messages: {
            name: {
                required: "Por favor, insira o nome do escritório.",
                minlength: "O nome do escritório deve ter pelo menos 2 caracteres.",
                maxlength: "O nome do escritório pode ter no máximo 120 caracteres.",
            },
            email: {
                required: "Por favor, insira seu e-mail",
                email: "Por favor, insira um e-mail válido",
                emailExists: "Este e-mail já está cadastrado.",
            },
            document: {
                required: "Por favor, insira seu CPF/CNPJ",
                minlength: "Seu documento deve ter pelo menos 14 caracteres",
                cpfcnpj: "Por favor, insira um CPF ou CNPJ válido",
                officeDocumentExists: "Este CPF/CNPJ já está cadastrado.",
            },
            phone: {
                required: "Por favor, insira seu telefone",
                minlength: "Seu telefone deve ter pelo menos 14 caracteres",
            },
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

            var btnSubmit = $("#officeForm #btnSubmit");
            var btnLoader = $("#officeForm #btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "update");
            formData.append("office_id", <?= $office['id'] ?? 0; ?>);

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/office/update/office.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        location.reload();
                    } else {
                        $(".alert").remove();
                        $("#officeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#officeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>

<!-- Editar Endereco -->
<script>
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

    // Validação do Formulário
    $("#officeAddressForm").validate({
        rules: {
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
        },
        messages: {
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

            var btnSubmit = $("#officeAddressForm #btnSubmit");
            var btnLoader = $("#officeAddressForm #btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "update");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/office/update/address.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        location.reload();
                    } else {
                        $(".alert").remove();
                        $("#officeAddressForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#officeAddressForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>