<!-- Left Sidebar Start -->
<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <div class="logo-box">
                <a href="index.html" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-sm.png" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/logo-light.png" alt="" height="50">
                    </span>
                </a>
                <a href="index.html" class="logo logo-dark">
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

                <li class="menu-title">Páginas</li>

                <li>
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>empresas" class="tp-link">
                        <i data-feather="briefcase"></i>
                        <span> Empresas </span>
                    </a>
                </li>

                <li>
                    <a href="#sidebarDocuments" data-bs-toggle="collapse">
                        <i data-feather="file-text"></i>
                        <span> Documentos </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarDocuments">
                        <ul class="nav-second-level">
                            <li>
                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-documento" class="tp-link">Upload Manual</a>
                            </li>
                            <hr style="margin: .5rem 1.25rem;">
                            <li>
                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>documentos" class="tp-link">Documentos</a>
                            </li>
                            <li>
                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>tipos-documentos" class="tp-link">Tipos de Documentos</a>
                            </li>
                            <li>
                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>categorias" class="tp-link">Categorias</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#sidebarOffice" data-bs-toggle="collapse">
                        <i data-feather="printer"></i>
                        <span> Escritório </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarOffice">
                        <ul class="nav-second-level">
                            <li>
                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>escritorio" class="tp-link">Escritório</a>
                            </li>
                            <li>
                                <a href="<?= INCLUDE_PATH_DASHBOARD; ?>parametrizar-notificacoes" class="tp-link">Notificações</a>
                            </li>
                        </ul>
                    </div>
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

    </div>
</div>
<!-- Left Sidebar End -->