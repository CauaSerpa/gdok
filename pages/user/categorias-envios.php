<style>
    #categoriesTable_paginate .pagination {
        display: none;
    }
    .dataTables_empty {
        text-align: center;
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
                Tem certeza de que deseja excluir a Categoria de Envios "<span id="categorySendingName"></span>"?
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
        <h4 class="fs-18 fw-semibold m-0">Categoria de Envio</h4>
    </div>

    <div class="text-end">
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-categoria-envio" class="btn btn-dark">
            <i class="mdi mdi-plus fs-16 align-middle"></i>
            Cadastrar Categoria de Envio
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black mb-0">Categoria cadastradas de envio</h5>
                </div>
            </div>

            <div class="card-body p-0">
                <table id="categoriesTable" class="table table-traffic mb-0">
                    <thead>
                        <tr>
                            <th>Nome da Categoria de Envio</th>
                            <th>Depto</th>
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

<script>
    $(document).ready(function () {
        let elementIdToDelete = null;

        // Quando clicar no botão de exclusão
        $(document).on('click', '.btn-delete', function () {
            elementIdToDelete = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            const elementNameToDelete = $(this).data('name'); // Obtém o nome do elemento a ser excluído
            $('#categorySendingName').text(elementNameToDelete); // Define o nome no modal
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            console.log(elementIdToDelete);
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category-sending/delete.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#categorySendingForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Inicializa o DataTables com configuração básica
        const table = $('#categoriesTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            paging: true, // Desabilita a paginação interna do DataTable
            ajax: {
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/category-sending/list.php', // URL para carregar os dados
                type: 'GET',
                dataSrc: function (json) {
                    return json.data;
                },
            },
            columns: [
                { data: 'name', width: '50%' },
                { data: 'depto', width: '40%' },
                {
                    data: 'actions',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    width: '10%'
                },
            ],
            language: {
                emptyTable: "Nenhum dado disponível na tabela", // Tradução personalizada para "No data available in table"
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