<?php
    $stmt = $conn->prepare("SELECT office_id, is_active, contact, send_type, start_days_before, after_due_days 
        FROM tb_office_notification_settings 
        WHERE office_id = ? AND channel = 'email'");
    $stmt->execute([$office['id']]);

    $settingsEmail = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT office_id, is_active, contact, send_type, start_days_before, after_due_days 
        FROM tb_office_notification_settings 
        WHERE office_id = ? AND channel = 'whatsapp'");
    $stmt->execute([$office['id']]);

    $settingsWhatsapp = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="tab-pane pt-4" id="company_settings" role="tabpanel">
    <div class="row">

        <div class="col-md-12 col-xl-12 mb-3">
            <div class="border rounded-2 bg-light p-2 d-flex align-items-center">
                <i class="mdi mdi-information-slab-circle-outline fs-19 text-warning me-2"></i>
                <p class="fs-16 mb-0"><strong>Atenção!</strong> As alterações feitas aqui serão aplicadas a <strong>todas as notificações do sistema</strong>.</p>
            </div>
        </div>

        <div class="col-lg-6 col-xl-6">
            <div class="card border">

                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Parametrizar Notificações por E-mail</h4>
                        </div><!--end col-->
                    </div>
                </div>

                <div class="card-body">

                    <form id="configureEmailNotificationsForm">

                        <div class="form-group mb-3 row">
                            <label for="notify_email" class="form-label">Notificações por E-mail</label>
                            <div class="col-lg-4 col-xl-4">
                                <select class="form-select" name="notify_email" id="notify_email" required>
                                    <option value="on" <?php if (!isset($settingsEmail) || (isset($settingsEmail['is_active']) && $settingsEmail['is_active'] == 1)) {echo 'selected';} ?>>Habilitado</option>
                                    <option value="off" <?php if (isset($settingsEmail['is_active']) && $settingsEmail['is_active'] == 0) {echo 'selected';} ?>>Desabilitado</option>
                                </select>
                            </div>
                        </div>

                        <div id="emailSettings">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3 row">
                                        <label for="office_email" class="form-label">E-mail do Escritório</label>
                                        <div class="col-lg-12 col-xl-7">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                                <input class="form-control" name="office_email" type="email" id="office_email" value="<?= ($settingsEmail) ? $settingsEmail['contact'] : $office['email']; ?>" placeholder="Digite o E-mail do Escritório">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3 row">
                                        <label for="email_frequency" class="form-label">Frequência de envio</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <select class="form-select" name="email_frequency" id="email_frequency" required>
                                                <option value="once_due" <?php if (!isset($settingsEmail) || (isset($settingsEmail['send_type']) && $settingsEmail['send_type'] == 'once_due')) {echo 'selected';} ?>>Conforme prazo escolhido no cadastro do documento e no vencimento (padrão)</option>
                                                <option value="daily_until_due" <?php if (isset($settingsEmail['send_type']) && $settingsEmail['send_type'] == 'daily_until_due') {echo 'selected';} ?>>Todo dia (iniciando no prazo escolhido até o vencimento)</option>
                                                <option value="daily_until_after" <?php if (isset($settingsEmail['send_type']) && $settingsEmail['send_type'] == 'daily_until_after') {echo 'selected';} ?>>Todos os dias antes do vencimento (iniciando no prazo escolhido e até 7 dias após o vencimento)</option>
                                                <option value="predefined_dates" <?php if (isset($settingsEmail['send_type']) && $settingsEmail['send_type'] == 'predefined_dates') {echo 'selected';} ?>>No dia predefinido, no vencimento e até 7 dias após o vencimento</option>
                                                <option value="due_date" <?php if (isset($settingsEmail['send_type']) && $settingsEmail['send_type'] == 'due_date') {echo 'selected';} ?>>Apenas no dia do vencimento</option>
                                                <option value="personalized" <?php if (isset($settingsEmail['send_type']) && $settingsEmail['send_type'] == 'personalized') {echo 'selected';} ?>>Apenas no dia predefinido</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="personalizedSettings" class="d-none">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row">
                                            <label for="before_expiration" class="form-label">Notificar antes do vencimento?</label>
                                            <div class="col-lg-12 col-xl-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="before_expiration" type="checkbox" id="before_expiration" <?php if ($settingsEmail && !empty($settingsEmail['start_days_before'])) {echo 'checked';} ?>>
                                                    <label class="form-check-label" for="before_expiration">
                                                        Sim
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row">
                                            <label for="after_expiration" class="form-label">Notificar após vencimento?</label>
                                            <div class="col-lg-12 col-xl-12">
                                                <input class="form-check-input" name="after_expiration" type="checkbox" id="after_expiration" <?php if ($settingsEmail && !empty($settingsEmail['after_due_days'])) {echo 'checked';} ?>>
                                                <label class="form-check-label" for="after_expiration">
                                                    Sim
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row" id="frequencyBeforeExpirationSettings">
                                            <label for="frequency_before_expiration" class="form-label">
                                                Frequência de notificações antes do vencimento
                                                <i class="mdi mdi-help-circle text-muted" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="Este prazo é definido na criação do documento.">
                                                </i>
                                            </label>
                                            <div class="col-lg-12 col-xl-12">
                                                <select class="form-select" name="frequency_before_expiration" id="frequency_before_expiration" required>
                                                    <option value="once" <?php if (!isset($settingsEmail) || (isset($settingsEmail['start_days_before']) && $settingsEmail['start_days_before'] == 'once')) {echo 'selected';} ?>>Uma vez no dia predefinido (Padrão)</option>
                                                    <option value="daily" <?php if (isset($settingsEmail['start_days_before']) && $settingsEmail['start_days_before'] == 'daily') {echo 'selected';} ?>>Todos os dias (Iniciando no prazo escolhido na criação do documento)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row" id="daysAfterExpirationSettings">
                                            <label for="days_after_expiration" class="form-label">
                                                Dias após vencimento
                                                <i class="mdi mdi-help-circle text-muted" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="Será enviado todos os dias até chegar ao prazo definido abaixo.">
                                                </i>
                                            </label>
                                            <div class="col-lg-12 col-xl-12">
                                                <div class="input-group">
                                                    <input class="form-control text-end" name="days_after_expiration" type="text" id="days_after_expiration" value="<?= ($settingsEmail && !empty($settingsEmail['after_due_days'])) ? $settingsEmail['after_due_days'] : 7; ?>" placeholder="Digite os dias após vencimento" required>
                                                    <div class="input-group-text">Dias</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="form-group row">
                            <div class="col-lg-12 col-xl-12 d-flex aling-items-center justify-content-between">
                                <button class="btn btn-light" id="reset" type="button">Redefinir Predefinições</button>
                                <div>
                                    <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar</button>
                                    <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                        <span role="status">Carregando...</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>

                </div><!--end card-body-->
            </div>
        </div>

        <div class="col-lg-6 col-xl-6">
            <div class="card border">

                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">Parametrizar Notificações por WhatsApp</h4>
                        </div><!--end col-->
                    </div>
                </div>

                <div class="card-body">

                    <form id="configureWhatsappNotificationsForm">

                        <div class="form-group mb-3 row">
                            <label for="notify_whatsapp" class="form-label">Notificações por WhatsApp</label>
                            <div class="col-lg-4 col-xl-4">
                                <select class="form-select" name="notify_whatsapp" id="notify_whatsapp" required>
                                    <option value="on" <?php if (!isset($settingsWhatsapp) || (isset($settingsWhatsapp['is_active']) && $settingsWhatsapp['is_active'] == 1)) {echo 'selected';} ?>>Habilitado</option>
                                    <option value="off" <?php if (isset($settingsWhatsapp['is_active']) && $settingsWhatsapp['is_active'] == 0) {echo 'selected';} ?>>Desabilitado</option>
                                </select>
                            </div>
                        </div>

                        <div id="whatsappSettings">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3 row">
                                        <label for="office_whatsapp" class="form-label">WhatsApp do Escritório</label>
                                        <div class="col-lg-12 col-xl-7">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="mdi mdi-phone-outline"></i></span>
                                                <input class="form-control" name="office_whatsapp" type="tel" id="office_whatsapp" value="<?= ($settingsWhatsapp) ? $settingsWhatsapp['contact'] : $office['phone']; ?>" placeholder="Digite o WhatsApp do Escritório" onkeyup="handlePhone(event)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3 row">
                                        <label for="whatsapp_frequency" class="form-label">Frequência de envio</label>
                                        <div class="col-lg-12 col-xl-12">
                                            <select class="form-select" name="whatsapp_frequency" id="whatsapp_frequency" required>
                                                <option value="once_due" <?php if (!isset($settingsWhatsapp) || (isset($settingsWhatsapp['send_type']) && $settingsWhatsapp['send_type'] == 'once_due')) {echo 'selected';} ?>>Conforme prazo escolhido no cadastro do documento e no vencimento (padrão)</option>
                                                <option value="daily_until_due" <?php if (isset($settingsWhatsapp['send_type']) && $settingsWhatsapp['send_type'] == 'daily_until_due') {echo 'selected';} ?>>Todo dia (iniciando no prazo escolhido até o vencimento)</option>
                                                <option value="daily_until_after" <?php if (isset($settingsWhatsapp['send_type']) && $settingsWhatsapp['send_type'] == 'daily_until_after') {echo 'selected';} ?>>Todos os dias antes do vencimento (iniciando no prazo escolhido e até 7 dias após o vencimento)</option>
                                                <option value="predefined_dates" <?php if (isset($settingsWhatsapp['send_type']) && $settingsWhatsapp['send_type'] == 'predefined_dates') {echo 'selected';} ?>>No dia predefinido, no vencimento e até 7 dias após o vencimento</option>
                                                <option value="due_date" <?php if (isset($settingsWhatsapp['send_type']) && $settingsWhatsapp['send_type'] == 'due_date') {echo 'selected';} ?>>Apenas no dia do vencimento</option>
                                                <option value="personalized" <?php if (isset($settingsWhatsapp['send_type']) && $settingsWhatsapp['send_type'] == 'personalized') {echo 'selected';} ?>>Apenas no dia predefinido</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="personalizedSettings" class="d-none">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row">
                                            <label for="before_expiration" class="form-label">Notificar antes do vencimento?</label>
                                            <div class="col-lg-12 col-xl-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="before_expiration" type="checkbox" id="before_expiration" <?php if ($settingsWhatsapp && !empty($settingsWhatsapp['start_days_before'])) {echo 'checked';} ?>>
                                                    <label class="form-check-label" for="before_expiration">
                                                        Sim
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row">
                                            <label for="after_expiration" class="form-label">Notificar após vencimento?</label>
                                            <div class="col-lg-12 col-xl-12">
                                                <input class="form-check-input" name="after_expiration" type="checkbox" id="after_expiration" <?php if ($settingsWhatsapp && !empty($settingsWhatsapp['after_due_days'])) {echo 'checked';} ?>>
                                                <label class="form-check-label" for="after_expiration">
                                                    Sim
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row" id="frequencyBeforeExpirationSettings">
                                            <label for="frequency_before_expiration" class="form-label">
                                                Frequência de notificações antes do vencimento
                                                <i class="mdi mdi-help-circle text-muted" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="Este prazo é definido na criação do documento.">
                                                </i>
                                            </label>
                                            <div class="col-lg-12 col-xl-12">
                                                <select class="form-select" name="frequency_before_expiration" id="frequency_before_expiration" required>
                                                    <option value="once" <?php if (!isset($settingsWhatsapp) || (isset($settingsWhatsapp['start_days_before']) && $settingsWhatsapp['start_days_before'] == 'once')) {echo 'selected';} ?>>Uma vez no dia predefinido (Padrão)</option>
                                                    <option value="daily" <?php if (isset($settingsWhatsapp['start_days_before']) && $settingsWhatsapp['start_days_before'] == 'daily') {echo 'selected';} ?>>Todos os dias (Iniciando no prazo escolhido na criação do documento)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 row" id="daysAfterExpirationSettings">
                                            <label for="days_after_expiration" class="form-label">
                                                Dias após vencimento
                                                <i class="mdi mdi-help-circle text-muted" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    data-bs-title="Será enviado todos os dias até chegar ao prazo definido abaixo.">
                                                </i>
                                            </label>
                                            <div class="col-lg-12 col-xl-12">
                                                <div class="input-group">
                                                    <input class="form-control text-end" name="days_after_expiration" type="text" id="days_after_expiration" value="<?= ($settingsWhatsapp && !empty($settingsWhatsapp['after_due_days'])) ? $settingsWhatsapp['after_due_days'] : 7; ?>" placeholder="Digite os dias após vencimento" required>
                                                    <div class="input-group-text">Dias</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="form-group row">
                            <div class="col-lg-12 col-xl-12 d-flex aling-items-center justify-content-between">
                                <button class="btn btn-light" id="reset" type="button">Redefinir Predefinições</button>
                                <div>
                                    <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar</button>
                                    <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                        <span role="status">Carregando...</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>

                </div><!--end card-body-->
            </div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function () {
        function toggleSettings(selector, condition) {
            if (condition) {
                $(selector).removeClass('d-none');
            } else {
                $(selector).addClass('d-none');
            }
        }

        // Função para verificar configurações iniciais
        function checkInitialSettings() {
            toggleSettings("#configureEmailNotificationsForm #emailSettings", $("#configureEmailNotificationsForm #notify_email").val() === "on");
            toggleSettings("#configureEmailNotificationsForm #personalizedSettings", $("#configureEmailNotificationsForm #email_frequency").val() === "personalized");
            toggleSettings("#configureEmailNotificationsForm #frequencyBeforeExpirationSettings", $("#configureEmailNotificationsForm #before_expiration").is(":checked"));
            toggleSettings("#configureEmailNotificationsForm #daysAfterExpirationSettings", $("#configureEmailNotificationsForm #after_expiration").is(":checked"));

            toggleSettings("#configureWhatsappNotificationsForm #whatsappSettings", $("#configureWhatsappNotificationsForm #notify_whatsapp").val() === "on");
            toggleSettings("#configureWhatsappNotificationsForm #personalizedSettings", $("#configureWhatsappNotificationsForm #whatsapp_frequency").val() === "personalized");
            toggleSettings("#configureWhatsappNotificationsForm #frequencyBeforeExpirationSettings", $("#configureWhatsappNotificationsForm #before_expiration").is(":checked"));
            toggleSettings("#configureWhatsappNotificationsForm #daysAfterExpirationSettings", $("#configureWhatsappNotificationsForm #after_expiration").is(":checked"));
        }

        // Notificações por e-mail habilitadas/desabilitadas
        $("#configureEmailNotificationsForm #notify_email").change(function () {
            toggleSettings("#configureEmailNotificationsForm #emailSettings", $(this).val() === "on");
        });

        // Mostrar/esconder configurações personalizadas do e-mail
        $("#configureEmailNotificationsForm #email_frequency").change(function () {
            toggleSettings("#configureEmailNotificationsForm #personalizedSettings", $(this).val() === "personalized");
        });

        // Mostrar/esconder frequência antes do vencimento para e-mail
        $("#configureEmailNotificationsForm #before_expiration").change(function () {
            toggleSettings("#configureEmailNotificationsForm #frequencyBeforeExpirationSettings", $(this).is(":checked"));
        });

        // Mostrar/esconder dias após vencimento para e-mail
        $("#configureEmailNotificationsForm #after_expiration").change(function () {
            toggleSettings("#configureEmailNotificationsForm #daysAfterExpirationSettings", $(this).is(":checked"));
        });


        // Notificações por WhatsApp habilitadas/desabilitadas
        $("#configureWhatsappNotificationsForm #notify_whatsapp").change(function () {
            toggleSettings("#configureWhatsappNotificationsForm #whatsappSettings", $(this).val() === "on");
        });

        // Mostrar/esconder configurações personalizadas do WhatsApp
        $("#configureWhatsappNotificationsForm #whatsapp_frequency").change(function () {
            toggleSettings("#configureWhatsappNotificationsForm #personalizedSettings", $(this).val() === "personalized");
        });

        // Mostrar/esconder frequência antes do vencimento para WhatsApp
        $("#configureWhatsappNotificationsForm #before_expiration").change(function () {
            toggleSettings("#configureWhatsappNotificationsForm #frequencyBeforeExpirationSettings", $(this).is(":checked"));
        });

        // Mostrar/esconder dias após vencimento para WhatsApp
        $("#configureWhatsappNotificationsForm #after_expiration").change(function () {
            toggleSettings("#configureWhatsappNotificationsForm #daysAfterExpirationSettings", $(this).is(":checked"));
        });

        // Resetar configurações para os valores padrão
        $("#reset").click(function () {
            $("#configureEmailNotificationsForm #notify_email").val("on").trigger("change");
            $("#configureEmailNotificationsForm #email_frequency").val("once_due").trigger("change");
            $("#configureEmailNotificationsForm #before_expiration").prop("checked", true).trigger("change");
            $("#configureEmailNotificationsForm #after_expiration").prop("checked", true).trigger("change");
            $("#configureEmailNotificationsForm #days_after_expiration").val(7);

            $("#configureWhatsappNotificationsForm #notify_whatsapp").val("on").trigger("change");
            $("#configureWhatsappNotificationsForm #whatsapp_frequency").val("once_due").trigger("change");
            $("#configureWhatsappNotificationsForm #before_expiration").prop("checked", true).trigger("change");
            $("#configureWhatsappNotificationsForm #after_expiration").prop("checked", true).trigger("change");
            $("#configureWhatsappNotificationsForm #days_after_expiration").val(7);
        });

        // Verificar configurações iniciais ao carregar a página
        checkInitialSettings();
    });
