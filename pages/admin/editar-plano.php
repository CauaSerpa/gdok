<?php
    // Verificar se o token foi fornecido
    if (!isset($token) || empty($token)) {
        // Redirecionar para a página de tipos de documentos com uma mensagem de erro
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Token inválido ou ausente. Não foi possível carregar o plano.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'planos');
        exit;
    }

    include('back-end/admin/plan/functions.php');

    // Validar o token (por exemplo, verificar se existe no banco de dados)
    $plan_id = $token;
    $plan = getPlanByToken($plan_id, $conn); // Suponha que essa função pegue a categoria pelo token

    // Se não encontrar a categoria com o token, redirecionar
    if (!$plan) {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Plano não encontrado.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'planos');
        exit;
    }

    // Caso a categoria exista, preencher os campos com os dados da categoria
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Editar Plano</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>planos">Planos</a></li>
                <li class="breadcrumb-item active">Editar Plano</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Edição de Plano</h5>
        </div>
        <div class="card-body">
            <form id="planForm">
                <!-- Nome do Plano -->
                <div class="mb-3">
                    <label for="planName" class="form-label">Nome do Plano*</label>
                    <input type="text" class="form-control" id="planName" name="planName" value="<?= $plan['plan_name']; ?>" placeholder="Informe o nome do plano" required>
                </div>
                
                <!-- Descrição do Plano -->
                <div class="mb-3">
                    <label for="planDescription" class="form-label">Descrição do Plano</label>
                    <textarea class="form-control" id="planDescription" name="planDescription" rows="3" placeholder="Descreva as funcionalidades e benefícios deste plano"><?= $plan['plan_description']; ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <!-- Valor do Plano -->
                        <div class="mb-3">
                            <label for="price" class="form-label">Valor*</label>
                            <div class="input-group">
                                <div class="input-group-text">R$</div>
                                <input type="text" class="form-control text-end" id="price" name="price" value="<?= number_format($plan['plan_price'], 2, ',', '.'); ?>" placeholder="0,00" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Período de Cobrança -->
                <div class="mb-3">
                    <label for="billingPeriod" class="form-label">Período de Cobrança*</label>
                    <select class="form-select" id="billingPeriod" name="billingPeriod" required>
                        <option value="" disabled>Selecione o período de cobrança</option>
                        <option value="mensal" <?= $plan['billing_period'] === 'mensal' ? 'selected' : ''; ?>>Mensal</option>
                        <option value="trimestral" <?= $plan['billing_period'] === 'trimestral' ? 'selected' : ''; ?>>Trimestral</option>
                        <option value="semestral" <?= $plan['billing_period'] === 'semestral' ? 'selected' : ''; ?>>Semestral</option>
                        <option value="anual" <?= $plan['billing_period'] === 'anual' ? 'selected' : ''; ?>>Anual</option>
                    </select>
                </div>
                
                <!-- Módulos Acessíveis (Select2 Multiple) -->
                <div class="mb-3">
                    <label for="accessibleModules" class="form-label">Módulos Acessíveis</label>
                    <select class="form-select" id="accessibleModules" name="accessibleModules[]" multiple="multiple" required>
                        <?php $selectedModules = json_decode($plan['accessible_modules'], true) ?? []; ?>
                        <option value="gdok_entregas" disabled <?= in_array('gdok_entregas', $selectedModules) ? 'selected' : ''; ?>>Gdok Entregas</option>
                        <option value="gdok_envios" <?= in_array('gdok_envios', $selectedModules) ? 'selected' : ''; ?>>Gdok Envios</option>
                        <option value="gdok_holerites" disabled <?= in_array('gdok_holerites', $selectedModules) ? 'selected' : ''; ?>>Gdok Holerites</option>
                        <option value="gdok_honorarios" disabled <?= in_array('gdok_honorarios', $selectedModules) ? 'selected' : ''; ?>>Gdok Honorários</option>
                        <option value="gdok_vencimento" <?= in_array('gdok_vencimento', $selectedModules) ? 'selected' : ''; ?>>Gdok Vencimento</option>
                    </select>
                    <small class="form-text text-muted">Selecione os módulos que serão disponibilizados para este plano.</small>
                </div>
                
                <!-- Checkboxes para configurações do plano -->
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input" id="defaultPlan" name="defaultPlan" <?= $plan['default_plan'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="defaultPlan">Marcar como Plano Padrão</label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input" id="publicPlan" name="publicPlan" <?= $plan['public_plan'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="publicPlan">Plano Público</label>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input" id="activePlan" name="activePlan" <?= $plan['active_plan'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="activePlan">Plano Ativo</label>
                </div>
                
                <!-- Botões de ação -->
                <div class="d-flex align-items-center justify-content-between">
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>planos" class="btn btn-light">Voltar</a>
                    <div>
                        <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar</button>
                        <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Carregando...</span>
                        </button>
                    </div>
                </div>
                
                <!-- Campos ocultos -->
                <input type="hidden" name="plan_id" value="<?= $plan['id']; ?>">
                <input type="hidden" name="action" value="update-plan">
            </form>
        </div>
    </div>
</div>

<!-- Inclusão dos estilos e scripts do Select2 e máscara de preço -->
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function() {
    // Função que realiza a validação para o plano padrão
    function validateDefaultPlan() {
        if ($('#defaultPlan').is(':checked')) {
            var billingPeriod = $('#billingPeriod').val();
            if (!billingPeriod) {
                alert('Por favor, selecione o período de cobrança antes de marcar como plano padrão.');
                $('#defaultPlan').prop('checked', false);
                return;
            }
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/plan/forms-validations/check-default-plan.php',
                type: 'GET',
                data: { billingPeriod: billingPeriod, planId: <?= $plan['id']; ?> },
                dataType: 'json',
                success: function(response) {
                    if(response.exists) {
                        alert(response.message);
                        $('#defaultPlan').prop('checked', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erro na validação:", error);
                }
            });
        }
    }

    // Evento: Ao marcar ou desmarcar o checkbox "Plano Padrão"
    $('#defaultPlan').on('change', function() {
        validateDefaultPlan();
    });

    // Evento: Quando o período de cobrança é alterado e o checkbox já estiver selecionado
    $('#billingPeriod').on('change', function() {
        if ($('#defaultPlan').is(':checked')) {
            validateDefaultPlan();
        }
    });

    // Validação na submissão do formulário
    $('#planForm').on('submit', function(event) {
        if ($('#defaultPlan').is(':checked')) {
            var billingPeriod = $('#billingPeriod').val();
            var valid = false;
            // Chamada síncrona para garantir a validação antes de enviar o form
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/plan/forms-validations/check-default-plan.php',
                type: 'GET',
                data: { billingPeriod: billingPeriod, planId: <?= $plan['id']; ?> },
                dataType: 'json',
                async: false,
                success: function(response) {
                    if(response.exists) {
                        alert(response.message);
                        valid = false;
                    } else {
                        valid = true;
                    }
                },
                error: function() {
                    valid = false;
                }
            });
            if (!valid) {
                event.preventDefault();
                return false;
            }
        }
    });
});
</script>

