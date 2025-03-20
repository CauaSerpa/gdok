<!-- Left Sidebar Start -->
<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <div class="logo-box">
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-light.png" alt="" height="50">
                    </span>
                </a>
                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-dark.png" alt="" height="50">
                    </span>
                </a>
            </div>

            <ul id="side-menu">

                <?php
                    // Consulta para obter departamentos únicos que possuem documentos
                    $stmt = $conn->prepare("
                        SELECT DISTINCT sd.* 
                        FROM tb_sending_documents d
                        JOIN tb_sending_departments sd ON d.department_id = sd.id
                        WHERE d.user_id = ? AND d.company_id = ?
                    ");
                    $stmt->execute([$company['user_id'], $company['id']]);
                    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <li class="menu-title">Deptos</li>

                <?php if ($departments): ?>
                    <?php foreach ($departments as $department): ?>
                        <?php
                            // Consulta para obter categorias únicas que possuem documentos para este departamento
                            $stmt = $conn->prepare("
                                SELECT DISTINCT c.*
                                FROM tb_sending_categories c
                                JOIN tb_sending_documents d ON d.category_id = c.id
                                WHERE c.department_id = ? AND c.user_id = ? AND d.company_id = ?
                            ");
                            $stmt->execute([$department['id'], $company['user_id'], $company['id']]);
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php if ($categories): ?>
                            <li>
                                <a href="#sidebar<?= $department['id']; ?>" data-bs-toggle="collapse">
                                    <span><?= $department['name']; ?></span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebar<?= $department['id']; ?>">
                                    <ul class="nav-second-level">
                                        <?php foreach ($categories as $category): ?>
                                            <li>
                                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>c/<?= $category['id']; ?>?depto=<?= $department['id']; ?>" class="tp-link">
                                                    <?= $category['name']; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        <i class="mdi mdi-alert-octagon-outline me-2"></i>
                        Não foram encontrados departamentos para esta empresa.
                    </div>
                <?php endif; ?>

            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

        <style>
            .sidebar-footer {
                position: absolute;
                bottom: 2rem;
                left: 50%;
                transform: translateX(-50%);
            }

            li.list-inline-dots::after {
                content: ""; /* ou qualquer outro caractere que represente o dot */
                margin: 0 0.5rem;
                color: #556474;
                width: 4px;
                height: 4px;
                display: flex;
                background: #556474 !important;
                border-radius: 50%;
            }
        </style>

        <!-- Footer da Sidebar -->
        <div class="sidebar-footer">
            <ul class="list-inline list-inline-dots d-flex align-items-center justify-content-center mb-0">
                <li class="list-inline-item">
                    <p class="text-reset fw-semibold mb-0"><?= $project['name']; ?></p>
                </li>
                <li class="list-inline-item list-inline-dots"></li>
                <li class="list-inline-item">
                    <p class="text-reset mb-0"><?= $project['version']; ?></p>
                </li>
            </ul>
        </div>

    </div>
</div>
<!-- Left Sidebar End -->