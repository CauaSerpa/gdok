<!-- Topbar Start -->
<div class="topbar-custom">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <button class="button-toggle-menu nav-link">
                        <i data-feather="menu" class="noti-icon"></i>
                    </button>
                </li>
                <li class="d-none d-lg-block">
                    <h5 class="mb-0"><?= getGreeting(); ?>, <?= $user['firstname']; ?>!</h5>
                </li>
            </ul>

            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

                <li class="d-none d-sm-flex">
                    <button type="button" class="btn nav-link" data-toggle="fullscreen">
                        <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
                    </button>
                </li>

                <?php
                    // Data atual
                    $currentDate = date('Y-m-d');

                    // Query para buscar documentos próximos do vencimento
                    $sql = "
                        SELECT n.*, d.expiration_date 
                        FROM tb_notifications n
                        LEFT JOIN tb_documents d ON n.related_id = d.id AND n.notification_type = 'document_expiration_system'
                        WHERE n.user_id = ? 
                        AND n.notification_type IN ('system', 'document_expiration_system', 'custom', 'document') 
                        ORDER BY n.is_read ASC, n.created_at ASC, d.expiration_date DESC
                        LIMIT 10
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$_SESSION['user_id']]);

                    // Obter os resultados
                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $sql = "SELECT COUNT(id) AS count FROM tb_notifications WHERE user_id = ? AND is_read = 0 AND notification_type IN ('system', 'document_expiration_system', 'custom', 'document') ORDER BY created_at DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$_SESSION['user_id']]);

                    // Obter os resultados
                    $unreadCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                ?>

                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="noti-icon"></i>
                        <span class="badge bg-danger rounded-circle noti-icon-badge<?= (!$unreadCount) ? " d-none" : ""; ?>" id="unread-count"><?= $unreadCount; ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-lg">
                        <div class="dropdown-item noti-title">
                            <h5 class="m-0">
                                Notificações
                                <?php if ($unreadCount > 0): ?>
                                <span class="float-end">
                                    <a href="#" class="text-muted" id="mark-all-read">
                                        <small>Marcar todas como lidas</small>
                                    </a>
                                </span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="noti-scroll" data-simplebar>

                        <?php foreach ($notifications as $notification) : ?>
                            <?php if  ($notification['notification_type'] == 'document_expiration_system'): ?>
                                <?php
                                    // Query para buscar documentos próximos do vencimento
                                    $sql = "
                                        SELECT 
                                            d.id,
                                            dt.name AS document_type,
                                            d.expiration_date,
                                            d.advance_notification,
                                            d.personalized_advance_notification,
                                            d.created_at,
                                            u.firstname AS user_name,
                                            CASE
                                                WHEN d.personalized_advance_notification IS NOT NULL THEN d.personalized_advance_notification
                                                ELSE d.advance_notification
                                            END AS notification_days
                                        FROM tb_documents d
                                        INNER JOIN tb_users u ON u.id = d.user_id
                                        INNER JOIN tb_document_types dt ON dt.id = d.document_type_id 
                                        WHERE d.user_id = ? AND d.id = ?
                                    ";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute([$_SESSION['user_id'], $notification['related_id']]);
                                    $document = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <a href="javascript:void(0);" 
                                onclick="openDocumentNotification(<?= $document['id']; ?>)" 
                                class="dropdown-item notify-item text-muted link-primary<?= (!$notification['is_read']) ? " bg-body-tertiary" : ""; ?>">
                                    <div class="notify-icon text-inherit rounded-circle border border-dashed border-warning d-flex align-items-center justify-content-center me-2" style="height: 35px; width: 35px;">
                                        <i class="mdi mdi-file-alert-outline fs-17 text-warning"></i>
                                    </div>
                                    <div class="notify-content">
                                        <p class="notify-details fw-bold text-dark mb-0">Aviso: Documento Próximo ao Vencimento</p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <small class="text-muted">
                                                <?= timeAgo($notification['created_at'] ?? 'now'); ?>
                                            </small>
                                            <?php if (!$notification['is_read']): ?>
                                                <input type="radio" class="form-check-input mark-as-read m-0" 
                                                    data-bs-toggle="tooltip" data-bs-placement="left" 
                                                    title="Marcar como lida" data-id="<?= $notification['id']; ?>">
                                            <?php endif; ?>
                                        </div>
                                        <p class="noti-mentioned p-2 rounded-2 mb-0 mt-2">
                                            Tipo: <span class="text-reset fw-bold"><?= htmlspecialchars($document['document_type']) ?></span><br>
                                            Data de Vencimento: <span class="<?= $document['expiration_date'] > $currentDate ? 'text-inherit' : 'text-danger'; ?>">
                                                <?= htmlspecialchars(date('d/m/Y', strtotime($document['expiration_date']))); ?>
                                            </span><br>
                                            Status: <?= $document['expiration_date'] > $currentDate ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'; ?>
                                        </p>
                                        <p class="mb-0 text-muted small">
                                            Configurado para <?= $document['advance_notification'] == "personalized" ? $document['personalized_advance_notification'] : $document['advance_notification']; ?> dia(s) antes do vencimento.
                                        </p>
                                    </div>
                                </a>
                            <?php elseif ($notification['notification_type'] == 'document'): ?>
                                <!-- Notificações de documento sem vencimento também podem usar a mesma função -->
                                <a href="javascript:void(0);" 
                                onclick="openDocumentNotification(<?= $notification['related_id']; ?>)" 
                                class="dropdown-item notify-item text-muted<?= (!$notification['is_read']) ? " bg-body-tertiary" : ""; ?>">
                                    <div class="notify-icon text-inherit rounded-circle border border-dashed d-flex align-items-center justify-content-center me-2" style="height: 35px; width: 35px;">
                                        <i class="mdi mdi-file-document-outline fs-17"></i>
                                    </div>
                                    <div class="notify-content">
                                        <p class="notify-details fw-bold text-dark mb-0"><?= htmlspecialchars($notification['title']); ?></p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <small class="text-muted">
                                                <?= timeAgo($notification['created_at'] ?? 'now'); ?>
                                            </small>
                                            <?php if (!$notification['is_read']): ?>
                                                <input type="radio" class="form-check-input mark-as-read m-0" 
                                                    data-bs-toggle="tooltip" data-bs-placement="left" 
                                                    title="Marcar como lida" data-id="<?= $notification['id']; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <!-- Outros tipos de notificação -->
                                <div class="dropdown-item notify-item text-muted">
                                    <div class="notify-icon text-inherit rounded-circle border border-dashed d-flex align-items-center justify-content-center me-2" style="height: 35px; width: 35px;">
                                        <i class="mdi mdi-account-outline fs-17"></i>
                                    </div>
                                    <div class="notify-content">
                                        <p class="notify-details fw-bold text-dark mb-0">Usuário <?= htmlspecialchars($user['shortname']); ?> Criado</p>
                                        <p class="noti-mentioned p-2 rounded-2 mb-0 mt-2 text-wrap">
                                            Olá, <span class="text-reset fw-bold"><?= htmlspecialchars($user['firstname']); ?></span>! Sua conta foi criada com sucesso no dia <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))); ?>.
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                            <div class="dropdown-item notify-item text-muted">
                                <div class="notify-icon text-inherit rounded-circle border border-dashed d-flex align-items-center justify-content-center me-2" style="height: 35px; width: 35px;">
                                    <i class="mdi mdi-account-outline fs-17"></i>
                                </div>
                                <div class="notify-content">
                                    <p class="notify-details fw-bold text-dark mb-0">Usuário <?= htmlspecialchars($user['shortname']); ?> Criado</p>
                                    <p class="noti-mentioned p-2 rounded-2 mb-0 mt-2 text-wrap">
                                        Olá, <span class="text-reset fw-bold"><?= htmlspecialchars($user['firstname']); ?></span>! Sua conta foi criada com sucesso no dia <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))); ?>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>notificacoes" class="dropdown-item text-center text-primary notify-item notify-all">
                            Ver todas <i class="fe-arrow-right"></i>
                        </a>
                    </div>
                </li>

                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="<?= $user['profile_image']; ?>" alt="user-image" class="rounded-circle">
                        <span class="pro-user-name ms-1">
                            <?= $user['shortname'] ?> <i class="mdi mdi-chevron-down"></i> 
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                        <!-- item-->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Bem-vindo !</h6>
                        </div>

                        <!-- item-->
                        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>configuracoes" class="dropdown-item notify-item">
                            <i class="mdi mdi-account-circle-outline fs-16 align-middle"></i>
                            <span>Minha Conta</span>
                        </a>

                        <!-- item-->
                        <a href="#" class="dropdown-item notify-item d-none">
                            <i class="mdi mdi-lock-outline fs-16 align-middle"></i>
                            <span>Tela de bloqueio</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <!-- item-->
                        <a href="<?= INCLUDE_PATH_AUTH; ?>sair" class="dropdown-item notify-item">
                            <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                            <span>Sair</span>
                        </a>

                    </div>
                </li>

            </ul>
        </div>

    </div>
    
