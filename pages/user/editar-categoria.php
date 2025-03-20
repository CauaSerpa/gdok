<style>
    .line-height {
        height: 20px;
        width: 1px;
        background: black;
    }
</style>

<!-- Modal de Confirmação -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir a categoria "<span id="categoryName"></span>"?
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
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Token inválido ou ausente. Não foi possível carregar a categoria.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'tipos-documentos');
        exit;
    }

    include('back-end/user/category/functions.php');

    // Validar o token (por exemplo, verificar se existe no banco de dados)
    $category_id = $token;
    $category = getCategoryByToken($category_id, $conn); // Suponha que essa função pegue a categoria pelo token

    // Se não encontrar a categoria com o token, redirecionar
    if (!$category) {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Categoria não encontrada.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'tipos-documentos');
        exit;
    }

    // Caso a categoria exista, preencher os campos com os dados da categoria
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Editar Categoria</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>tipos-documentos">Tipos de Documentos</a></li>
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>categorias">Categorias</a></li>
                <li class="breadcrumb-item active">Editar Categoria</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Edição de Categoria</h5>
                </div><!-- end card header -->

                <div class="card-body">

                    <form id="categoryForm">

                        <!-- Nome da Caregoria -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome da Categoria</label>
                            <input class="form-control" name="name" type="text" id="name" value="<?= $category['name']; ?>" maxlength="120" placeholder="Digite o Nome da Categoria" required>
                        </div>

                        <!-- Observação -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Observação</label>
                            <textarea class="form-control" name="description" id="description" rows="5" spellcheck="false" placeholder="Digite a Observação"><?= $category['description']; ?></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">

                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>categorias" class="btn btn-light">Voltar</a>

                            <div>

                                <button class="btn btn-danger btn-delete" type="button" data-id="<?= $category['id']; ?>" data-name="<?= $category['name']; ?>">Excluir</button>

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
        let elementIdToDelete = null;

        // Quando clicar no botão de exclusão
        $('.btn-delete').on('click', function () {
            elementIdToDelete = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            elementNameToDelete = $(this).data('name'); // Obtém o ID do elemento a ser excluído
            $('#categoryName').text(elementNameToDelete); // Mostra o modal
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category/delete.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        if (response.status == "success") {
                            window.location.href = '<?= INCLUDE_PATH_DASHBOARD; ?>categorias';
                        } else {
                            location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#categoryForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
            }
        });
    });
</script>

<script>
$(document).ready(function() {
    // Validação do Formulário
    $("#categoryForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
            }
        },
        messages: {
            name: {
                required: "Por favor, insira seu nome",
                minlength: "Seu nome deve ter pelo menos 2 caracteres",
            }
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.next("label"));
            } else if (element.prop("type") === "select-one") {
                error.insertAfter(element.next("span.select2"));
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
            formData.append("category_id", <?= $category_id; ?>);
            formData.append("action", "update-category");

            // Realiza o AJAX para enviar os dados
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category/update.php', // Substitua pelo URL do seu endpoint
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
                        $("#categoryForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#categoryForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>