<style>
    .line-height {
        height: 20px;
        margin: 8px 0;
        width: 1px;
        background: black;
    }
</style>

<!-- Modal para criar categoria -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCategoryLabel">Criar Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createCategoryForm">
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

<!-- Modal de Confirmação -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir o tipo de documento "<span id="documentTypeName"></span>"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
                <button type="button" id="confirmDelete" class="btn btn-danger">Excluir</button>
            </div>
        </div>
    </div>
</div>

<?php
    // Verificar se o token foi fornecido
    if (!isset($token) || empty($token)) {
        // Redirecionar para a página de tipos de documentos com uma mensagem de erro
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Token inválido ou ausente. Não foi possível carregar o tipo de documento.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'tipos-documentos');
        exit;
    }

    include('back-end/user/document-types/document-type-functions.php');

    // Validar o token (por exemplo, verificar se existe no banco de dados)
    $document_type_id = $token;
    $documentType = getDocumentTypeByToken($document_type_id, $conn); // Suponha que essa função pegue a tipo de documento pelo token

    // Se não encontrar a tipo de documento com o token, redirecionar
    if (!$documentType) {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Tipo de documento não encontrada.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'tipos-documentos');
        exit;
    }

    // Caso a tipo de documento exista, preencher os campos com os dados da tipo de documento
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Editar Tipo de Documento</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>tipos-documentos">Tipos de Documentos</a></li>
                <li class="breadcrumb-item active">Editar Tipo de Documento</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Edição de Tipo de Documento</h5>
                </div><!-- end card header -->

                <div class="card-body">

                    <form id="documentTypeForm">

                        <!-- Nome do Documento -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do Documento*</label>
                            <input class="form-control" name="name" type="text" id="name" value="<?= htmlspecialchars($documentType['name']); ?>" maxlength="120" placeholder="Digite o Nome do Documento" required>
                        </div>

                        <!-- Categoria -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Categoria*</label>
                            <div class="d-flex align-items-center">
                                <select class="form-select w-25" name="category" id="category" required>
                                    <option value="" disabled>Selecione uma Categoria</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id']; ?>" <?= $category['id'] == $documentType['category_id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                    <i class="mdi mdi-plus fs-16 align-middle"></i> Criar categoria
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <!-- Prioridade -->
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Prioridade*</label>
                                    <select class="form-select" name="priority" id="priority" required>
                                        <option value="" disabled>Selecione a Prioridade</option>
                                        <option value="low" <?= $documentType['priority'] == 'low' ? 'selected' : ''; ?>>Baixa</option>
                                        <option value="average" <?= $documentType['priority'] == 'average' ? 'selected' : ''; ?>>Média</option>
                                        <option value="high" <?= $documentType['priority'] == 'high' ? 'selected' : ''; ?>>Alta</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-10">
                                <!-- Notificação Antecipada -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Notificação Antecipada*</label>

                                    <div class="radio-options d-flex">
                                        <div class="options">
                                            <input type="radio" class="btn-check" name="advance_notification" id="option1" autocomplete="off" value="7" <?= $documentType['advance_notification'] == 7 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option1">7 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option2" autocomplete="off" value="15" <?= $documentType['advance_notification'] == 15 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option2">15 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option3" autocomplete="off" value="30" <?= $documentType['advance_notification'] == 30 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option3">30 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option4" autocomplete="off" value="45" <?= $documentType['advance_notification'] == 45 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option4">45 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option5" autocomplete="off" value="90" <?= $documentType['advance_notification'] == 90 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option5">90 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option6" autocomplete="off" value="personalized" <?= $documentType['advance_notification'] == 'personalized' ? 'checked' : ''; ?>>
                                            <label class="btn" for="option6">Personalizado</label>
                                        </div>

                                        <div id="advanceNotificationCustomize" class="<?= $documentType['advance_notification'] == 'personalized' ? 'd-flex' : 'd-none'; ?>">
                                            <span class="line-height mx-2"></span>
                                            <div class="position-relative">
                                                <input class="form-control" name="personalized_advance_notification" type="number" id="personalized_advance_notification" value="<?= $documentType['advance_notification'] == 'personalized' ? $documentType['personalized_advance_notification'] : ''; ?>" placeholder="Digite o Período" style="width: 150px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>tipos-documentos" class="btn btn-light">Voltar</a>

                            <div>

                                <button class="btn btn-danger btn-delete" type="button" data-id="<?= $documentType['id']; ?>" data-name="<?= $documentType['name']; ?>">Excluir</button>

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

