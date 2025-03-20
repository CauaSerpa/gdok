<style>
    .w-200 {
        width: 200px !important;
    }

    #documentsTable_paginate .pagination {
        display: none;
    }
    .dataTables_empty {
        text-align: center;
    }
</style>

<?php
    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_sending_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar categorias cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_sending_departments WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar categorias cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_sending_categories WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Offcanvas de Filtro -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="filterForm">
            <div class="mb-3">
                <label for="startDateReferenceFilter" class="form-label">Período</label>
                <div class="date-fields reference row">
                    <div class="col-6">
                        <input type="text" id="startDateReferenceFilter" class="form-control" placeholder="mm/aaaa">
                        <small>De</small>
                    </div>
                    <div class="col-6">
                        <input type="text" id="endDateReferenceFilter" class="form-control" placeholder="mm/aaaa">
                        <small>Até</small>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="companyFilter" class="form-label">Empresa</label>
                <select id="companyFilter" class="form-select">
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= htmlspecialchars($company['id']); ?>">
                            <?= htmlspecialchars($company['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="departmentFilter" class="form-label">Depto</label>
                <select id="departmentFilter" class="form-select">
                    <option value="">Selecione uma depto</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= htmlspecialchars($department['id']); ?>">
                            <?= htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="categoryFilter" class="form-label">Categoria</label>
                <select id="categoryFilter" class="form-select">
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']); ?>">
                            <?= htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="startDateFilter" class="form-label">Vencimento</label>
                <div class="date-fields row">
                    <div class="col-6">
                        <input type="date" id="startDateFilter" class="form-control">
                        <small>De</small>
                    </div>
                    <div class="col-6">
                        <input type="date" id="endDateFilter" class="form-control">
                        <small>Até</small>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="minPriceFilter" class="form-label">Valor</label>
                <div class="date-fields row">
                    <div class="col-6">
                        <div class="input-group">
                            <div class="input-group-text">R$</div>
                            <input type="text" id="minPriceFilter" class="form-control" placeholder="Ex.: 100,00">
                        </div>
                        <small>Min.</small>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <div class="input-group-text">R$</div>
                            <input type="text" id="maxPriceFilter" class="form-control" placeholder="Ex.: 10.000,00">
                        </div>
                        <small>Máx.</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer d-flex align-items-center justify-content-between">
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas" aria-label="Close">Voltar</button>
        <div>
            <button id="clearFiltersButton" type="button" class="btn btn-link d-none">Limpar Filtros</button>
            <button id="applyFiltersButton" type="button" class="btn btn-primary">Aplicar Filtros</button>
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
                Tem certeza de que deseja excluir o documento<span id="documentName"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
                <button type="button" id="confirmDelete" class="btn btn-danger">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para informar renovação -->
<div class="modal fade" id="renewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewModalLabel">Renovar Documento<span id="documentId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="informRenewal" class="d-none">
                    <h6 class="fs-15">Como deseja continuar?</h6>
                    <div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="option_renew" id="option_renew_1" value="no_renewal">
                            <label class="form-check-label" for="option_renew_1">
                                Documento não será renovado
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="option_renew" id="option_renew_2" value="renew_later">
                            <label class="form-check-label" for="option_renew_2">
                                Documento a ser renovado
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="option_renew" id="option_renew_3" value="renew_with_new">
                            <label class="form-check-label" for="option_renew_3">
                                Documento renovado, informar novo vencimento
                            </label>
                        </div>
                    </div>
                </form>

                <form id="renewForm">
                    <!-- Novo Documento -->
                    <div class="mb-3">
                        <label for="document" class="form-label">Novo Documento</label>
                        <input class="form-control" name="document" type="file" id="document" accept=".jpg,.png,.pdf,.doc,.docx,.xls,.xlsx,.pfx,.p12">
                    </div>

                    <!-- Nova Data de Validade -->
                    <div class="row">
                        <div class="col-md-4">
                            <div>
                                <label for="expiration_date" class="form-label">Nova Data de Validade</label>
                                <input class="form-control" name="expiration_date" type="date" id="expiration_date">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex aling-items-center justify-content-between">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <div>
                    <button class="btn btn-primary" id="btnNextStep" type="submit">Continuar</button>
                    <button class="btn btn-primary d-none" id="btnSubmit" type="submit">Salvar</button>
                    <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status">Carregando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Documento de Envio</h4>
    </div>

    <div class="text-end">
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-documento-envio" class="btn btn-dark">
            <i class="mdi mdi-plus fs-16 align-middle"></i>
            Upload de Documento de Envio
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 text-black">Documentos de envio cadastrados</h5>
                    <div class="ms-auto d-flex">
                        <div class="dropdown me-2">
                            <button class="btn btn-primary btn-sm dropdown-toggle fs-14" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Exportar
                                <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button id="exportCSV" class="dropdown-item">Exportar CSV</button></li>
                                <li><button id="exportXLS" class="dropdown-item">Exportar XLS</button></li>
                                <li><button id="exportPDF" class="dropdown-item">Exportar PDF</button></li>
                                <li><button id="exportDocx" class="dropdown-item">Exportar DOCX</button></li>
                                <li><button id="exportPrint" class="dropdown-item">Imprimir</button></li>
                            </ul>
                        </div>
                        <button class="btn btn-sm bg-light border dropdown-toggle fw-medium text-black" type="button" data-bs-toggle="offcanvas" href="#filterOffcanvas" role="button" aria-controls="filterOffcanvas">
                            <i class="mdi mdi-filter-outline me-1 fs-14"></i>Filtrar Documentos</i>
                        </button>
                    </div>
                </div>
                <div class="active-filters mt-3 d-none">
                    <h6 class="text-uppercase fs-13">Filtros</h6>

                    <div id="filterList" class="d-flex flex-wrap gap-2"></div>

                </div>
            </div>

            <div class="card-body p-0">
                <table id="documentsTable" class="table table-traffic mb-0">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Categoria</th>
                            <th>Depto</th>
                            <th>Arquivo</th>
                            <th>Referência</th>
                            <th>Valor</th>
                            <th>Vencimento do Documento</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="card-footer py-0 border-top">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <div class="text-block text-center text-sm-start">
                            <span id="info" class="fw-medium">1 de 3</span>
                        </div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <div class="pagination gap-2 justify-content-center py-3 ps-0 pe-3">
                            <ul id="pagination" class="pagination mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link me-2 rounded-2" href="javascript:void(0);" id="prevPage">Anterior</a>
                                </li>
                                <!-- Dynamically generated page numbers will be inserted here -->
                                <li class="page-item liNextPage">
                                    <a class="page-link text-primary rounded-2" href="javascript:void(0);" id="nextPage">Próxima</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

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
    $('#startDateReferenceFilter, #endDateReferenceFilter').mask("00/0000");

    // Inicializa o datepicker configurado para mês/ano
    $('#startDateReferenceFilter, #endDateReferenceFilter').datepicker({
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
        $('#minPriceFilter, #maxPriceFilter').mask("#.##0,00", {reverse: true});
    });
</script>


<script>
    $(document).ready(function() {
        // Função para converter um valor "MM/YYYY" em um objeto Date (primeiro dia do mês)
        function parseMonth(value) {
            if (!value) return null;
            value = value.trim();
            if (!value) return null;
            var parts = value.split('/');
            if (parts.length === 2) {
                var month = parseInt(parts[0], 10);
                var year = parseInt(parts[1], 10);
                if (!isNaN(month) && !isNaN(year)) {
                    return new Date(year, month - 1, 1);
                }
            }
            return null;
        }

        // Função para formatar um objeto Date para o padrão "MM/YYYY"
        function formatMonthToBR(dateObj) {
            if (!dateObj) return '';
            // Se não for um objeto Date, tente convertê-lo
            if (!(dateObj instanceof Date)) {
                dateObj = parseMonth(dateObj);
                if (!(dateObj instanceof Date)) return '';
            }
            var month = dateObj.getMonth() + 1;
            var year = dateObj.getFullYear();
            if (month < 10) month = "0" + month;
            return month + "/" + year;
        }

        // Anexa a máscara e o datepicker para os inputs de mês
        $('#startDateReferenceFilter, #endDateReferenceFilter').mask("00/0000");
        $('#startDateReferenceFilter, #endDateReferenceFilter').datepicker({
            format: "mm/yyyy",
            startView: "months",
            minViewMode: "months",
            autoclose: true
        });

        // Evento para o input de início: detecta tanto "change" quanto "changeDate"
        $('#startDateReferenceFilter').on('change changeDate', function() {
            var startMonth = $(this).val(); // Esperado no formato "MM/YYYY"
            var startDateObj = parseMonth(startMonth);
            if (startDateObj) {
                var formattedMin = formatMonthToBR(startDateObj); 
                $('#endDateReferenceFilter').attr('min', formattedMin);

                var endMonth = $('#endDateReferenceFilter').val();
                var endDateObj = parseMonth(endMonth);
                if (startDateObj && endDateObj && endDateObj < startDateObj) {
                    $('#dateError').remove();
                    $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início</div>')
                        .insertAfter('.date-fields.reference').show();
                    $('#endDateReferenceFilter').val('');
                } else {
                    $('#dateError').remove();
                }
            } else {
                $('#endDateReferenceFilter').removeAttr('min');
            }
        });

        // Evento para o input de término: detecta "blur" e "changeDate"
        $('#endDateReferenceFilter').on('blur changeDate', function() {
            var startMonth = $('#startDateReferenceFilter').val();
            var endMonth = $(this).val();
            var startDateObj = parseMonth(startMonth);
            var endDateObj = parseMonth(endMonth);
            if (startDateObj && endDateObj && endDateObj < startDateObj) {
                $('#dateError').remove();
                $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início</div>')
                    .insertAfter('.date-fields.reference').show();
                $(this).val('');
            } else {
                $('#dateError').remove();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Função para configurar a data mínima no end date
        $('#startDateFilter').on('change', function() {
            var startDate = $(this).val();

            // Verifica se a data do start date foi preenchida
            if (startDate) {
                // Formata a data para o formato YYYY-MM-DD
                var startDateObj = new Date(startDate);

                // Atualiza o minDate do end date
                var formattedEndDate = startDateObj.toISOString().split('T')[0]; // Formata para o formato adequado
                $('#endDateFilter').attr('min', formattedEndDate);

                var startDate = $(this).val();
                var endDate = $('#endDateFilter').val();

                // Verifica se o end date é menor que o start date
                if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                    // Remove o erro se os campos forem corrigidos
                    $('#dateError').remove();
                    $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início</div>')
                        .insertAfter('.date-fields').show(); // Adiciona a mensagem de erro após o campo de data de término
                    $('#endDateFilter').val(''); // Limpa o campo end date
                } else {
                    // Remove o erro se os campos forem corrigidos
                    $('#dateError').remove();
                }
            } else {
                // Remove o minDate do end date caso o start date seja apagado
                $('#endDateFilter').removeAttr('min');
            }
        });

        // Validação para impedir a seleção de uma data menor no end date
        $('#endDateFilter').on('blur', function() {
            var startDate = $('#startDateFilter').val();
            var endDate = $(this).val();

            // Verifica se o end date é menor que o start date
            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                // Remove o erro se os campos forem corrigidos
                $('#dateError').remove();
                $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início</div>')
                    .insertAfter('.date-fields').show(); // Adiciona a mensagem de erro após o campo de data de término
                $(this).val(''); // Limpa o campo end date
            } else {
                // Remove o erro se os campos forem corrigidos
                $('#dateError').remove();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#companyFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione uma empresas',
            allowClear: true,
        });

        $('#departmentFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione um depto',
            allowClear: true,
        });

        $('#categoryFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione uma categorias',
            allowClear: true,
        });
    });
</script>

<script>
    $(document).ready(function () {
        let elementIdToDelete = null;

        // Quando clicar no botão de exclusão
        $(document).on('click', '.btn-delete', function () {
            elementIdToDelete = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            const elementNameToDelete = $(this).data('name'); // Obtém o nome do elemento a ser excluído
            if (elementNameToDelete || elementNameToDelete.trim() !== "") {
                $('#documentName').text(' "'+elementNameToDelete+'"'); // Mostra o modal
            }
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            console.log(elementIdToDelete);
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/delete.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#documentsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        let elementIdToRenew = null;

        // Quando clicar no botão de exclusão
        $(document).on('click', '.btn-renew', function () {
            $(".alert").remove();
            $('#informRenewal').removeClass('d-none'); // Mostra campos
            $('#renewForm').addClass('d-none'); // Ocultar opcoes
            $("#btnNextStep").removeClass("d-none");
            $("#btnSubmit").addClass("d-none");
            $('#renewForm')[0].reset();
            $('#informRenewal')[0].reset();

            elementIdToRenew = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            const elementNameToRenew = $(this).data('name'); // Obtém o nome do elemento a ser excluído
            if (elementIdToRenew || elementIdToRenew.trim() !== "") {
                $('#documentId').text(' #'+elementIdToRenew+''); // Mostra o modal
            }
            if (elementNameToRenew || elementNameToRenew.trim() !== "") {
                $('#documentName').text(' "'+elementNameToRenew+'"'); // Mostra o modal
            }
            $('#renewModal').modal('show'); // Mostra o modal
        });

        $('#expiration_date').on('change', function() {
            $('#expirationDateError').remove();
        });

        $("#btnSubmit").click(function () {
            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            let expirationDate = $("#expiration_date").val(); // Obtém a nova data de validade

            // Verifica se o campo de validade está vazio
            if (!expirationDate) {
                // Remove o erro se os campos forem corrigidos
                $('#expirationDateError').remove();
                $('<div id="expirationDateError" class="invalid-feedback">Por favor, preencha a nova data de validade antes de continuar</div>')
                    .insertAfter('#expiration_date').show(); // Adiciona a mensagem de erro após o campo de data de término

                btnSubmit.prop("disabled", false).removeClass("d-none");
                btnLoader.addClass("d-none");
                return;
            }

            var form = $('#renewForm')[0];
            var formData = new FormData(form);

            formData.append('document_id', elementIdToRenew);
            formData.append('action', 'renew-with-new');

            // Enviar AJAX confirmando que o documento não será renovado
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/renew.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>documentos-envios";
                    } else {
                        $(".alert").remove();
                        $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        });

        let selectedOption = "";

        // Monitora a seleção do usuário e altera a interface conforme a opção escolhida
        $("input[name='option_renew']").change(function () {
            selectedOption = $(this).val();
        });

        // Ação ao clicar no botão "Continuar"
        $("#btnNextStep").click(function () {
            if (selectedOption === "no_renewal") {
                var btnSubmit = $("#btnNextStep");
                var btnLoader = $("#btnLoader");

                btnSubmit.prop("disabled", true).addClass("d-none");
                btnLoader.removeClass("d-none");

                ajaxData = {
                    'document_id': elementIdToRenew,
                    'action': 'no-renewal'
                };

                // Enviar AJAX confirmando que o documento não será renovado
                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/renew.php',
                    type: 'POST',
                    data: ajaxData,
                    success: function (response) {
                        if (response.status === "success") {
                            window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>documentos-envios";
                        } else {
                            $(".alert").remove();
                            $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        }
                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro no AJAX:", status, error);

                        $(".alert").remove();
                        $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    }
                });
            } else if (selectedOption === "renew_with_new") {
                // Caso o usuário vá enviar um novo documento, valida os campos antes de enviar
                $('#informRenewal').addClass('d-none'); // Mostra campos
                $("#renewForm").removeClass("d-none");
                $("#btnNextStep").addClass("d-none");
                $("#btnSubmit").removeClass("d-none");
            } else if (selectedOption === "renew_later") {
                // Apenas fecha o modal
                $("#renewModal").modal("hide");
            } else {
                $(".alert").remove();
                $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Por favor, selecione uma opção antes de continuar.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        const table = $('#documentsTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            paging: true,
            ajax: {
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document-sending/list.php',
                type: 'GET',
                data: function (d) {
                    d.startDateReferenceFilter = $('#startDateReferenceFilter').val();
                    d.endDateReferenceFilter = $('#endDateReferenceFilter').val();
                    d.companyFilter = $('#companyFilter').val();
                    d.departmentFilter = $('#departmentFilter').val();
                    d.categoryFilter = $('#categoryFilter').val();
                    d.startDateFilter = $('#startDateFilter').val();
                    d.endDateFilter = $('#endDateFilter').val();
                    d.minPriceFilter = $('#minPriceFilter').val();
                    d.maxPriceFilter = $('#maxPriceFilter').val();
                },
                dataSrc: function (json) {
                    return json.data;
                },
            },
            columns: [
                { data: 'company', width: '20%' },
                { data: 'category', width: '15%' },
                { data: 'department', width: '15%' },
                { data: 'document', width: '10%' },
                { data: 'reference', width: '5%' },
                {
                    data: 'price',
                    className: 'text-nowrap text-end',
                    width: '10%'
                },
                { data: 'expiration_date', className: 'text-end', width: '10%' },
                {
                    data: 'status',
                    className: 'text-nowrap text-center',
                    width: '5%'
                },
                {
                    data: 'actions',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    width: '15%'
                },
            ],
            buttons: [
                {
                    extend: 'csvHtml5',
                    title: 'GDok - Documentos',
                    text: 'Exportar CSV',
                    className: 'btn btn-sm btn-outline-primary',
                    filename: function() {
                        return 'documentos_' + new Date().toISOString().replace(/T/, '_').replace(/:/g, '-').split('.')[0];
                    },
                    exportOptions: {
                        columns: ':not(:last-child)'
                    },
                    customize: function (csv) {
                        return 'GDok - Documentos\n' + csv; // Adiciona o título antes dos dados
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Exportar XLS',
                    className: 'btn btn-sm btn-outline-success',
                    filename: function() {
                        return 'documentos_' + new Date().toISOString().replace(/T/, '_').replace(/:/g, '-').split('.')[0];
                    },
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Exportar PDF',
                    className: 'btn btn-sm btn-outline-danger',
                    filename: function() {
                        return 'documentos_' + new Date().toISOString().replace(/T/, '_').replace(/:/g, '-').split('.')[0];
                    },
                    exportOptions: {
                        columns: ':not(:last-child)' // Exclui a última coluna do export
                    },
                    customize: function (doc) {
                        // Alinha os títulos à esquerda
                        doc.styles.tableHeader.alignment = 'left'; 

                        // Opcional: Alinhar todas as células do corpo à esquerda
                        doc.content[1].table.body.forEach(function(row) {
                            row.forEach(function(cell) {
                                cell.alignment = 'left';
                            });
                        });
                    }
                },
                {
                    extend: 'print',
                    text: 'Imprimir',
                    className: 'btn btn-sm btn-outline-secondary',
                    title: function() {
                        return 'Relatório de Exportação - ' + new Date().toLocaleString();
                    },
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            columns: [
                { data: 'company', width: '20%' },
                { data: 'category', width: '15%' },
                { data: 'department', width: '15%' },
                { data: 'document', width: '10%' },
                { data: 'reference', width: '5%' },
                {
                    data: 'price',
                    className: 'text-nowrap text-end',
                    width: '10%'
                },
                { data: 'expiration_date', className: 'text-end', width: '10%' },
                {
                    data: 'status',
                    className: 'text-nowrap text-center',
                    width: '5%'
                },
                {
                    data: 'actions',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    width: '15%'
                },
            ],
            language: {
                emptyTable: "Nenhum dado disponível na tabela",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 até 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros no total)",
                lengthMenu: "Mostrar _MENU_ registros por página",
                loadingRecords: "Carregando...",
                processing: "Processando...",
                search: "Buscar:",
                zeroRecords: "Nenhum registro encontrado",
            },
            pageLength: 10,
            lengthChange: false,
            info: false,
        });

        $('#exportCSV').click(function () {
            table.button('.buttons-csv').trigger();
        });

        $('#exportXLS').click(function () {
            table.button('.buttons-excel').trigger();
        });

        $('#exportPDF').click(function () {
            table.button('.buttons-pdf').trigger();
        });

        $('#exportDocx').click(function () {
            var startDateReferenceFilter = $('#startDateReferenceFilter').val();
            var endDateReferenceFilter = $('#endDateReferenceFilter').val();
            var companyFilter = $('#companyFilter').val();
            var categoryFilter = $('#categoryFilter').val();
            var startDateFilter = $('#startDateFilter').val();
            var endDateFilter = $('#endDateFilter').val();
            var statusFilter = $('#statusFilter').val();

            // Construir a URL com os parâmetros dos filtros
            var url = '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/export/docx.php?export=docx';

            if (startDateReferenceFilter) {
                url += '&startDateReferenceFilter=' + encodeURIComponent(startDateReferenceFilter);
            }
            if (endDateReferenceFilter) {
                url += '&endDateReferenceFilter=' + encodeURIComponent(endDateReferenceFilter);
            }
            if (companyFilter) {
                url += '&companyFilter=' + encodeURIComponent(companyFilter);
            }
            if (categoryFilter) {
                url += '&categoryFilter=' + encodeURIComponent(categoryFilter);
            }
            if (startDateFilter) {
                url += '&startDateFilter=' + encodeURIComponent(startDateFilter);
            }
            if (endDateFilter) {
                url += '&endDateFilter=' + encodeURIComponent(endDateFilter);
            }
            if (statusFilter) {
                url += '&statusFilter=' + encodeURIComponent(statusFilter);
            }

            // Redirecionar para a URL construída
            window.location.href = url;
        });

        $('#exportPrint').click(function () {
            table.button('.buttons-print').trigger();
        });

        // Limpar filtros ao clicar no botão "Limpar Filtros"
        $('#clearFiltersButton').on('click', function () {
            // Limpar os valores dos filtros
            $('#filterForm')[0].reset();

            // Remover filtros aplicados da listagem
            $('#filterList').empty();
            $('#companyFilter, #categoryFilter, #statusFilter').val(null).trigger('change');

            // Atualizar a tabela com os filtros limpos
            table.draw();

            // Ocultar o título "Filtros" se não houver filtros aplicados
            $('.active-filters').addClass('d-none');
            $('#clearFiltersButton').addClass('d-none');

            // Fechar o offcanvas
            $('#filterOffcanvas').offcanvas('hide');
        });

        // Capturar o evento de clique do botão "Aplicar Filtros"
        $('#applyFiltersButton').on('click', function () {
            // Função para converter um valor "MM/YYYY" em um objeto Date (primeiro dia do mês)
            function parseMonth(value) {
                if (!value) return null;
                value = value.trim();
                if (!value) return null;
                var parts = value.split('/');
                if (parts.length === 2) {
                    var month = parseInt(parts[0], 10);
                    var year = parseInt(parts[1], 10);
                    if (!isNaN(month) && !isNaN(year)) {
                        return new Date(year, month - 1, 1);
                    }
                }
                return null;
            }

            // Função para formatar um objeto Date para o padrão "MM/YYYY"
            function formatMonthToBR(dateObj) {
                if (!dateObj) return '';
                // Se não for um objeto Date, tente convertê-lo
                if (!(dateObj instanceof Date)) {
                    dateObj = parseMonth(dateObj);
                    if (!(dateObj instanceof Date)) return '';
                }
                var month = dateObj.getMonth() + 1;
                var year = dateObj.getFullYear();
                if (month < 10) month = "0" + month;
                return month + "/" + year;
            }

            // Função para formatar a data no padrão DD/MM/YYYY
            function formatDateToBR(date) {
                if (!date) return ''; // Verifica se a data é válida
                const [year, month, day] = date.split('-'); // Divide a data no formato ISO (YYYY-MM-DD)
                return `${day}/${month}/${year}`; // Retorna no formato DD/MM/YYYY
            }

            // Capturar os valores do formulário
            const startDateReferenceFilterVal = $('#startDateReferenceFilter').val();
            const endDateReferenceFilterVal = $('#endDateReferenceFilter').val();
            const companyFilterVal = $('#companyFilter').val();
            const companyFilterText = $('#companyFilter option:selected').text();
            const categoryFilterVal = $('#categoryFilter').val();
            const categoryFilterText = $('#categoryFilter option:selected').text();
            const startDateFilterVal = $('#startDateFilter').val();
            const endDateFilterVal = $('#endDateFilter').val();
            const statusFilterVal = $('#statusFilter').val();
            const statusFilterText = $('#statusFilter option:selected').text();

            // Validação para os inputs de mês (se ambos estiverem preenchidos)
            if (startDateReferenceFilterVal && endDateReferenceFilterVal) {
                var startDateRefObj = parseMonth(startDateReferenceFilterVal);
                var endDateRefObj = parseMonth(endDateReferenceFilterVal);
                if (startDateRefObj && endDateRefObj && endDateRefObj < startDateRefObj) {
                    $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início.</div>')
                        .insertAfter('.date-fields.reference').show();
                    return; // Impede que o filtro seja aplicado
                } else {
                    $('#dateError').remove();
                }
            }

            // Formatar as datas para o padrão brasileiro
            const startDateReferenceFormatted = formatMonthToBR(startDateReferenceFilterVal);
            const endDateReferenceFormatted = formatMonthToBR(endDateReferenceFilterVal);

            // Verificação de validação dos campos obrigatórios
            if (startDateFilterVal && endDateFilterVal && startDateFilterVal > endDateFilterVal) {
                // Exibir o erro
                $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início.</div>')
                    .insertAfter('.date-fields').show(); // Adiciona a mensagem de erro após o campo de data de término

                return; // Impede que o filtro seja aplicado
            } else {
                // Remove o erro se os campos forem corrigidos
                $('#dateError').remove();
            }

            // Formatar as datas para o padrão brasileiro
            const startDateFormatted = formatDateToBR(startDateFilterVal);
            const endDateFormatted = formatDateToBR(endDateFilterVal);

            // Adicionar filtros aplicados na listagem
            $('#filterList').empty();

            if (startDateReferenceFilterVal || endDateReferenceFilterVal) {
                const dateRange = startDateReferenceFilterVal && endDateReferenceFilterVal
                    ? `${startDateReferenceFilterVal} - ${endDateReferenceFilterVal}`
                    : (startDateReferenceFilterVal ? `De ${startDateReferenceFilterVal}` : (endDateReferenceFilterVal ? `Até ${endDateReferenceFilterVal}` : ''));
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${dateRange}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="dateRange">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (companyFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${companyFilterText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (categoryFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${categoryFilterText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (startDateFilterVal || endDateFilterVal) {
                const dateRange = startDateFormatted && endDateFormatted
                    ? `${startDateFormatted} - ${endDateFormatted}`
                    : (startDateFormatted ? `De ${startDateFormatted}` : (endDateFormatted ? `Até ${endDateFormatted}` : null));

                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${dateRange}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="dateRange">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (statusFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${statusFilterText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }

            // Verificar se existem filtros aplicados e mostrar ou ocultar o título "Filtros"
            if ($('#filterList').children().length > 0) {
                $('.active-filters').removeClass('d-none');
                $('#clearFiltersButton').removeClass('d-none');
            } else {
                $('.active-filters').addClass('d-none');
                $('#clearFiltersButton').addClass('d-none');
            }

            // Atualizar a tabela com os filtros
            table.draw();

            // Fechar o offcanvas
            $('#filterOffcanvas').offcanvas('hide');
        });

        // Capturar o evento de alteração do select "Filtrar por status"
        $('#statusFast').on('change', function () {
            const statusFastVal = $('#statusFast').val();
            const statusFastText = $('#statusFast option:selected').text();

            // Adicionar filtros aplicados na listagem
            $('#filterList').empty();

            // Atualizar o select statusFilter com o mesmo valor selecionado
            $('#statusFilter').val(statusFastVal).trigger('change');

            if (statusFastVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${statusFastText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }

            // Verificar se existem filtros aplicados e mostrar ou ocultar o título "Filtros"
            if ($('#filterList').children().length > 0) {
                $('.active-filters').removeClass('d-none');
                $('#clearFiltersButton').removeClass('d-none');
            } else {
                $('.active-filters').addClass('d-none');
                $('#clearFiltersButton').addClass('d-none');
            }

            // Atualizar a tabela com os filtros
            table.draw();

            // Fechar o offcanvas
            $('#filterOffcanvas').offcanvas('hide');
        });

        // Remover filtros aplicados
        $('#filterList').on('click', '.remove-filter', function () {
            const filterType = $(this).data('filter');
            if (filterType === 'dateRange') {
                $('#startDateFilter').val('');
                $('#endDateFilter').val('');
                $('#startDateReferenceFilter').val('');
                $('#endDateReferenceFilter').val('');
                $('#endDateReferenceFilter').removeAttr('min');
            } else {
                $(`#${filterType}Filter`).val(null).trigger('change');
            }
            $(this).closest('span').remove();
            table.draw();
            if ($('#filterList').children().length === 0) {
                $('.active-filters').addClass('d-none');
                $('#clearFiltersButton').addClass('d-none');
            }
        });

        // Custom pagination control
        let currentPage = 1;
        let totalPages = 1;

        // Função para atualizar os controles de paginação
        function updatePagination() {
            const info = table.page.info();
            totalPages = info.pages;

            // Atualiza a informação de páginas
            $('#info').text(`${currentPage} de ${totalPages}`);

            // Atualiza os estados dos botões de "Anterior" e "Próxima"
            $('#prevPage').parent().toggleClass('disabled', currentPage === 1);
            $('#nextPage').parent().toggleClass('disabled', currentPage === totalPages);

            // Limpa números de páginas existentes
            $('#pagination').find('.page-number').remove();

            // Adiciona os números de páginas dinamicamente
            for (let i = 1; i <= totalPages; i++) {
                const isActive = (i === currentPage) ? 'active' : '';
                $('#pagination').find('.liNextPage').before(
                    `<li class="page-item ${isActive}">
                        <a class="page-link rounded-2 me-2 page-number" href="#" data-page="${i}">${i}</a>
                    </li>`
                );
            }
        }

        // Clique no número da página
        $('#pagination').on('click', '.page-number', function (e) {
            e.preventDefault();
            currentPage = parseInt($(this).data('page'));
            table.page(currentPage - 1).draw(false);
            updatePagination();
        });

        // Clique no botão "Anterior"
        $('#prevPage').on('click', function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                table.page('previous').draw(false);
                updatePagination();
            }
        });

        // Clique no botão "Próxima"
        $('#nextPage').on('click', function (e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                table.page('next').draw(false);
                updatePagination();
            }
        });

        // Atualiza a paginação após o carregamento da tabela
        table.on('draw', function () {
            updatePagination();
        });

        // Atualização inicial da paginação
        updatePagination();
    });
</script>