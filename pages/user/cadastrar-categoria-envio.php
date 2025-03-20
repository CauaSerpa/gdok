<!-- Modal para criar depto -->
<div class="modal fade" id="createDepartmentSendingModal" tabindex="-1" aria-labelledby="createDepartmentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDepartmentLabel">Criar Depto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createDepartmentSendingForm">
                    <div class="mb-3">
                        <label for="departmentName" class="form-label">Nome do Depto</label>
                        <input type="text" class="form-control" id="departmentName" name="department_name" placeholder="Digite o nome do depto" required>
                    </div>
                    <button class="btn btn-primary" id="btnSubmitDepartment" type="submit">Cadastrar</button>
                    <button class="btn btn-primary loader-btn d-none" id="btnLoaderDepartment" type="button" disabled>
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
            <h4 class="fs-18 fw-semibold m-0">Cadastrar Categoria</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>categorias-envios">Categorias de Envios</a></li>
                <li class="breadcrumb-item active">Cadastrar Categoria de Envio</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Cadastro de Categoria de Envio</h5>
                </div><!-- end card header -->

                <div class="card-body">

                    <form id="categorySendingForm">

                        <!-- Nome da Caregoria de Envio -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome da Categoria de Envio*</label>
                            <input class="form-control" name="name" type="text" id="name" maxlength="120" placeholder="Digite o Nome da Categoria de Envio" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Depto -->
                                <div class="mb-3">
                                    <label for="department" class="form-label">Depto*</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select w-50" name="department" id="department" required>
                                            <option value="" selected disabled>Selecione um Depto</option>
                                        </select>
                                        <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#createDepartmentSendingModal">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar depto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observação -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Observação</label>
                            <textarea class="form-control" name="description" id="description" rows="5" spellcheck="false" placeholder="Digite a Observação"></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">

                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>categorias-envios" class="btn btn-light">Voltar</a>

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
        // Função para carregar depto
        function loadDepartments() {
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category-sending/list-departments.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        const departmentSelect = $('#department');
                        departmentSelect.empty(); // Limpa as opções existentes
                        departmentSelect.append('<option value="" selected disabled>Selecione um Depto</option>');
                        response.departments.forEach(function (department) {
                            departmentSelect.append(`<option value="${department.id}">${department.name}</option>`);
                        });
                    } else {
                        $(".alert").remove();
                        $("#departmentSendingForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Erro na requisição AJAX:', textStatus, errorThrown);
                    console.error('Resposta do servidor:', jqXHR.responseText);
                    $(".alert").remove();
                    $("#departmentSendingForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Erro ao se comunicar com o servidor<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                },
            });
        }

        // Carregar depto ao carregar a página
        loadDepartments();

        // Submissão do formulário para criao depto
        $('#createDepartmentSendingForm').on('submit', function (e) {
            e.preventDefault(); // Evitar reload da página

            const departmentName = $('#departmentName').val(); // Nome do depto

            ajaxData = { action: 'register-department-sending', department_name: departmentName };

            // Exibir loader se necessário
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category-sending/register-department.php', // Endpoint para salvar o depto
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#createDepartmentSendingModal').modal('hide');
                        $('#createDepartmentSendingForm').trigger('reset');

                        $(".alert").remove();
                        $("#departmentSendingForm").before('<div class="alert alert-primary alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                        // Atualizar o select com a novo depto
                        const newDepartment = response.data;
                        $('#department').append(new Option(newDepartment.name, newDepartment.id, true, true));
                    } else {
                        $(".alert").remove();
                        $("#createDepartmentSendingForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
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
$(document).ready(function() {
    // Validação do Formulário
    $("#categorySendingForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
            department: {
                required: true,
            }
        },
        messages: {
            name: {
                required: "Por favor, insira o nome do documento.",
                minlength: "O nome deve ter pelo menos 2 caracteres.",
                maxlength: "O nome pode ter no máximo 120 caracteres.",
            },
            department: {
                required: "Por favor, selecione um depto.",
            }
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
            formData.append("action", "register-category-sending");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category-sending/register.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>categorias-envios";
                    } else {
                        $(".alert").remove();
                        $("#categorySendingForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#categorySendingForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>