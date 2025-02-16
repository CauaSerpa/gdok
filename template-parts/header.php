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
                    <h5 class="mb-0"><?= getGreeting(); ?>, <?= $user['shortname']; ?></h5>
                </li>
            </ul>

            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

                <li class="d-none d-lg-block">
                    <div class="position-relative topbar-search">
                        <input type="text" class="form-control bg-light bg-opacity-75 border-light ps-4" placeholder="Search...">
                        <i class="mdi mdi-magnify fs-16 position-absolute text-muted top-50 translate-middle-y ms-2"></i>
                    </div>
                </li>

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
                        SELECT 
                            d.id,
                            d.name,
                            d.expiration_date,
                            d.advance_notification,
                            d.personalized_advance_notification,
                            u.firstname AS user_name,
                            CASE
                                WHEN d.personalized_advance_notification IS NOT NULL THEN d.personalized_advance_notification
                                ELSE d.advance_notification
                            END AS notification_days
                        FROM tb_documents d
                        INNER JOIN tb_users u ON u.id = d.user_id -- Supondo que há uma tabela de usuários
                        WHERE d.user_id = :user_id AND DATE_SUB(d.expiration_date, INTERVAL 
                            CASE
                                WHEN d.personalized_advance_notification IS NOT NULL THEN d.personalized_advance_notification
                                ELSE d.advance_notification
                            END DAY) <= :currentDate
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['currentDate' => $currentDate, 'user_id' => $_SESSION['user_id']]);

                    // Obter os resultados
                    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i data-feather="bell" class="noti-icon"></i>
                        <span class="badge bg-danger rounded-circle noti-icon-badge"><?= count($notifications); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-lg">
                        <div class="dropdown-item noti-title"><h5 class="m-0">Notificações</h5></div>
                        <div class="noti-scroll" data-simplebar>

                            <?php foreach ($notifications as $notification) : ?>
                                <a href="<?= INCLUDE_PATH_DASHBOARD ?>editar-documento/<?= $notification['id']; ?>" class="dropdown-item notify-item text-muted link-primary">
                                    <div class="notify-icon text-inherit rounded-circle border border-dashed border-warning d-flex align-items-center justify-content-center me-2" style="height: 35px; width: 35px;">
                                        <i class="mdi mdi-file-alert-outline fs-17 text-warning"></i>
                                    </div>
                                    <div class="notify-content">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <p class="notify-details fw-bold text-dark">Aviso: Documento Próximo ao Vencimento</p>
                                            <!-- <small class="text-muted"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($notification['created_at'] ?? 'now'))); ?></small> -->
                                        </div>
                                        <p class="noti-mentioned p-2 rounded-2 mb-0 mt-2">
                                            <span class="text-reset fw-bold"><?= htmlspecialchars($notification['name']) ?></span><br>
                                            Data de Vencimento: <span class="<?= $notification['expiration_date'] > $currentDate ? 'text-inherit' : 'text-danger'; ?>"><?= htmlspecialchars(date('d/m/Y', strtotime($notification['expiration_date']))); ?></span><br>
                                            Status: <?= $notification['expiration_date'] > $currentDate ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'; ?>
                                        </p>
                                        <p class="mb-0 text-muted small">
                                            Configurado para <?= $notification['advance_notification'] == "personalized" ? $notification['personalized_advance_notification'] : $notification['advance_notification']; ?> dia(s) antes do vencimento.
                                        </p>
                                    </div>
                                </a>
                            <?php endforeach; ?>

                            <!-- Notificação de criação de conta -->
                            <div class="dropdown-item notify-item text-muted">
                                <div class="notify-icon text-inherit rounded-circle border border-dashed d-flex align-items-center justify-content-center me-2" style="height: 35px; width: 35px;">
                                    <i class="mdi mdi-account-outline fs-17"></i>
                                </div>
                                <div class="notify-content">
                                    <p class="notify-details fw-bold text-dark">Usuário <?= htmlspecialchars($user['shortname']); ?> Criado</p>
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
                        <a href="#" class="dropdown-item notify-item">
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