<style>
    #couponsTable_paginate .pagination {
        display: none;
    }
    .dataTables_empty {
        text-align: center;
    }
</style>

<!-- Modal de Confirmação para Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir o cupom "<span id="couponName"></span>"?
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
        <h4 class="fs-18 fw-semibold m-0">Cupons</h4>
    </div>
    <div class="text-end">
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-cupom" class="btn btn-dark">
            <i class="mdi mdi-plus fs-16 align-middle"></i>
            Cadastrar Cupom
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black mb-0">Cupons Cadastrados</h5>
                </div>
            </div>

            <div class="card-body p-0">
                <table id="couponsTable" class="table table-traffic mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Vigência</th>
                            <th>Desconto</th>
                            <th>Código</th>
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

<!-- Dependências: jQuery, DataTables, etc. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    let elementIdToDelete = null;

    // Evento para abrir modal de exclusão
    $(document).on('click', '.btn-delete', function () {
        elementIdToDelete = $(this).data('id');
        const couponName = $(this).data('name');
        $('#couponName').text(couponName);
        $('#deleteModal').modal('show');
    });

    // Confirmação de exclusão
    $('#confirmDelete').on('click', function () {
        if (elementIdToDelete) {
            $.ajax({
                url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/coupon/delete.php?id=${elementIdToDelete}`,
                type: 'DELETE',
                success: function (response) {
                    location.reload();
                },
                error: function (xhr, status, error) {
                    console.error('Erro:', error);
                    $(".alert").remove();
                    $("#couponsTable").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                }
            });
        }
    });

    // Inicializa o DataTables
    const table = $('#couponsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: true,
        paging: true,
        ajax: {
            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/coupon/list.php',
            type: 'GET',
            dataSrc: function (json) {
                return json.data;
            }
        },
        columns: [
            { data: 'name', width: '25%' },
            { data: 'validity', width: '20%' },
            { data: 'discount', width: '20%', className: 'text-center' },
            { data: 'code', width: '20%', className: 'text-center' },
            { data: 'actions', width: '15%', className: 'text-center', orderable: false, searchable: false }
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
            zeroRecords: "Nenhum registro encontrado"
        },
        pageLength: 10,
        lengthChange: false,
        info: false
    });

    // Paginação customizada
    let currentPage = 1;
    let totalPages = 1;
    function updatePagination() {
        const info = table.page.info();
        totalPages = info.pages;
        $('#info').text(`${currentPage} de ${totalPages}`);
        $('#prevPage').parent().toggleClass('disabled', currentPage === 1);
        $('#nextPage').parent().toggleClass('disabled', currentPage === totalPages);
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

    $('#pagination').on('click', '.page-number', function (e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        table.page(currentPage - 1).draw(false);
        updatePagination();
    });

    $('#prevPage').on('click', function (e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            table.page('previous').draw(false);
            updatePagination();
        }
    });

    $('#nextPage').on('click', function (e) {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            table.page('next').draw(false);
            updatePagination();
        }
    });

    table.on('draw', function () {
        updatePagination();
    });
    updatePagination();
});
</script>