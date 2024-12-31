<!-- Begin page -->
<div class="account-page">
    <div class="container-fluid p-0">
        <div class="row align-items-center g-0">

            <!-- page content -->
            <?php
                // Se a url for ex.: "auth" sem barra ou se estiver vazia ex.: "auth/", Adiciona login ex.: "auth/login"
                $url = ($url == "auth" || empty($url)) ? "login" : $url;

                if (file_exists('pages/auth/' . $url . '.php')) {
                    include('pages/auth/' . $url . '.php');
                } else {
                    // A página não existe
                    header('Location: ' . INCLUDE_PATH_DASHBOARD . '404');
                    exit;
                }
            ?>
            <!-- end page content -->

            <div class="col-xl-7">
                <div class="account-page-bg p-md-5 p-4">
                    <div class="text-center">
                        <div class="auth-image">
                            <img src="<?= INCLUDE_PATH_DASHBOARD; ?>assets/images/auth-images.svg" class="mx-auto img-fluid"  alt="images">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- END wrapper -->