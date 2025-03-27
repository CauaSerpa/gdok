<style>
    #plansTable_paginate .pagination {
        display: none;
    }
    .dataTables_empty {
        text-align: center;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black mb-0">Planos cadastrados</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <table id="plansTable" class="table table-traffic mb-0">
                    <thead>
                        <tr>
                            <th>Nome do Plano</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Período</th>
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
                            <span id="info" class="fw-medium">1 de 1</span>
                        </div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <div class="pagination gap-2 justify-content-center py-3 ps-0 pe-3">
                            <ul id="pagination" class="pagination mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link me-2 rounded-2" href="javascript:void(0);" id="prevPage">Anterior</a>
                                </li>
                                <!-- Números de páginas serão inseridos dinamicamente -->
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

<script>
$(document).ready(function () {
    // Inicializa o DataTables com configuração de processamento no servidor
    const table = $('#plansTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: true,
        paging: true, // Desabilita a paginação interna do DataTable
        ajax: {
            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/plan/list.php', // URL do backend para carregar os dados
            type: 'GET',
            dataSrc: function (json) {
                return json.data;
            },
        },
        columns: [
            { data: 'plan_name', width: '25%' },
            { data: 'plan_description', width: '25%' },
            { data: 'price', width: '15%', className: 'text-end' },
            { data: 'billing_period', width: '20%', className: 'text-end' },
            { data: 'actions', width: '15%', className: 'text-center', orderable: false, searchable: false },
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
        pageLength: 10,  // Define a quantidade de registros por página
        lengthChange: false,  // Desabilita a opção de mudar a quantidade de registros por página
        info: false,  // Exibe a quantidade de registros exibidos e o total
    });

    // Controles customizados de paginação
    let currentPage = 1;
    let totalPages = 1;

    function updatePagination() {
        const info = table.page.info();
        totalPages = info.pages;
        $('#info').text(`${currentPage} de ${totalPages}`);

        // Atualiza o estado dos botões "Anterior" e "Próxima"
        $('#prevPage').parent().toggleClass('disabled', currentPage === 1);
        $('#nextPage').parent().toggleClass('disabled', currentPage === totalPages);

        // Remove os números de páginas existentes e insere os novos
        $('#pagination').find('.page-number').remove();
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

    // Atualiza a paginação a cada redraw da tabela
    table.on('draw', function () {
        updatePagination();
    });

    // Atualização inicial
    updatePagination();
});
</script>