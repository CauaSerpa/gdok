<style>
    #companiesTable_paginate .pagination {
        display: none;
    }
    .dataTables_empty {
        text-align: center;
    }
</style>

<!-- Offcanvas de Filtro -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="filterForm">
            <!-- Filtro de Empresa -->
            <div class="mb-3">
                <label for="companyFilter" class="form-label">Empresa</label>
                <input type="text" id="companyFilter" class="form-control" placeholder="Digite a empresa">
            </div>
            <!-- Filtro de Responsável -->
            <div class="mb-3">
                <label for="responsibleFilter" class="form-label">Responsável</label>
                <input type="text" id="responsibleFilter" class="form-control" placeholder="Digite o responsável">
            </div>
            <!-- Filtro de CPF/CNPJ -->
            <div class="mb-3">
                <label for="documentFilter" class="form-label">CPF/CNPJ</label>
                <input type="text" id="documentFilter" class="form-control" placeholder="Digite o CPF ou CNPJ" onkeyup="handleCpfCnpj(event)">
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
                Tem certeza de que deseja excluir a empresa "<span id="companyName"></span>"?
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
        <h4 class="fs-18 fw-semibold m-0">Empresas</h4>
    </div>

    <div class="text-end">
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-empresa" class="btn btn-dark">
            <i class="mdi mdi-plus fs-16 align-middle"></i>
            Cadastrar Empresa
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black mb-0">Empresas cadastradas</h5>
                    <div class="ms-auto">
                        <button class="btn btn-sm bg-light border dropdown-toggle fw-medium text-black" type="button" data-bs-toggle="offcanvas" href="#filterOffcanvas" role="button" aria-controls="filterOffcanvas">
                            <i class="mdi mdi-filter-outline me-1 fs-14"></i>Filtrar Empresas</i>
                        </button>
                    </div>
                </div>
                <div class="active-filters mt-3 d-none">
                    <h6 class="text-uppercase fs-13">Filtros</h6>

                    <div id="filterList" class="d-flex flex-wrap gap-2"></div>

                </div>
            </div>

            <div class="card-body p-0">
                <table id="companiesTable" class="table table-traffic mb-0">
                    <thead>
                        <tr>
                            <th>Nome da Empresa</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Responsável</th>
                            <th>CPF/CNPJ</th>
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
    function handleCpfCnpj(event) {
        var input = event.target;
        var value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

        if (value.length <= 11) {
            $(input).mask('000.000.000-00#####');
        } else {
            $(input).mask('00.000.000/0000-00');
        }
    }
</script>
<script>
    $(document).ready(function () {
        let elementIdToDelete = null;

        // Quando clicar no botão de exclusão
        $(document).on('click', '.btn-delete', function () {
            elementIdToDelete = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            const elementNameToDelete = $(this).data('name'); // Obtém o nome do elemento a ser excluído
            $('#companyName').text(elementNameToDelete); // Define o nome no modal
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            console.log(elementIdToDelete);
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company/delete.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#companyForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).ready(function () {
        // Inicializa o DataTable
        const table = $('#companiesTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            paging: true,
            ajax: {
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company/list.php',
                type: 'GET',
                data: function (d) {
                    // Adiciona os filtros à requisição
                    d.companyFilter = $('#companyFilter').val();
                    d.responsibleFilter = $('#responsibleFilter').val();
                    d.documentFilter = $('#documentFilter').val();
                },
                dataSrc: function (json) {
                    return json.data;
                },
            },
            columns: [
                { data: 'name' },
                { data: 'phone' },
                { data: 'email' },
                { data: 'responsible' },
                { data: 'document' },
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
            const responsibleFilterVal = $('#responsibleFilter').val();
            const documentFilterVal = $('#documentFilter').val();

            // Adicionar filtros aplicados na listagem
            $('#filterList').empty();

            if (companyFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${companyFilterVal}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (responsibleFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${responsibleFilterVal}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="responsible">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (documentFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${documentFilterVal}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="document">
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
            $(`#${filterType}Filter`).val(null).trigger('change');

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