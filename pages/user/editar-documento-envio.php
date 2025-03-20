<style>
    .line-height {
        height: 20px;
        margin: 8px 0;
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
                Tem certeza de que deseja excluir o documento de envio<span id="documentName"></span>?
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
        // Redirecionar para a página de documentos de envio com uma mensagem de erro
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Token inválido ou ausente. Não foi possível carregar o documento.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'documentos-envios');
        exit;
    }

    include('back-end/user/document-sending/functions.php');

    // Validar o token (por exemplo, verificar se existe no banco de dados)
    $document_id = $token;
    $document = getDocumentByToken($document_id, $conn); // Suponha que essa função pegue a documento pelo token

    // Se não encontrar a documento com o token, redirecionar
    if (!$document) {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Documento de envio não encontrada.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'documentos-envios');
        exit;
    }

    $document['price'] = number_format($document['price'], 2, ',', '.');

    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar documentos de envio cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_sending_categories WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar deptos cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_sending_departments WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Caso o documento exista, preencher os campos com os dados do documento
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Editar Documento de Envio</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos-envios">Documentos</a></li>
                <li class="breadcrumb-item active">Editar Documento de Envio</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">Edição de Documento</h5>
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
                                                <option value="<?= htmlspecialchars($company['id']); ?>" <?= $company['id'] == $document['company_id'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($company['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-empresa-envio" class="btn btn-link" onclick="confirm('Você será redirecionado para a página de cadastro de empresa. Deseja continuar?')">
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
                                                <option value="<?= htmlspecialchars($department['id']); ?>" <?= $department['id'] == $document['department_id'] ? 'selected' : ''; ?>>
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
                                    <label for="category" class="form-label">Categorias*</label>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select w-50" name="category" id="category" required>
                                            <option value="" disabled>Selecione a Categoria</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= htmlspecialchars($category['id']); ?>" <?= $category['id'] == $document['category_id'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-categoria-envio" class="btn btn-link" onclick="confirm('Você será redirecionado para a página de cadastro de categoria de envio. Deseja continuar?')">
                                            <i class="mdi mdi-plus fs-16 align-middle"></i>
                                            Criar Categoria de Envio
                                        </a>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Arquivo -->
                        <div class="mb-3">
                            <label for="document" class="form-label">Selecione o Documento</label>
                            <input class="form-control" name="document" type="file" id="document" accept=".jpg,.png,.pdf,.doc,.docx,.xls,.xlsx,.pfx,.p12">
                        </div>

                        <?php if ($document['document']): ?>
                            <div class="mb-3">
                                <label for="current_document" class="form-label">Documento Atual</label>
                                <div>
                                    <a href="<?= $document['document']; ?>" class="btn btn-primary btn-sm" target="_blank" data-bs-toggle="tooltip" title="Baixar Documento">
                                        <i class="mdi mdi-download fs-16 align-middle"></i>
                                        Baixar
                                    </a>
                                    <a href="<?= $document['document']; ?>" class="btn btn-sm btn-link" target="_blank" data-bs-toggle="tooltip" title="Pré-visualização"><?= basename($document['document']); ?></a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Nome do Documento -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do Documento*</label>
                            <input class="form-control" name="name" type="text" id="name" value="<?= htmlspecialchars($document['name']); ?>" maxlength="120" placeholder="Digite o Nome do Documento" required>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <!-- Referência -->
                                <div class="mb-3">
                                    <label for="reference" class="form-label">Referência</label>
                                    <input class="form-control" name="reference" type="text" id="reference" placeholder="mm/aaaa" value="<?= htmlspecialchars(date("m/Y", strtotime($document['reference']))); ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <!-- Data de Vencimento -->
                                <div class="mb-3">
                                    <label for="expiration_date" class="form-label">Data de Vencimento*</label>
                                    <input class="form-control" name="expiration_date" type="date" id="expiration_date" value="<?= htmlspecialchars($document['expiration_date']); ?>" required>
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
                                        <input class="form-control text-end" name="price" type="text" id="price" value="<?= htmlspecialchars($document['price']); ?>">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Observação -->
                        <div class="mb-3">
                            <label for="observation" class="form-label">Observação</label>
                            <textarea class="form-control" name="observation" id="observation" rows="5" spellcheck="false"><?= htmlspecialchars($document['observation']); ?></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos-envios" class="btn btn-light">Voltar</a>

                            <div>

                                <button class="btn btn-danger btn-delete" type="button" data-id="<?= $document['id']; ?>" data-name="<?= $document['name']; ?>">Excluir</button>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

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
<script>
    $(document).ready(function() {
        $('#price').mask("#.##0,00", {reverse: true});
    });
</script>

<script>
    $(document).ready(function () {
        function loadCategories(selectedCategoryId = null) {
            const departmentId = $('#department').val();
            const categorySelect = $('#category');

            if (departmentId) {
                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/list-categories.php',
                    type: 'GET',
                    data: { department: departmentId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            categorySelect.empty();
                            categorySelect.append('<option value="" selected disabled>Selecione a Categoria</option>');
                            response.categories.forEach(function (category) {
                                if (selectedCategoryId && category.id == selectedCategoryId) {
                                    categorySelect.append(`<option value="${category.id}" selected>${category.name}</option>`);
                                } else {
                                    categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                                }
                            });
                            $('#categoryContent').show();
                        } else {
                            $('#categoryContent').hide();
                            alert(response.message);
                        }
                    },
                    error: function () {
                        $('#categoryContent').hide();
                        alert('Erro ao carregar categorias.');
                    }
                });
            } else {
                $('#categoryContent').hide();
            }
        }

        // Ao carregar a página, se houver um departamento já selecionado, carrega as categorias com a opção previamente selecionada
        const selectedDepartment = $('#department').val();
        // Obtém a categoria selecionada na renderização (caso exista)
        const preselectedCategory = $('#category option[selected]').val();

        if (selectedDepartment) {
            loadCategories(preselectedCategory);
        } else {
            $('#categoryContent').hide();
        }

        // Se o departamento for alterado, recarrega as categorias (sem pré-seleção)
        $('#department').change(function () {
            loadCategories();
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
            const elementNameToDelete = $(this).data('name'); // Obtém o ID do elemento a ser excluído
            if (elementNameToDelete || elementNameToDelete.trim() !== "") {
                $('#documentName').text(' "'+elementNameToDelete+'"'); // Mostra o modal
            }
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/delete.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        window.location.href = '<?= INCLUDE_PATH_DASHBOARD; ?>documentos';
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#documentForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
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
            formData.append("document_id", <?= $document_id; ?>);
            formData.append("action", "update-document-sending");

            // Realiza o AJAX para enviar os dados
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/update.php', // Substitua pelo URL do seu endpoint
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
                        $("#documentForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    // Caso haja erro na requisição, exibe uma mensagem de erro
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#documentForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        }
    });
});
</script>