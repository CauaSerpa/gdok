<style>
    #usersTable_paginate .pagination {
        display: none;
    }
    .dataTables_empty {
        text-align: center;
    }
</style>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir o usuário "<span id="userNameToDelete"></span>"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
                <button type="button" id="confirmUserDelete" class="btn btn-danger">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Visualização do Documento -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- modal extra-grande para melhor visualização -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Visualizar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Nav tabs -->
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item me-2" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#user-info" role="tab">
                                    <span class="d-block d-sm-none"><i class="mdi mdi-home-account"></i></span>
                                    <span class="d-none d-sm-block">Informações do Usuário</span> 
                                </a>
                            </li>
                            <li class="nav-item me-2" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#office-info" role="tab">
                                    <span class="d-block d-sm-none"><i class="mdi mdi-account-outline"></i></span>
                                    <span class="d-none d-sm-block">Escritório</span> 
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#managed-companies" role="tab">
                                    <span class="d-block d-sm-none"><i class="mdi mdi-email-outline"></i></span>
                                    <span class="d-none d-sm-block">Empresas Gerenciadas</span>   
                                </a>
                            </li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content text-muted">
                            <div class="tab-pane active show" id="user-info" role="tabpanel">
                                <div class="row">
                                    <div class="col-4">
                                        <!-- Nome -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Nome:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="username">Nome do Usuário</p>
                                        </div>
                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Email:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="email">Email do Usuário</p>
                                        </div>
                                        <!-- Telefone -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Telefone:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="phone">Telefone do Usuário</p>
                                        </div>
                                        <!-- Data de Cadastro -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Data de Cadastro:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="dateCreate">DD/MM/AAAA</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <!-- CEP -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">CEP:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="cep">CEP do Usuário</p>
                                        </div>
                                        <!-- Endereço -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Endereço:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="address">Endereço do Usuário</p>
                                        </div>
                                        <!-- Número -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Número:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="addressNumber">Número</p>
                                        </div>
                                        <!-- Complemento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Complemento:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="complement">Complemento</p>
                                        </div>
                                        <!-- Cidade -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Cidade:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="city">Cidade</p>
                                        </div>
                                        <!-- Estado -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Estado:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="state">Estado</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end tab pane -->
                            <div class="tab-pane" id="office-info" role="tabpanel">
                                <div class="row">
                                    <div class="col-4">
                                        <!-- Nome do Escritório -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Nome do Escritório:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="username">Nome do Escritório</p>
                                        </div>
                                        <!-- Email do Escritório -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Email do Escritório:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="email">Email do Escritório</p>
                                        </div>
                                        <!-- Telefone do Escritório -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Telefone do Escritório:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="phone">Telefone do Escritório</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <!-- CEP -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">CEP:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="cep">CEP do Escritório</p>
                                        </div>
                                        <!-- Endereço -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Endereço:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="address">Endereço do Escritório</p>
                                        </div>
                                        <!-- Número -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Número:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="addressNumber">Número</p>
                                        </div>
                                        <!-- Complemento -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Complemento:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="complement">Complemento</p>
                                        </div>
                                        <!-- Cidade -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Cidade:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="city">Cidade</p>
                                        </div>
                                        <!-- Estado -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold mb-0">Estado:</label>
                                            <p class="form-control-plaintext fs-16 p-0" id="state">Estado</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end tab pane -->
                            <div class="tab-pane" id="managed-companies" role="tabpanel">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Nome da Empresa</th>
                                                        <th scope="col">Nome do Responsável</th>
                                                        <th scope="col">Documentos Cadastrados</th>
                                                        <th scope="col">Data de Cadastro</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Nome da Empresa</td>
                                                        <td>Nome do Responsável</td>
                                                        <td>Documentos Cadastrados</td>
                                                        <td>Data de Cadastro</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end tab pane -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Lista de Usuários</h4>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black mb-0">Usuários</h5>
                </div>
            </div>

            <div class="card-body p-0">
                <table id="usersTable" class="table table-traffic mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Escritório</th>
                            <th>Email</th>
                            <th>Documento</th>
                            <th>Telefone</th>
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
$(document).ready(function(){
    // Usando delegação de eventos para capturar cliques em elementos que podem ser inseridos dinamicamente
    $(document).on('click', '.btn-view', function(e) {
        e.preventDefault();

        // Pega o data-id do botão clicado
        var userId = $(this).data('id');

        // Faz a requisição AJAX para o backend PHP
        $.ajax({
            url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/user/get.php`, // Altere para o caminho correto do seu arquivo PHP
            type: 'POST',
            dataType: 'json',
            data: { id: userId },
            success: function(response) {
                if(response.status === "success") {
                    // Preenche os campos do modal com os dados retornados
                    $('#empresa').text(response.data.company);
                    $('#dataVencimento').text(response.data.expiration_date);
                    $('#nomeDocumento').text(response.data.name);
                    
                    $('#observacao').text(response.data.observation);
                    $('#tipoDocumento').text(response.data.document_type);
                    $('#notificacaoAntecipada').text(response.data.advance_notification);
                    $('#status').html(response.data.status);
                    
                    // Abre o modal após preencher os dados
                    $('#userModal').modal('show');
                } else {
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#usersForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Usuário não encontrado ou ocorreu um erro na consulta.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição AJAX: ' + error);
                $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                $("#usersForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            }
        });
    });
});
</script>

<script>
    $(document).ready(function () {
        // Inicializa o DataTables
        const table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            paging: true,
            ajax: {
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/user/list.php', // Ajuste a URL
                type: 'GET',
                dataSrc: function (json) {
                    return json.data;
                },
            },
            columns: [
                { data: 'name' },
                { data: 'office' },
                { data: 'email' },
                { data: 'document' },
                { data: 'phone' },
                { data: 'status' },
                {
                    data: 'actions',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
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

        // Paginação customizada (como no exemplo original)
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
                $('#pagination').find('.liNextPage').before(`<li class="page-item ${isActive}"><a class="page-link rounded-2 me-2 page-number" href="#" data-page="${i}">${i}</a></li>`);
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