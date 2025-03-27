
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Planos</h4>
    </div>
</div>

<?php
    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT * FROM tb_plans WHERE active_plan = 1 AND public_plan = 1 ORDER BY plan_price ASC");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row justify-content-center">
    <div class="col-lg-12">

        <!-- Pricing Title-->
        <div class="text-center">
            <h3 class="mb-2">Preço dos Planos</h3>
            <p class="text-muted mb-5">
                Temos planos e preços que se encaixam perfeitamente no seu negócio. Faça do site do seu cliente <br> um sucesso com nossos produtos.
            </p>
        </div>

        <!-- Planos Cadastrados -->
        <div class="row justify-content-center my-3">

            <?php foreach($plans as $plan): ?>
            <div class="pricing-box col-xl-3 col-md-6">
                <div class="<?= ($plan['default_plan']) ? "card card-h-full border-primary border shadow-none" : "card card-h-full"; ?>">
                    <div class="inner-box card-body p-4">
                        <!-- Ícone de Edição -->
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 px-2 py-1 d-inline-flex justify-content-center align-content-center">
                            <i class="mdi mdi-office-building fs-19"></i>
                        </div>

                        <div class="flex-shrink-0 pb-3 mb-1 mt-4">
                            <h2 class="month mb-0">
                                <sup class="fw-semibold"><small>R$</small></sup> 
                                <span class="fw-semibold fs-28"><?= number_format($plan['plan_price'], 2, ',', '.'); ?></span> / 
                                <span class="fs-14 text-muted"><?= ucfirst($plan['billing_period']); ?></span>
                            </h2>
                        </div>

                        <div class="plan-header">
                            <h5 class="plan-title text-primary"><?= htmlspecialchars($plan['plan_name']); ?></h5>
                            <p class="plan-subtitle"><?= htmlspecialchars($plan['plan_description']); ?></p>
                        </div>

                        <ul class="flex-grow-1 plan-stats list-unstyled">
                            <?php 
                                // Exibe os módulos acessíveis, se houver
                                $modules = json_decode($plan['accessible_modules'], true);

                                // Mapeamento dos valores para os nomes dos módulos
                                $modulesLabels = [
                                    'gdok_vencimento'  => $project['name'].' Vencimento',
                                    'gdok_envios'      => $project['name'].' Envios',
                                    'gdok_honorarios'  => $project['name'].' Honorários',
                                    'gdok_entregas'    => $project['name'].' Entregas'
                                ];

                                if ($modules && count($modules)) {
                                    foreach ($modules as $module): 
                                ?>
                                    <li>
                                        <i data-feather="check" class="check-icon text-primary me-2"></i>
                                        <?= htmlspecialchars($modulesLabels[$module] ?? $module); ?>
                                    </li>
                                <?php 
                                    endforeach;
                                } else {
                                    echo '<li>Nenhum módulo disponível</li>';
                                }
                            ?>
                        </ul>

                        <div class="flex-shrink-0 text-center">
                            <a href="#" class="btn <?= ($plan['default_plan']) ? "btn-primary" : "btn-outline-primary fw-medium"; ?> w-100 rounded-2">Comprar Plano</a>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div>

    </div>
</div>
