<?php
    // Define o layout padrão
    $defaultView = 'table';

    // Verifica se foi passado o parâmetro "view" na URL
    if (isset($_GET['view'])) {
        $view = $_GET['view'];
        // Grava o valor em um cookie por 30 dias
        setcookie("view", $view, time() + (86400 * 30), "/");
    } elseif (isset($_COOKIE['view'])) {
        // Se não passou na URL, mas existe o cookie, utiliza-o
        $view = $_COOKIE['view'];
    } else {
        // Caso contrário, usa o layout padrão
        $view = $defaultView;
    }
?>

<!-- Modal de Confirmação -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir o Plano "<span id="planName"></span>"?</p>
                <small class="text-reset"><strong>Atenção:</strong> Ao excluir este plano, todos os usuários associados a ele serão automaticamente transferidos para o plano gratuito, perdendo os benefícios específicos deste plano.</small>
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
        <h4 class="fs-18 fw-semibold m-0">Planos</h4>
    </div>

    <div class="text-end">
        <!-- Botões que alteram o layout via URL -->
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>planos?view=<?= ($view == 'card') ? 'table' : 'card'; ?>" class="btn btn-light me-2" data-bs-toggle="tooltip" title="<?= ($view == 'card') ? 'Mudar para Lista' : 'Mudar para cards'; ?>">
            <?php if ($view == 'card'): ?>
                <i class="mdi mdi-format-list-bulleted fs-16 align-middle"></i>
            <?php else: ?>
                <i class="mdi mdi-dots-grid fs-16 align-middle"></i>
            <?php endif; ?>
        </a>
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-plano" class="btn btn-dark">
            <i class="mdi mdi-plus fs-16 align-middle"></i>
            Cadastrar Plano
        </a>
    </div>
</div>

<?php if ($view == 'card'): ?>

    <!-- Listagem em Cards -->
    <?php include_once('./pages/admin/plans/list-cards.php'); ?>

<?php else: ?>

    <!-- Listagem por tabela -->
    <?php include_once('./pages/admin/plans/list-table.php'); ?>

<?php endif; ?>

<!-- Deletar Plano -->
<script>
    $(document).ready(function () {
        let elementIdToDelete = null;

        // Quando clicar no botão de exclusão
        $(document).on('click', '.btn-delete', function () {
            elementIdToDelete = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            const elementNameToDelete = $(this).data('name'); // Obtém o nome do elemento a ser excluído
            $('#planName').text(elementNameToDelete); // Define o nome no modal
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            console.log(elementIdToDelete);
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/admin/plan/delete.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#deleteModal .modal-body").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert"> um erro, teOcorreunte novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
            }
        });
    });
</script>