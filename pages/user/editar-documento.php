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
                Tem certeza de que deseja excluir o documento<span id="documentName"></span>?
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
        // Redirecionar para a página de documentos com uma mensagem de erro
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Token inválido ou ausente. Não foi possível carregar o documento.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'documentos');
        exit;
    }

    include('back-end/user/document/functions.php');

    // Validar o token (por exemplo, verificar se existe no banco de dados)
    $document_id = $token;
    $document = getDocumentByToken($document_id, $conn); // Suponha que essa função pegue a documento pelo token

    // Se não encontrar a documento com o token, redirecionar
    if (!$document) {
        // Defina a mensagem de erro na sessão
        $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Documento não encontrada.');
        $_SESSION['msg'] = $message;
        header('Location: ' . INCLUDE_PATH_DASHBOARD . 'documentos');
        exit;
    }

    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar documentos cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_document_types WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $document_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Caso o documento exista, preencher os campos com os dados do documento
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Editar Documento</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos">Documentos</a></li>
                <li class="breadcrumb-item active">Editar Documento</li>
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
                                                <option value="<?= htmlspecialchars($document_type['id']); ?>" <?= $document_type['id'] == $document['document_type_id'] ? 'selected' : ''; ?>>
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
                            <label for="name" class="form-label">Nome do Documento</label>
                            <input class="form-control" name="name" type="text" id="name" value="<?= htmlspecialchars($document['name']); ?>" maxlength="120" placeholder="Digite o Nome do Documento">
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <!-- Data de Validade -->
                                <div class="mb-3">
                                    <label for="expiration_date" class="form-label">Data de Validade*</label>
                                    <input class="form-control" name="expiration_date" type="date" id="expiration_date" value="<?= htmlspecialchars($document['expiration_date']); ?>">
                                </div>
                            </div>

                            <div class="col-md-10">
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
                                            <input type="radio" class="btn-check" name="advance_notification" id="option1" autocomplete="off" value="7" <?= $document['advance_notification'] == 7 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option1">7 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option2" autocomplete="off" value="15" <?= $document['advance_notification'] == 15 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option2">15 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option3" autocomplete="off" value="30" <?= $document['advance_notification'] == 30 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option3">30 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option4" autocomplete="off" value="45" <?= $document['advance_notification'] == 45 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option4">45 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option5" autocomplete="off" value="90" <?= $document['advance_notification'] == 90 ? 'checked' : ''; ?>>
                                            <label class="btn" for="option5">90 dias</label>
        
                                            <input type="radio" class="btn-check" name="advance_notification" id="option6" autocomplete="off" value="personalized" <?= $document['advance_notification'] == 'personalized' ? 'checked' : ''; ?>>
                                            <label class="btn" for="option6">Personalizado</label>
                                        </div>

                                        <div id="advanceNotificationCustomize" class="<?= $document['advance_notification'] == 'personalized' ? 'd-flex' : 'd-none'; ?>">
                                            <span class="line-height mx-2"></span>
                                            <div class="position-relative">
                                                <input class="form-control" name="personalized_advance_notification" type="number" id="personalized_advance_notification" value="<?= $document['advance_notification'] == 'personalized' ? $document['personalized_advance_notification'] : ''; ?>" placeholder="Digite o Período" style="width: 150px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observação -->
                            <div class="mb-3">
                                <label for="observation" class="form-label">Observação</label>
                                <textarea class="form-control" name="observation" id="observation" rows="5" spellcheck="false"><?= htmlspecialchars($document['observation']); ?></textarea>
                            </div>

                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos" class="btn btn-light">Voltar</a>

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
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/delete.php?id=${elementIdToDelete}`,
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
            formData.append("action", "update-document");

            // Realiza o AJAX para enviar os dados
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/update.php', // Substitua pelo URL do seu endpoint
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