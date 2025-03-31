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

                <li class="menu-title">Menu</li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>" class="tp-link active">
                        <i data-feather="home"></i>
                        <span> Home </span>
                    </a>
                </li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>aparencia" class="tp-link">
                        <i data-feather="sliders"></i>
                        <span> Aparência </span>
                    </a>
                </li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>planos" class="tp-link">
                        <i data-feather="dollar-sign"></i>
                        <span> Planos </span>
                    </a>
                </li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cupons" class="tp-link">
                        <i data-feather="percent"></i>
                        <span> Cupons </span>
                    </a>
                </li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>usuarios" class="tp-link">
                        <i data-feather="users"></i>
                        <span> Usuários </span>
                    </a>
                </li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>clientes" class="tp-link">
                        <i data-feather="book-open"></i>
                        <span> Clientes </span>
                    </a>
                </li>

                <li class="menu-title mt-2">Conta</li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>configuracoes" class="tp-link">
                        <i data-feather="settings"></i>
                        <span> Configurações </span>
                    </a>
                </li>

                <li>
                    <a href="<?= INCLUDE_PATH_AUTH; ?>sair" class="tp-link">
                        <i data-feather="log-out"></i>
                        <span> Sair </span>
                    </a>
                </li>

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