<script>
$(document).ready(function() {
    // Aplicar máscara de preço
    $('#price').mask("#.##0,00", {reverse: true});

    // Inicializa o Select2 para os módulos acessíveis
    $('#accessibleModules').select2({
        placeholder: 'Selecione os módulos disponíveis',
        width: '100%',
        theme: 'bootstrap-5'
    });

    // Método de validação personalizado para preço
    jQuery.validator.addMethod("price", function(value, element) {
        var valor = value.replace(".", "").replace(",", "."); 
        return this.optional(element) || (parseFloat(valor) >= 0.01);
    }, "O valor deve ser maior que 0,01");

    // Validação do formulário com jQuery Validate
    $('#planForm').validate({
        rules: {
            planName: {
                required: true,
                minlength: 3
            },
            price: {
                required: true,
                price: true
            },
            billingPeriod: {
                required: true
            },
            accessibleModules: {
                required: true
            }
        },
        messages: {
            planName: {
                required: "Por favor, informe o nome do plano.",
                minlength: "O nome deve conter pelo menos 3 caracteres."
            },
            price: {
                required: "Informe o valor do plano.",
                price: "O valor deve ser maior que 0,01"
            },
            billingPeriod: {
                required: "Selecione o período de cobrança."
            },
            accessibleModules: {
                required: "Selecione pelo menos um módulo acessível."
            }
        },
        errorElement: "em",
        errorPlacement: function(error, element) {
            error.addClass("invalid-feedback");
            if (element.hasClass("select2-hidden-accessible")) {
                error.insertAfter(element.next(".select2"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function(element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        submitHandler: function(form) {
            event.preventDefault();

            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/plan/update.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>planos";
                    } else {
                        $(".alert").remove();
                        $("#planForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function(xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);
                    $(".alert").remove();
                    $("#planForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>