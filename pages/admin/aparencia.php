<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Aparência do Template</h4>
        </div>
    </div>

    <?php
        // Consulta para buscar empresas cadastradas
        $stmt = $conn->prepare("SELECT * FROM tb_template_appearance WHERE id = ?");
        $stmt->execute([1]);
        $appearence = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <div class="row">
        <div class="col-6">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Editar Aparência do Template</h5>
                </div><!-- end card header -->

                <div class="card-body">
                    <form id="appearenceForm">
                        <div class="row">
                            <div class="col-md-12">

                                <h6 class="fs-16">Aparência do Template</h6>

                                <div class="row gy-2 gx-3 align-items-center mb-3">
                                    <label for="bg_color" class="col-sm-3 col-form-label">Cor de fundo</label>
                                    <div class="col-auto">
                                        <input type="color" id="bg_color" name="bg_color" value="<?= $appearence['bg_color'] ?? '#F8F9FA'; ?>" title="Escolha a cor de fundo do template" class="form-control form-control-color">
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="bg_color_default" date-value="#F8F9FA">
                                            <label class="form-check-label" for="bg_color_default">
                                                Voltar a cor padrão
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row gy-2 gx-3 align-items-center mb-3">
                                    <label for="header_color" class="col-sm-3 col-form-label">Cor do header</label>
                                    <div class="col-auto">
                                        <input type="color" id="header_color" name="header_color" value="<?= $appearence['header_color'] ?? '#ffffff'; ?>" title="Escolha a cor de fundo do template" class="form-control form-control-color">
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="header_color_default" date-value="#ffffff">
                                            <label class="form-check-label" for="header_color_default">
                                                Voltar a cor padrão
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row gy-2 gx-3 align-items-center mb-5">
                                    <label for="sidebar_color" class="col-sm-3 col-form-label">Cor da sidebar</label>
                                    <div class="col-auto">
                                        <input type="color" id="sidebar_color" name="sidebar_color" value="<?= $appearence['sidebar_color'] ?? '#ffffff'; ?>" title="Escolha a cor de fundo do template" class="form-control form-control-color">
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="sidebar_color_default" date-value="#ffffff">
                                            <label class="form-check-label" for="sidebar_color_default">
                                                Voltar a cor padrão
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="fs-16">Aparência dos Textos</h6>

                                <div class="row gy-2 gx-3 align-items-center mb-5">
                                    <label for="sidebar_color" class="col-sm-3 col-form-label">Cor dos textos</label>
                                    <div class="col-auto">
                                        <input type="color" id="text_color" name="text_color" value="<?= $appearence['text_color'] ?? '#4a5a6b'; ?>" class="form-control form-control-color">
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="text_color_default" date-value="#4a5a6b">
                                            <label class="form-check-label" for="text_color_default">
                                                Voltar a cor padrão
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="fs-16">Aparência dos botões</h6>

                                <div class="row gy-2 gx-3 align-items-center mb-3">
                                    <label for="sidebar_color" class="col-sm-3 col-form-label">Cor dos botões principais</label>
                                    <div class="col-auto">
                                        <input type="color" id="button_color" name="button_color" value="<?= $appearence['button_color'] ?? '#287F71'; ?>" class="form-control form-control-color">
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="button_color_default" date-value="#287F71">
                                            <label class="form-check-label" for="button_color_default">
                                                Voltar a cor padrão
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row gy-2 gx-3 align-items-center mb-3">
                                    <label for="sidebar_color" class="col-sm-3 col-form-label">Cor do hover dos botões principais</label>
                                    <div class="col-auto">
                                        <input type="color" id="hover_color" name="hover_color" value="<?= $appearence['hover_color'] ?? '#247266'; ?>" class="form-control form-control-color">
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="hover_color_default" date-value="#247266">
                                            <label class="form-check-label" for="hover_color_default">
                                                Voltar a cor padrão
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <div>

                                <button class="btn btn-primary" id="btnSubmit" type="submit">Salvar</button>
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

<script>
    $(document).ready(function() {
        // Para cada checkbox com id terminando em "_default" e atributo "date-value"
        $('input[type="checkbox"][id$="_default"]').on('change', function() {
            // Obtém o valor padrão definido no atributo "date-value"
            var defaultValue = $(this).attr('date-value');
            // Determina o id do input removendo o sufixo "_default" do id do checkbox
            var inputId = $(this).attr('id').replace('_default', '');
            var $colorInput = $('#' + inputId);
            
            if ($(this).is(':checked')) {
                // Se marcado, desabilita o input e define o valor padrão
                $colorInput.prop('readonly', true).val(defaultValue);
            } else {
                // Se desmarcado, habilita o input
                $colorInput.prop('readonly', false);
            }
        });
    });
</script>

<script>
$(document).ready(function() {
    // Validação do Formulário
    $("#appearenceForm").validate({
        rules: {
            bg_color: {
                required: true,
            },
            header_color: {
                required: true,
            },
            sidebar_color: {
                required: true,
            },
            text_color: {
                required: true,
            },
            button_color: {
                required: true,
            },
            hover_color: {
                required: true,
            },
        },
        messages: {
            bg_color: {
                required: "Por favor, insira a cor.",
            },
            header_color: {
                required: "Por favor, insira a cor.",
            },
            sidebar_color: {
                required: "Por favor, insira a cor.",
            },
            text_color: {
                required: "Por favor, insira a cor.",
            },
            button_color: {
                required: "Por favor, insira a cor.",
            },
            hover_color: {
                required: "Por favor, insira a cor.",
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

            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            var formData = new FormData(form);
            formData.append("action", "update-appearence-colors");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/template/update.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        location.reload();
                    } else {
                        $(".alert").remove();
                        $("#appearenceForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#appearenceForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>