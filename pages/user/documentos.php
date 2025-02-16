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
    $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar tipos de documentos cadastradas
    $stmt = $conn->prepare("SELECT id, name, advance_notification, personalized_advance_notification FROM tb_document_types WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $document_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= htmlspecialchars($company['id']); ?>">
                            <?= htmlspecialchars($company['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="documentTypeFilter" class="form-label">Tipo de Documento</label>
                <select id="documentTypeFilter" class="form-select">
                    <option value="">Selecione um tipo de documento</option>
                    <?php foreach ($document_types as $document_type): ?>
                        <option value="<?= htmlspecialchars($document_type['id']); ?>" 
                                data-advance-notification="<?= htmlspecialchars($document_type['advance_notification']); ?>"
                                data-personalized-advance-notification="<?= htmlspecialchars($document_type['personalized_advance_notification']); ?>">
                            <?= htmlspecialchars($document_type['name']); ?>
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

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Documento</h4>
    </div>

    <div class="text-end">
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-documento" class="btn btn-dark">
            <i class="mdi mdi-plus fs-16 align-middle"></i>
            Upload de Documento
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 text-black">Documentos cadastrados</h5>
                    <div class="ms-auto">
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
                            <th>Tipo de Documento</th>
                            <th>Arquivo</th>
                            <th>Vencimento do Documento</th>
                            <th>Notificação Antecipada</th>
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

<script>
    $(document).ready(function() {
        $('#companyFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione uma ou mais empresas',
            allowClear: true,
        });

        $('#documentTypeFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione um ou mais tipos de documentos',
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
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/delete.php?id=${elementIdToDelete}`,
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
        const table = $('#documentsTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            paging: true,
            ajax: {
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/list.php',
                type: 'GET',
                data: function (d) {
                    d.companyFilter = $('#companyFilter').val();
                    d.documentTypeFilter = $('#documentTypeFilter').val();
                    d.startDateFilter = $('#startDateFilter').val();
                    d.endDateFilter = $('#endDateFilter').val();
                },
                dataSrc: function (json) {
                    return json.data;
                },
            },
            columns: [
                { data: 'company', width: '20%' },
                { data: 'document_type', width: '15%' },
                { data: 'document', width: '10%' },
                { data: 'expiration_date', width: '15%' },
                {
                    data: 'advance_notification',
                    className: 'text-end',
                    width: '15%'
                },
                {
                    data: 'status',
                    className: 'text-nowrap text-reset text-center',
                    width: '15%'
                },
                {
                    data: 'actions',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    width: '10%'
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

        // Limpar filtros ao clicar no botão "Limpar Filtros"
        $('#clearFiltersButton').on('click', function () {
            // Limpar os valores dos filtros
            $('#filterForm')[0].reset();

            // Remover filtros aplicados da listagem
            $('#filterList').empty();
            $('#companyFilter, #documentTypeFilter').val(null).trigger('change');

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
            const documentTypeFilterVal = $('#documentTypeFilter').val();
            const documentTypeFilterText = $('#documentTypeFilter option:selected').text();
            const startDateFilterVal = $('#startDateFilter').val();
            const endDateFilterVal = $('#endDateFilter').val();

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
            if (documentTypeFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${documentTypeFilterText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="documentType">
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