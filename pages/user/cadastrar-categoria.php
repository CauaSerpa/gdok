<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Cadastrar Categoria</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>tipos-documentos">Tipos de Documentos</a></li>
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>categorias">Categorias</a></li>
                <li class="breadcrumb-item active">Cadastrar Categoria</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Cadastro de Categoria</h5>
                </div><!-- end card header -->

                <div class="card-body">

                    <form id="categoryForm">

                        <!-- Nome da Caregoria -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome da Categoria</label>
                            <input class="form-control" name="name" type="text" id="name" maxlength="120" placeholder="Digite o Nome da Categoria" required>
                        </div>

                        <!-- Descrição da Categoria -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição da Categoria</label>
                            <textarea class="form-control" name="description" id="description" rows="5" spellcheck="false" placeholder="Digite a Descrição da Categoria"></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">

                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>categorias" class="btn btn-light">Voltar</a>

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
$(document).ready(function() {
    // Validação do Formulário
    $("#categoryForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
            description: {
                maxlength: 255,
            },
        },
        messages: {
            name: {
                required: "Por favor, insira o nome do documento.",
                minlength: "O nome deve ter pelo menos 2 caracteres.",
                maxlength: "O nome pode ter no máximo 120 caracteres.",
            },
            description: {
                maxlength: "O nome pode ter no máximo 255 caracteres.",
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
            formData.append("action", "register-category");

            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category/register.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>categorias";
                    } else {
                        $(".alert").remove();
                        $("#categoryForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#categoryForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>