</script>

<!-- Configurar E-mail -->
<script>
$(document).ready(function() {
    // Validação do Formulário
    $("#configureEmailNotificationsForm").validate({
        rules: {
            office_email: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
        },
        messages: {
            office_email: {
                required: "Por favor, insira o nome do escritório.",
                minlength: "O nome do escritório deve ter pelo menos 2 caracteres.",
                maxlength: "O nome do escritório pode ter no máximo 120 caracteres.",
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

            var btnSubmit = $("#configureEmailNotificationsForm #btnSubmit");
            var btnLoader = $("#configureEmailNotificationsForm #btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "update");
            formData.append("office_id", <?= $office['id'] ?? 0; ?>);

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/office/settings/email/configure.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        location.reload();
                    } else {
                        $(".alert").remove();
                        $("#configureEmailNotificationsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#configureEmailNotificationsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>

<!-- Configurar WhatsApp -->
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

$(document).ready(function() {
    // Validação do Formulário
    $("#configureWhatsappNotificationsForm").validate({
        rules: {
            office_whatsapp: {
                required: true,
                minlength: 14,
            },
        },
        messages: {
            office_whatsapp: {
                required: "Por favor, insira um WhatsApp do escritório.",
                minlength: "O WhatsApp do escritório deve ter pelo menos 14 caracteres.",
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

            var btnSubmit = $("#configureWhatsappNotificationsForm #btnSubmit");
            var btnLoader = $("#configureWhatsappNotificationsForm #btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "update");
            formData.append("office_id", <?= $office['id'] ?? 0; ?>);

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/office/settings/whatsapp/configure.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        location.reload();
                    } else {
                        $(".alert").remove();
                        $("#configureWhatsappNotificationsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#configureWhatsappNotificationsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>