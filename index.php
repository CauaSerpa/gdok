<?php
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    session_start();
    ob_start();
    include('./config.php');

    if (isset($_SESSION['user_id']) || isset($_SESSION['finalize_registration_user_id'])) {
        $user['id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['finalize_registration_user_id'];
        $permission = getUserPermission($user['id'], $conn);
    }











// Função para determinar o título da página com base na URL
function getPageTitle($url) {
    // Mapeamento customizado: associe rotas a títulos desejados
    $map = [
        'home'                      => 'Painel Administrativo',
        'editar-empresa'            => 'Editar Empresa',
        'editar-documento'          => 'Editar Documento',
        'editar-tipo-documento'     => 'Editar Tipo de Documento',
        'editar-categoria'          => 'Editar Categoria',
        'editar-documento-envio'    => 'Editar Documento de Envio',
        'editar-categoria-envio'    => 'Editar Categoria de Envio',
        'editar-depto-envio'        => 'Editar Depto de Envio',
        'escritorio'                => 'Escritório',
        'parametrizar-notificacoes' => 'Parametrizar Notificações',
        'configuracoes'             => 'Configurações',
        'portal'                    => 'Portal',
        'c'                         => 'Categorias',
    ];

    // Se a URL estiver vazia, retorna um título padrão
    if (empty($url)) {
        return 'Painel';
    }

    // Se a URL contém barras, considere o primeiro segmento
    $segments = explode('/', $url);
    $firstSegment = strtolower($segments[0]);

    // Se existir um título customizado para o primeiro segmento, retorna-o
    if (array_key_exists($firstSegment, $map)) {
        return $map[$firstSegment];
    }

    // Se não houver mapeamento, formata a URL: substitui traços por espaços e coloca em maiúscula
    return ucwords(str_replace("-", " ", $url));
}







    //Url Amigavel
    $url = isset($_GET['url']) ? $_GET['url'] : 'home';

    // //Edita o escrito da url para ser colocado no title
    // $title = ($url == "") ? "Painel" : ucwords(str_replace("-", " ", str_replace("/", " ", $url)));













// Determina o título com base na URL
$title = getPageTitle($url);












    // Iniciando variável $tab
    $tab = "";

    // Verifica se a URL contém pelo menos uma barra
    if (strpos($url, '/') !== false) {
        // Conta quantas barras existem na URL
        $slashCount = substr_count($url, '/');

        if (strpos($url, 'auth/') !== false) {
            // Caso a URL contenha "auth"
            if ($slashCount == 1) {
                // Há apenas uma barra na URL
                list($tab, $url) = explode('/', $url, 2);
            } elseif ($slashCount > 1) {
                // Há mais de uma barra na URL
                list($tab, $url, $token) = explode('/', $url, 3);
            }
        } else {
            // Caso a URL não contenha "auth"
            if ($slashCount == 1) {
                // Há apenas uma barra na URL
                list($url, $token) = explode('/', $url, 2);
            } elseif ($slashCount > 1) {
                // Há mais de uma barra na URL
                list($tab, $url, $token) = explode('/', $url, 3);
            }
        }
    }

    // echo "Tab: $tab<br>";
    // echo "Url: $url<br>";
    // echo "Token: $token<br>";
    // exit;
?>

<?php
    if (isset($_SESSION['user_id'])) {
        // Contar numero de notificacoes
        // Data atual
        $currentDate = date('Y-m-d');

        $sql = "SELECT COUNT(id) AS count FROM tb_notifications WHERE user_id = ? AND is_read = 0 AND notification_type IN ('system', 'document_expiration_system', 'custom', 'document') ORDER BY created_at DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);

        // Obter os resultados
        $notifications = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>

        <meta charset="utf-8" />
        <title><?= (isset($notifications) && $notifications > 0) ? '(' . $notifications .  ') ' : ''; ?>GDok - <?= $title; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Gestão de documentos."/>
        <meta name="author" content="GDok"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/favicon.ico">

        <!-- App css -->
        <link href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
        <!-- <link href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" /> -->

        <!-- Flatpickr Timepicker css -->
        <link href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />

        <!-- Icons -->
        <link href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />

        <!-- Vendor -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/jquery/jquery.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/jquery.counterup/jquery.counterup.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- jQuery Validation, Input Mask, and Validation Script -->
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <?php if ($tab == "auth" || $url == "auth"): ?>

        <!-- Vendor -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/simplebar/simplebar.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/node-waves/waves.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/waypoints/lib/jquery.waypoints.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/feather-icons/feather.min.js"></script>

        <!-- Apexcharts JS -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/apexcharts/apexcharts.min.js"></script> -->

        <!-- for basic area chart -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>https://apexcharts.com/samples/assets/stock-prices.js"></script> -->

        <!-- Widgets Init Js -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/pages/crm-dashboard.init.js"></script> -->

        <!-- App js-->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/app.js"></script> -->

        <?php elseif ($url == "home"): ?>
        <?php endif; ?>

    </head>

    <body <?= ($tab == "auth" || $url == "auth") ? 'class="bg-primary-subtle"' : (($url == "portal") ? 'data-menu-color="light" data-sidebar="hidden"' : 'data-menu-color="light" data-sidebar="default"'); ?>>

        <?php
            // Se for a página de verificação de e-mail
            if ($url == "404") {
                include('templates/error-pages/404.php');
            } elseif ($tab == "auth" || $url == "auth") {
                include('templates/auth.php');
            } elseif ($permission['role'] == 3) {
                include('templates/portal-cliente.php');
            } elseif ($tab == "portal" || $url == "portal") {
                include('templates/portal.php');
            } else {
                include('templates/dashboard.php');
            }
        ?>

        <?php if ($tab !== "auth" || $url !== "auth"): ?>

        <!-- Vendor -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script> -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/simplebar/simplebar.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/node-waves/waves.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/waypoints/lib/jquery.waypoints.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/feather-icons/feather.min.js"></script>

        <?php if ($url == "home"): ?>

        <!-- Apexcharts JS -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- for basic area chart -->
        <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>

        <!-- Widgets Init Js -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/pages/crm-dashboard.init.js"></script>

        <!-- Apexcharts JS -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- for basic area chart -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>https://apexcharts.com/samples/assets/stock-prices.js"></script>

        <!-- Apexcharts Init Js -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/pages/apexcharts-line.init.js"></script>

        <?php endif; ?>

        <!-- Datatables js -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>

        <!-- dataTables.bootstrap5 -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>

        <!-- buttons.colVis -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-buttons/js/buttons.flash.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>

        <!-- buttons.bootstrap5 -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>

        <!-- dataTables.keyTable -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js"></script>

        <!-- dataTable.responsive -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>

        <!-- dataTables.select -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js"></script>

        <!-- Datatable Demo App Js -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/pages/datatable.init.js"></script>

        <!-- Flatpickr Timepicker Plugin js -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/flatpickr/flatpickr.min.js"></script>

        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/pages/form-picker.js"></script>

        <!-- App js-->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/app.js"></script>

        <?php endif; ?>

    </body>
</html>