</div>
<!-- end Topbar -->

<!-- Evento para abrir pagina de documento com o modal de visualizacao ja aberto -->
<script>
    // Função para redirecionar para a página de documentos com parâmetros para abrir o modal automaticamente
    function openDocumentNotification(documentId) {
        // Redireciona para a página de documentos (por exemplo, "documentos") passando os parâmetros
        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>documentos?openModal=1&docId=" + documentId;
    }
</script>

<!-- Evento para marcar como lida -->
<script>
$(document).ready(function() {
    $('#mark-all-read').on('click', function() {
        $.ajax({
            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/notifications/mark-as-read.php', // Crie esse arquivo no backend
            type: 'POST',
            data: { action: 'mark-all-read' },
            success: function(response) {
                if (response.status === 'success') {
                    $(this).remove();
                    $('.noti-scroll a').removeClass('bg-body-tertiary');
                    $('.noti-scroll a input[type="radio"]').remove();
                    $('#unread-count').text(0).addClass('d-none');

                    // Remove o número de notificações, se existir, usando expressão regular
                    var currentTitle = document.title;
                    var newTitle = currentTitle.replace(/\(\d+\)\s?/, '');
                    document.title = newTitle;
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("Erro no AJAX:", status, error);
                alert('Ocorreu um erro, tente novamente mais tarde.');
            }
        });
    });

    $('.mark-as-read').on('change', function() {
        let notificationId = $(this).data('id');
        let parentElement = $(this).closest('.notify-item');
        let radioButton = $(this);

        $.ajax({
            url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/notifications/mark-as-read.php', // Crie esse arquivo no backend
            type: 'POST',
            data: { id: notificationId, action: 'mark-as-read' },
            success: function(response) {
                if (response.status === 'success') {
                    $('[data-bs-toggle="tooltip"]').tooltip('hide');
                    radioButton.remove();
                    parentElement.removeClass('bg-body-tertiary');

                    // Atualiza a contagem de notificações não lidas
                    let unreadCount = parseInt($('#unread-count').text(), 10) || 0;
                    unreadCount--; // Diminui 1 da contagem

                    // Atualiza a contagem na tela
                    if (unreadCount <= 0) {
                        $('#unread-count').text(0).addClass('d-none'); // Esconde se não houver mais notificações
                    } else {
                        $('#unread-count').text(unreadCount);
                    }

                    // Atualiza o título da página
                    var currentTitle = document.title;
                    var newTitle = currentTitle.replace(/\(\d+\)\s?/, ''); // Remove o número de notificações não lidas do título
                    if (unreadCount > 0) {
                        newTitle = '(' + unreadCount + ') ' + newTitle;
                    }
                    document.title = newTitle;
                } else {
                    alert(response.message);
                    radioButton.prop('checked', false);
                }
            },
            error: function (xhr, status, error) {
                console.error("Erro no AJAX:", status, error);
                alert('Ocorreu um erro, tente novamente mais tarde.');

                radioButton.prop('checked', false);
            }
        });
    });
});
</script>