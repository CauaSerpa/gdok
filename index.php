<?php
    session_start();
    ob_start();
    include('./config.php');

    //Url Amigavel
    $url = isset($_GET['url']) ? $_GET['url'] : 'home';

    //Edita o escrito da url para ser colocado no title
    $title = ($url == "") ? "Painel" : ucwords(str_replace("-", " ", str_replace("/", " ", $url)));
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>

        <meta charset="utf-8" />
        <title>GDok - <?= $title; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Gestão de documentos."/>
        <meta name="author" content="GDok"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/favicon.ico">

        <!-- App css -->
        <link href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
        <!-- <link href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" /> -->

        <!-- Icons -->
        <link href="<?= INCLUDE_PATH_DASHBOARD; ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />

        <?php if ($url !== 'home'): ?>

        <!-- Vendor -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/jquery/jquery.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/simplebar/simplebar.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/node-waves/waves.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/waypoints/lib/jquery.waypoints.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/jquery.counterup/jquery.counterup.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/feather-icons/feather.min.js"></script>

        <!-- jQuery Validation, Input Mask, and Validation Script -->
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Apexcharts JS -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/apexcharts/apexcharts.min.js"></script> -->

        <!-- for basic area chart -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>https://apexcharts.com/samples/assets/stock-prices.js"></script> -->

        <!-- Widgets Init Js -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/pages/crm-dashboard.init.js"></script> -->

        <!-- App js-->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/app.js"></script> -->

        <?php endif; ?>

    </head>

    <body <?= ($url == 'home') ? 'data-menu-color="light" data-sidebar="default"' : 'class="bg-primary-subtle"'; ?>>

        <?php
            // Iniciando variável $tab
            $tab = "";

            // Verifica se a URL contém pelo menos uma barra
            if (strpos($url, '/') !== false) {
                // Conta quantas barras existem na URL
                $slashCount = substr_count($url, '/');

                if ($slashCount == 1) {
                    // Há apenas uma barra na URL
                    list($tab, $url) = explode('/', $url, 2);
                } elseif ($slashCount > 1) {
                    // Há mais de uma barra na URL
                    list($tab, $url, $token) = explode('/', $url, 3);
                }
            }

            // Se for a página de verificação de e-mail
            if ($url == "404") {
                include('templates/error-pages/404.php');
            } elseif ($tab == "auth" || $url == "auth") {
                include('templates/auth.php');
            } else {
                include('templates/dashboard.php');
            }
        ?>

        <?php if ($url == 'home'): ?>

        <!-- Vendor -->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/jquery/jquery.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/simplebar/simplebar.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/node-waves/waves.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/waypoints/lib/jquery.waypoints.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/jquery.counterup/jquery.counterup.min.js"></script>
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/feather-icons/feather.min.js"></script>

        <!-- Apexcharts JS -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- for basic area chart -->
        <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>

        <!-- Widgets Init Js -->
        <script src="assets/js/pages/crm-dashboard.init.js"></script>

        <!-- Apexcharts JS -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/libs/apexcharts/apexcharts.min.js"></script> -->

        <!-- for basic area chart -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>https://apexcharts.com/samples/assets/stock-prices.js"></script> -->

        <!-- Widgets Init Js -->
        <!-- <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/pages/crm-dashboard.init.js"></script> -->

        <!-- App js-->
        <script src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/js/app.js"></script>

        <?php endif; ?>

        
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <img src="..." class="rounded me-2" alt="...">
      <strong class="me-auto">Bootstrap</strong>
      <small>11 mins ago</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      Hello, world! This is a toast message.
    </div>
  </div>
</div>

    </body>
</html>