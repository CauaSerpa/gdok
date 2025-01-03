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

                <li class="menu-title mt-2">Sair</li>
    
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