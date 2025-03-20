<?php
    $department = $_GET['depto'] ?? null;

    if ($department) {
        // Preparar a consulta para verificar se o usuário faz parte de algum escritório
        $stmt = $conn->prepare("
            SELECT sc.*
            FROM tb_sending_categories sc
            WHERE sc.id = ?
        ");
        $stmt->execute([$token]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Preparar a consulta para verificar se o usuário faz parte de algum escritório
    $stmt = $conn->prepare("
        SELECT sc.*
        FROM tb_sending_categories sc
        WHERE sc.id = ?
    ");
    $stmt->execute([$token]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    // echo "<pre>";
    // print_r($category);
    // echo "</pre>";
?>

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
                <label for="companyFilter" class="form-label">Empresa</label>
                <select id="companyFilter" class="form-select">
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($companies as $companyFilter): ?>
                        <option value="<?= htmlspecialchars($companyFilter['id']); ?>">
                            <?= htmlspecialchars($companyFilter['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="categoryFilter" class="form-label">Categoria</label>
                <select id="categoryFilter" class="form-select">
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categories as $categoryFilter): ?>
                        <option value="<?= htmlspecialchars($categoryFilter['id']); ?>">
                            <?= htmlspecialchars($categoryFilter['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="expirationDateFilter" class="form-label">Vencimento</label>
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
                <label for="statusFilter" class="form-label">Filtrar por status</label>
                <select id="statusFilter" class="form-select">
                    <option value="all_parametrized" selected>Todos (até 7 dias de vencido)</option>
                    <option value="all">Todos</option>
                    <option value="in_day">Em dia</option>
                    <option value="next">A vencer</option>
                    <option value="today">Vence hoje</option>
                    <option value="overdue">Vencido</optiozn>
                </select>
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

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <div class="clearfix">
            <div class="float-start">
                <?php if (!empty($office['logo'])): ?>
                <img src="<?= $office['logo']; ?>" class="mb-2" alt="logo" style="height: 30px; max-width: 150px; object-fit: contain;">
                <?php endif; ?>
                <h5 class="mb-0 caption fw-semibold"><?= $office['name']; ?></h5>
            </div>
        </div>
    </div>
</div>

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0"><?= isset($department) ? $department['name'] . " / " : ''; ?><?= $category['name']; ?></h4>
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
                        <div class="me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Filtrar por status">
                            <label class="visually-hidden" for="statusFast" name="statusFast">Filtrar por status</label>
                            <select class="form-select form-select-sm fs-14" id="statusFast" name="statusFast">
                                <option value="all_parametrized" selected>Todos (até 7 dias de vencido)</option>
                                <option value="all">Todos</option>
                                <option value="in_day">Em dia</option>
                                <option value="next">A vencer</option>
                                <option value="today">Vence hoje</option>
                                <option value="overdue">Vencido</optiozn>
                            </select>
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
                            <th>Nome</th>
                            <th>Referência</th>
                            <th>Valor</th>
                            <th>Vencimento do Documento</th>
                            <th>Data de Upload</th>
                            <th>Arquivo</th>
                            <th>Status</th>
                            <th>Visualizado Em</th>
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

<!-- Marcar documento como lido -->
<script>
    $(document).on('click', '#markAsViewed', function(){
        var documentId = $(this).data('document-id');
        $.ajax({
            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/portal/document-sending/document_viewed.php', // arquivo PHP que fará a atualização
            type: 'POST',
            data: { document_id: documentId },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success'){
                    // Exibe uma mensagem ou atualiza a interface, se necessário
                    console.log('Documento marcado como visualizado!');
                    location.reload();
                } else {
                    console.error('Erro: ' + response.message);
                }
            },
            error: function() {
                console.error('Erro na requisição.');
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#companyFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione uma ou mais empresas',
            allowClear: true,
        });

        $('#categoryFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione uma ou mais categorias',
            allowClear: true,
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
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/portal/document-sending/list.php',
                type: 'GET',
                data: function (d) {
                    d.companyFilter = $('#companyFilter').val();
                    d.categoryFilter = $('#categoryFilter').val();
                    d.startDateFilter = $('#startDateFilter').val();
                    d.endDateFilter = $('#endDateFilter').val();
                    d.statusFilter = $('#statusFilter').val();
                    d.category = <?= $category['id']; ?>;
                    d.company = <?= $company['id']; ?>;
                },
                dataSrc: function (json) {
                    return json.data;
                },
            },
            columns: [
                { data: 'name', width: '20%' },
                { data: 'reference', width: '10%' },
                { data: 'price', width: '10%' },
                { data: 'expiration_date', width: '20%' },
                { data: 'upload_date', width: '10%' },
                { data: 'document', width: '10%' },
                {
                    data: 'status',
                    className: 'text-nowrap text-center',
                    width: '5%'
                },
                { data: 'read_in', width: '20%' },
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
                { data: 'name', width: '20%' },
                { data: 'reference', width: '10%' },
                { data: 'price', width: '10%' },
                { data: 'expiration_date', width: '20%' },
                { data: 'upload_date', width: '10%' },
                { data: 'document', width: '10%' },
                {
                    data: 'status',
                    className: 'text-nowrap text-center',
                    width: '5%'
                },
                { data: 'read_in', width: '20%' },
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
            var companyFilter = $('#companyFilter').val();
            var categoryFilter = $('#categoryFilter').val();
            var startDateFilter = $('#startDateFilter').val();
            var endDateFilter = $('#endDateFilter').val();
            var statusFilter = $('#statusFilter').val();

            // Construir a URL com os parâmetros dos filtros
            var url = '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/portal/document/export/docx.php?export=docx';

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
            // Função para formatar a data no padrão DD/MM/YYYY
            function formatDateToBR(date) {
                if (!date) return ''; // Verifica se a data é válida
                const [year, month, day] = date.split('-'); // Divide a data no formato ISO (YYYY-MM-DD)
                return `${day}/${month}/${year}`; // Retorna no formato DD/MM/YYYY
            }

            // Capturar os valores do formulário
            const companyFilterVal = $('#companyFilter').val();
            const companyFilterText = $('#companyFilter option:selected').text();
            const categoryFilterVal = $('#categoryFilter').val();
            const categoryFilterText = $('#categoryFilter option:selected').text();
            const startDateFilterVal = $('#startDateFilter').val();
            const endDateFilterVal = $('#endDateFilter').val();
            const statusFilterVal = $('#statusFilter').val();
            const statusFilterText = $('#statusFilter option:selected').text();

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
            
            // Remover o filtro
            if (filterType === 'dateRange') {
                $('#startDateFilter').val('');
                $('#endDateFilter').val('');
                $('#endDateFilter').attr('min', '');
            } else {
                $(`#${filterType}Filter`).val(null).trigger('change');
            }

            // Remover o item do filtro da listagem
            $(this).closest('span').remove();

            // Atualizar a tabela com os filtros
            table.draw();

            // Verificar se existem filtros aplicados e mostrar ou ocultar o título "Filtros"
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