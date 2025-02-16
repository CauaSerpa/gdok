<?php
    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar tipos de documentos cadastradas
    $stmt = $conn->prepare("SELECT id, name, advance_notification, personalized_advance_notification FROM tb_document_types WHERE user_id = ? ORDER BY id DESC");
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
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-empresa" class="btn btn-link" onclick="confirm('Você será redirecionado para a página de cadastro de empresa. Deseja continuar?')">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Empresa
                                        </a>
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
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-tipo-documento" class="btn btn-link" onclick="confirm('Você será redirecionado para a página de cadastro de tipo de documento. Deseja continuar?')">
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

                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>tipos-documentos" class="btn btn-light">Voltar</a>

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