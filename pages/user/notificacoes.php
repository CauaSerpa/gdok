<?php
    // Data atual
    $currentDate = date('Y-m-d');

    // Query para buscar documentos próximos do vencimento
    $sql = "SELECT * FROM tb_notifications WHERE user_id = ? AND notification_type IN ('system', 'document_expiration_system', 'custom', 'document') ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);

    // Obter os resultados
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT COUNT(id) AS count FROM tb_notifications WHERE user_id = ? AND is_read = 0 AND notification_type IN ('system', 'document_expiration_system', 'custom', 'document') ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);

    // Obter os resultados
    $unreadCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Notificações</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= INCLUDE_PATH_DASHBOARD; ?>">Home</a></li>
                <li class="breadcrumb-item active">Notificações</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pb-0">
                    <?php if (count($notifications) > 0) : ?>
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
                                        INNER JOIN tb_users u ON u.id = d.user_id -- Supondo que há uma tabela de usuários
                                        INNER JOIN tb_document_types dt ON dt.id = d.document_type_id 
                                        WHERE d.user_id = ? AND d.id = ?
                                    ";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute([$_SESSION['user_id'], $notification['related_id']]);

                                    // Obter os resultados
                                    $document = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>

                                <div class="alert alert-light border rounded-3 d-flex align-items-center mb-3 p-3">
                                    <div class="notify-icon text-inherit rounded-circle border border-dashed border-warning d-flex align-items-center justify-content-center me-3" style="height: 50px; width: 50px;">
                                        <i class="mdi mdi-file-alert-outline fs-24 text-warning"></i>
                                    </div>
                                    <div class="notify-content flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($document['document_type']) ?></h6>
                                        <p class="mb-1 small text-muted">
                                            Data de Vencimento: <span class="<?= $document['expiration_date'] > $currentDate ? 'text-inherit' : 'text-danger'; ?>"><?= htmlspecialchars(date('d/m/Y', strtotime($document['expiration_date']))) ?></span>
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            Configurado para <?= $document['advance_notification'] == "personalized" ? $document['personalized_advance_notification'] : $document['advance_notification']; ?> dia(s) antes do vencimento.
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <a href="<?= INCLUDE_PATH_DASHBOARD ?>editar-documento/<?= $notification['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            Ver Documento
                                        </a>
                                    </div>
                                </div>
                            <?php elseif ($notification['notification_type'] == 'document'): ?>
                                <div class="alert alert-light border rounded-3 d-flex align-items-center p-3">
                                    <div class="notify-icon text-inherit rounded-circle border border-dashed d-flex align-items-center justify-content-center me-3" style="height: 50px; width: 50px;">
                                        <i class="mdi mdi-file-document-outline fs-24"></i>
                                    </div>
                                    <div class="notify-content flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($notification['title']); ?></h6>
                                        <p class="mb-0 text-muted"><?= htmlspecialchars($notification['message']); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <a href="<?= INCLUDE_PATH_DASHBOARD ?>editar-documento/<?= $notification['related_id'] ?>" class="btn btn-outline-primary btn-sm">
                                            Ver Documento
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="alert alert-light border rounded-3 d-flex align-items-center p-3">
                        <div class="notify-icon text-inherit rounded-circle border border-dashed d-flex align-items-center justify-content-center me-3" style="height: 50px; width: 50px;">
                            <i class="mdi mdi-account-outline fs-24"></i>
                        </div>
                        <div class="notify-content flex-grow-1">
                            <h6 class="fw-bold mb-1 text-primary">Usuário <?= htmlspecialchars($user['shortname']); ?> Criado</h6>
                            <p class="mb-0 text-muted">
                                Olá, <span class="text-reset fw-bold"><?= htmlspecialchars($user['firstname']) ?></span>! Sua conta foi criada com sucesso no dia <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>