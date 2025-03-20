<?php
    $link = (!empty($tab)) ? $url : $token;

    $urlCompleta = INCLUDE_PATH_PORTAL."$link/$token";

    $stmt = $conn->prepare("
        SELECT o.*, u.id AS user_id
        FROM tb_offices o
        JOIN tb_office_users ou ON o.id = ou.office_id
        JOIN tb_users u ON ou.user_id = u.id
        WHERE o.link = ?
        LIMIT 1
    ");
    $stmt->execute([$link]);

    if ($stmt->rowCount()) {
        $office = $stmt->fetch(PDO::FETCH_ASSOC);

        // Consulta para buscar empresas cadastradas
        $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
        $stmt->execute([$office['user_id']]);
        $companiesSelect = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // TEMPORARIO, CRIAR MAIN PARA PAGINAS, EX.: MAIN PARA PAGINAS DE LOGIN, MAIN PARA PAGINAS DO PAINEL
    // Durante o processo de login
    // $stmt = $conn->prepare("
    //     SELECT u.*, un.network_id, i.id AS inviter_id
    //     FROM tb_users u
    //     LEFT JOIN tb_user_networks un ON u.id = un.user_id
    //     LEFT JOIN tb_networks n ON un.network_id = n.id
    //     LEFT JOIN tb_users i ON n.inviter_id = i.id
    //     WHERE u.id = ?
    // ");
    if (!isset($_SESSION['user_id']) && empty($_SESSION['user_id']) && !isset($_SESSION['email']) && empty($_SESSION['email'])) {
        // Salva pagina que o usuario estava anteriormente
        $_SESSION['http_referer'] = $url;

        $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Por favor faça login para acessar essa página.');

        // Usuário não está logado ou não tem permissões
        header("Location: " . INCLUDE_PATH_AUTH);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT u.*, a.city, a.state
        FROM tb_users u
        LEFT JOIN tb_address a ON a.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);

    if ($stmt->rowCount()) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("
            SELECT o.*, oa.id AS office_address_id, oa.*, ou.role
            FROM tb_users u
            JOIN tb_office_users ou ON ou.user_id = u.id
            JOIN tb_offices o ON o.id = ou.office_id
            JOIN tb_office_addresses oa ON o.id = oa.office_id AND type = 'headquarters'
            WHERE u.id = ?
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $office = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$office) {
            session_destroy();
            session_start();
            $_SESSION['msg'] = array('status' => 'error', 'alert' => 'danger', 'title' => 'Erro', 'message' => 'Por favor finalize seu cadastro para acessar essa página.');
            header('Location: ' . INCLUDE_PATH_AUTH);
            exit;
        }

        $user['profile_image'] = isset($user['profile_image']) || !empty($user['profile_image']) ? $user['profile_image'] : INCLUDE_PATH_DASHBOARD . "assets/images/profile-image/no-image.svg";

        // Define variáveis ​​com nomes diferentes
        $user['fullname'] = $user['firstname'] . " " . $user['lastname'];
        $user['surname'] = explode(' ', $user['lastname'])[0];
        $user['shortname'] = $user['firstname'] . " " . $user['surname'];
    } else {
        session_destroy();
        header('Location: ' . INCLUDE_PATH_AUTH);
        exit;
    }
?>

<!-- Begin page -->
<div id="app-layout">

    <!-- Topbar Start -->
    <?php
        include('template-parts/header.php');
    ?>
    <!-- end Topbar -->

    <!-- Left Sidebar Start -->
    <?php
        include('template-parts/office-portal-sidebar.php');
    ?>
    <!-- Left Sidebar End -->

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="content-page">
        <div class="content">

            <!-- Start Content-->
            <div class="container-fluid">

                <!-- Exibição de mensagem de sucesso ou erro -->
                <?php if (isset($_SESSION['msg'])): ?>
                    <div class="alert alert-<?= $_SESSION['msg']['alert']; ?> alert-dismissible fade show w-100 mt-3" role="alert">
                        <?= $_SESSION['msg']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">  
                        </button>
                    </div>
                <?php unset($_SESSION['msg']); endif; ?>

                <!-- page content -->
                <?php
                    if (isset($_GET['documento'])) {
                        $page = 'documento';
                    } else if (empty($link)) {
                        $page = '404';
                    } else {
                        $page = 'home';
                    }

                    if (file_exists('pages/portal/' . $page . '.php')) {
                        include('pages/portal/' . $page . '.php');
                    } else {
                        // A página não existe
                        header('Location: ' . INCLUDE_PATH_DASHBOARD . '404');
                        exit;
                    }
                ?>
                <!-- end page content -->

            </div> <!-- container-fluid -->
        </div> <!-- content -->

        <!-- Footer Start -->
        <!-- <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col fs-13 text-muted text-center">
                        &copy; <script>document.write(new Date().getFullYear())</script> - Made with <span class="mdi mdi-heart text-danger"></span> by <a href="#!" class="text-reset fw-semibold">Zoyothemes</a> 
                    </div>
                </div>
            </div>
        </footer> -->
        <!-- end Footer -->

    </div>
    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

</div>
<!-- END wrapper -->