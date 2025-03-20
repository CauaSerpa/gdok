<?php
    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar deptos cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_sending_departments WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .line-height {
        height: 20px;
        margin: 8px 0;
        width: 1px;
        background: black;
    }
</style>

<!-- Modal para criar categoria -->
<div class="modal fade" id="createCategorySendingModal" tabindex="-1" aria-labelledby="createCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCategoryLabel">Criar Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createCategorySendingForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" id="categoryName" name="category_name" placeholder="Digite o nome da categoria" required>
                    </div>
                    <button class="btn btn-primary" id="btnSubmitCategory" type="submit">Cadastrar</button>
                    <button class="btn btn-primary loader-btn d-none" id="btnLoaderCategory" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status">Carregando...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Upload Documento de Envio</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos-envios">Documentos de Envio</a></li>
                <li class="breadcrumb-item active">Upload Documento de Envio</li>
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
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-empresa-envio" class="btn btn-link" onclick="confirm('Você será redirecionado para a página de cadastro de empresa de envio. Deseja continuar?')">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Empresa de Envio
                                        </a>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">

                                <!-- Deptos -->
                                <div class="mb-3">
                                    <label for="department" class="form-label">Depto*</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select w-50" name="department" id="department" required>
                                            <option value="" selected disabled>Selecione uma Depto</option>
                                            <?php foreach ($departments as $department): ?>
                                                <option value="<?= htmlspecialchars($department['id']); ?>">
                                                    <?= htmlspecialchars($department['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-depto-envio" class="btn btn-link" onclick="confirm('Você será redirecionado para a página de cadastro de depto de envio. Deseja continuar?')">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Depto de Envio
                                        </a>
                                    </div>
                                </div>

                            </div>

                            <div id="categoryContent" class="col-md-6">

                                <!-- Categoria -->
                                <div class="mb-3">
                                    <label for="category" class="form-label">Categoria</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select w-50" name="category" id="category" required>
                                            <option value="" selected disabled>Selecione uma Categoria</option>
                                        </select>
                                        <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#createCategorySendingModal">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Categoria de Envio
                                        </button>
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
                                <!-- Referência -->
                                <div class="mb-3">
                                    <label for="reference" class="form-label">Referência</label>
                                    <input class="form-control" name="reference" type="text" id="reference" placeholder="mm/aaaa">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <!-- Data de Vencimento -->
                                <div class="mb-3">
                                    <label for="expiration_date" class="form-label">Data de Vencimento*</label>
                                    <input class="form-control" name="expiration_date" type="date" id="expiration_date">
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <!-- Valor -->
                                <div class="mb-3">
                                    <label for="price" class="form-label">Valor</label>
                                    <div class="input-group">
                                        <div class="input-group-text">R$</div>
                                        <input class="form-control text-end" name="price" type="text" id="price">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Observação -->
                        <div class="mb-3">
                            <label for="observation" class="form-label">Observação</label>
                            <textarea class="form-control" name="observation" id="observation" rows="5" spellcheck="false"></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">

                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos-envios" class="btn btn-light">Voltar</a>

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

<!-- Bootstrap Datepicker (JS e CSS) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />

<!-- Seu CSS customizado -->
<style>
    .century.active,
    .decade.active,
    .month.active,
    .year.active {
        background-color: #287F71 !important;
        background-image: none !important;
    }
</style>

<!-- Inclua os scripts do jQuery, Bootstrap Datepicker e Inputmask conforme necessário -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/jquery.inputmask.min.js"></script>

<script>
  $(document).ready(function(){
    // Aplica a máscara
    $('#reference').mask("00/0000");

    // Inicializa o datepicker configurado para mês/ano
    $('#reference').datepicker({
      format: "mm/yyyy",
      startView: "months",
      minViewMode: "months",
      autoclose: true
    });
  });
</script>

<!-- Máscara de Preço -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        $('#price').mask("#.##0,00", {reverse: true});
    });
</script>

<script>
    $(document).ready(function () {
        $('#categoryContent').addClass('d-none'); // Esconder a seleção de categorias inicialmente

        $('#department').change(function () {
            const departmentId = $(this).val();
            const categoryContent = $('#categoryContent');
            const categorySelect = $('#category');

            if (departmentId) {
                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/list-categories.php',
                    type: 'GET',
                    data: { department: departmentId }, // Enviar o ID do departamento
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            categorySelect.empty(); // Limpar opções existentes
                            categorySelect.append('<option value="" selected disabled>Selecione uma Categoria</option>');

                            response.categories.forEach(function (category) {
                                categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                            });

                            categoryContent.removeClass('d-none'); // Mostrar a seleção de categorias
                        } else {
                            categoryContent.addClass('d-none');
                            alert(response.message);
                        }
                    },
                    error: function () {
                        categoryContent.addClass('d-none');
                        alert('Erro ao carregar categorias.');
                    }
                });
            } else {
                categoryContent.addClass('d-none'); // Esconder caso o usuário limpe o campo
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Função para carregar categorias
        function loadCategories() {
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/list-categories.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        const categorySelect = $('#category');
                        categorySelect.empty(); // Limpa as opções existentes
                        categorySelect.append('<option value="" selected disabled>Selecione uma Categoria</option>');
                        response.categories.forEach(function (category) {
                            categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                        });
                    } else {
                        $(".alert").remove();
                        $("#documentForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Erro na requisição AJAX:', textStatus, errorThrown);
                    console.error('Resposta do servidor:', jqXHR.responseText);
                    $(".alert").remove();
                    $("#documentForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Erro ao se comunicar com o servidor<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                },
            });
        }

        // Carregar categorias ao carregar a página
        loadCategories();

        // Submissão do formulário para criar categoria
        $('#createCategorySendingForm').on('submit', function (e) {
            e.preventDefault(); // Evitar reload da página

            const categoryName = $('#categoryName').val(); // Nome da categoria

            ajaxData = { action: 'register-category-sending', category_name: categoryName };

            // Exibir loader se necessário
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/register-category.php', // Endpoint para salvar a categoria
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#createCategorySendingModal').modal('hide');
                        $('#createCategorySendingForm').trigger('reset');

                        $(".alert").remove();
                        $("#documentForm").before('<div class="alert alert-primary alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                        // Atualizar o select com a nova categoria
                        const newCategory = response.data;
                        $('#category').append(new Option(newCategory.name, newCategory.id, true, true));
                    } else {
                        $(".alert").remove();
                        $("#createCategorySendingForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Erro na requisição AJAX:', textStatus, errorThrown);
                    console.error('Resposta do servidor:', jqXHR.responseText);
                    alert('Erro ao se comunicar com o servidor.');
                },
            });
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
    // Validação do Formulário
    $("#documentForm").validate({
        rules: {
            company: {
                required: true,
            },
            category: {
                required: true,
            },
            department: {
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
            category: {
                required: "Por favor, selecione uma categoria.",
            },
            department: {
                required: "Por favor, selecione um depto.",
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
            formData.append("action", "register-document-sending");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/register.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>documentos-envios";
                    } else {
                        $(".alert").remove();
                        $("#documentForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#documentForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>