<!-- Adicionar máscaras e validação -->
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        // Função para carregar categorias
        function loadCategories() {
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-types/list-document-type-categories.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        const categorySelect = $('#category');
                        categorySelect.empty(); // Limpa as opções existentes
                        categorySelect.append('<option value="" selected disabled>Selecione uma Categoria</option>');

                        // Preenche as opções de categoria
                        response.categories.forEach(function (category) {
                            categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                        });

                        // Seleciona a categoria atual do documentType
                        const selectedCategoryId = <?= $documentType['category_id']; ?>; // Atribua o category_id do banco de dados
                        if (selectedCategoryId) {
                            categorySelect.val(selectedCategoryId); // Marca a categoria selecionada
                        }
                    } else {
                        $(".alert").remove();
                        $("#documentTypeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Erro na requisição AJAX:', textStatus, errorThrown);
                    console.error('Resposta do servidor:', jqXHR.responseText);
                    $(".alert").remove();
                    $("#documentTypeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Erro ao se comunicar com o servidor<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                },
            });
        }

        // Carregar categorias ao carregar a página
        loadCategories();

        // Submissão do formulário para criar categoria
        $('#createCategoryForm').on('submit', function (e) {
            e.preventDefault(); // Evitar reload da página
            
            const categoryName = $('#categoryName').val(); // Nome da categoria

            ajaxData = { action: 'register-document-type-category', category_name: categoryName };

            // Exibir loader se necessário
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-types/register-document-type-category.php', // Endpoint para salvar a categoria
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#createCategoryModal').modal('hide');
                        $('#createCategoryForm').trigger('reset');

                        $(".alert").remove();
                        $("#documentTypeForm").before('<div class="alert alert-primary alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                        // Atualizar o select com a nova categoria
                        const newCategory = response.data;
                        $('#category').append(new Option(newCategory.name, newCategory.id, true, true));
                    } else {
                        $(".alert").remove();
                        $("#createCategoryForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
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
        // Monitorar mudanças nas opções de notificação antecipada
        $('input[name="advance_notification"]').on('change', function () {
            if ($('#option6').is(':checked')) {
                // Se a opção "Personalizado" for selecionada, mostra o div
                $("#personalized_advance_notification").val("");
                $('#advanceNotificationCustomize').removeClass('d-none').addClass('d-flex');
            } else {
                // Caso contrário, esconde o div
                $('#advanceNotificationCustomize').removeClass('d-flex').addClass('d-none');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        let elementIdToDelete = null;

        // Quando clicar no botão de exclusão
        $('.btn-delete').on('click', function () {
            elementIdToDelete = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            elementNameToDelete = $(this).data('name'); // Obtém o ID do elemento a ser excluído
            $('#documentTypeName').text(elementNameToDelete); // Mostra o modal
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-types/delete-document-type.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        window.location.href = '<?= INCLUDE_PATH_DASHBOARD; ?>tipos-documentos';
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#documentTypeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
            }
        });
    });
</script>

<script>
$(document).ready(function() {
    // Validação do Formulário
    $("#documentTypeForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
                maxlength: 120,
            },
            category: {
                required: true,
            },
            priority: {
                required: true,
            },
            advance_notification: {
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
            name: {
                required: "Por favor, insira o nome do documento.",
                minlength: "O nome deve ter pelo menos 2 caracteres.",
                maxlength: "O nome pode ter no máximo 120 caracteres.",
            },
            category: {
                required: "Por favor, selecione uma categoria.",
            },
            priority: {
                required: "Por favor, selecione uma prioridade.",
            },
            advance_notification: {
                required: "Por favor, selecione uma opção de notificação antecipada.",
            },
            personalized_advance_notification: {
                required: "Por favor, insira o período personalizado.",
                min: "O valor deve ser no mínimo 1.",
            },
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            console.log(element.prop("id"));
            if (element.prop("type") === "radio") {
                error.insertAfter(element.closest(".radio-options"));
            } else if (element.prop("type") === "select-one" && element.prop("id") === "category") {
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
        submitHandler: function(form) {
            // Impede o envio padrão do formulário
            event.preventDefault(); 

            // Define os botões como variáveis
            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            // Desabilitar botão submit e habilitar loader
            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            // Cria um objeto FormData a partir do formulário
            var formData = new FormData(form);

            // Adiciona um novo campo
            formData.append("document_type_id", <?= $document_type_id; ?>);
            formData.append("action", "update-document-type");

            // Realiza o AJAX para enviar os dados
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-types/update-document-type.php', // Substitua pelo URL do seu endpoint
                type: 'POST',
                data: formData,
                processData: false, // Impede que o jQuery processe os dados
                contentType: false, // Impede que o jQuery defina o Content-Type
                success: function (response) {
                    if (response.status == "success") {
                        location.reload();
                    } else {
                        // console.error("Erro no AJAX:", status, error);

                        // Caso contrário, exibe a mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#documentTypeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#documentTypeForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>