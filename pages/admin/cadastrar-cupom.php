<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Cadastrar cupom de desconto</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>cupons">Cupons</a></li>
                <li class="breadcrumb-item active">Cadastrar cupom</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cadastro de cupom</h5>
                </div><!-- end card header -->

                <div class="card-body">
                    <form id="couponForm">
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome*</label>
                            <input class="form-control" name="name" type="text" id="name" placeholder="Digite o nome do cupom" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <!-- Código -->
                                <div class="mb-3">
                                    <label for="code" class="form-label">Código*</label>
                                    <input class="form-control" name="code" type="text" id="code" placeholder="Digite o código do cupom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Vigência -->
                                <div class="mb-3">
                                    <label for="validity_start" class="form-label">Vigência*</label>
                                    <div class="input-group mb-3">
                                        <input class="form-control" name="validity_start" type="date" id="validity_start" required>
                                        <span class="input-group-text">à</span>
                                        <input class="form-control" name="validity_end" type="date" id="validity_end" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tipo de Desconto -->
                        <label class="form-label">Tipo de Desconto*</label>
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="discount_type" id="discount_fixed" value="fixed" checked>
                                <label class="form-check-label" for="discount_fixed">Valor</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="discount_type" id="discount_percentage" value="percentage">
                                <label class="form-check-label" for="discount_percentage">Percentual</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">

                                <!-- Input para Preço Fixo -->
                                <div class="mb-3" id="fixed_input">
                                    <label for="price" class="form-label">Valor*</label>
                                    <div class="input-group">
                                        <div class="input-group-text">R$</div>
                                        <input class="form-control text-end" name="price" type="text" id="price">
                                    </div>
                                </div>

                                <!-- Input para Percentual -->
                                <div class="mb-3 d-none" id="percentage_input">
                                    <label for="percent" class="form-label">Percentual*</label>
                                    <div class="input-group">
                                        <input class="form-control text-end" name="percent" type="number" id="percent" min="0" max="100">
                                        <div class="input-group-text">%</div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Módulos Acessíveis (Select2 Multiple) -->
                        <div class="mb-3">
                            <label for="accessibleModules" class="form-label">Módulos Acessíveis</label>
                            <select class="form-select" id="accessibleModules" name="accessibleModules[]" multiple="multiple" required>
                                <option value="gdok_entregas" disabled>Gdok Entregas</option>
                                <option value="gdok_envios">Gdok Envios</option>
                                <option value="gdok_holerites" disabled>Gdok Holerites</option>
                                <option value="gdok_honorarios" disabled>Gdok Honorários</option>
                                <option value="gdok_vencimento">Gdok Vencimentos</option>
                            </select>
                            <small class="form-text text-muted">Selecione os módulos que serão disponibilizados para este cupom.</small>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cupons" class="btn btn-light">Voltar</a>
                            <div>
                                <button class="btn btn-primary" id="btnSubmit" type="submit">Cadastrar</button>
                                <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                    <span role="status">Carregando...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>
</div>

<!-- Dependências: jQuery, jQuery Validate, Select2 (se necessário) e jQuery Mask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Aplica máscara ao campo de preço
    $('#price').mask("#.##0,00", {reverse: true});
    $('#percent').mask("#0.00", {reverse: true});

    // Inicializa o Select2 para o campo de módulos
    $('#accessibleModules').select2({
        placeholder: 'Selecione os módulos disponíveis',
        width: '100%',
        theme: 'bootstrap-5'
    });

    // Alterna os inputs de desconto fixo e porcentagem
    $('input[name="discount_type"]').on('change', function() {
        if ($(this).val() == 'fixed') {
            $('#fixed_input').removeClass('d-none');
            $('#percentage_input').addClass('d-none');
        } else {
            $('#percentage_input').removeClass('d-none');
            $('#fixed_input').addClass('d-none');
        }
    });
    
    // Validação do formulário
    $("#couponForm").validate({
        rules: {
            name: { 
                required: true,
                minlength: 2
            },
            validity_start: {
                required: true
            },
            validity_end: {
                required: true
            },
            code: {
                required: true
            },
            price: {
                required: function() {
                    return $('input[name="discount_type"]:checked').val() == 'fixed';
                }
            },
            percent: {
                required: function() {
                    return $('input[name="discount_type"]:checked').val() == 'percentage';
                },
                min: 0,
                max: 100
            },
            accessibleModules: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Por favor, insira o nome do cupom.",
                minlength: "O nome deve ter pelo menos 2 caracteres."
            },
            validity_start: {
                required: "Por favor, informe a data de inicio de vigência."
            },
            validity_end: {
                required: "Por favor, informe a data de fim de vigência."
            },
            code: {
                required: "Por favor, informe o código do cupom."
            },
            price: {
                required: "Por favor, informe o valor do cupom."
            },
            percent: {
                required: "Por favor, informe a porcentagem do cupom.",
                min: "Mínimo de 0%.",
                max: "Máximo de 100%."
            },
            accessibleModules: {
                required: "Selecione pelo menos um módulo acessível."
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
        submitHandler: function (form, event) {
            event.preventDefault();
            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");
            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "register-coupon");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/coupon/register.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>cupons";
                    } else {
                        $(".alert").remove();
                        $("#couponForm").before(
                            '<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + 
                            response.message + 
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
                        );
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);
                    $(".alert").remove();
                    $("#couponForm").before(
                        '<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
                    );
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>