
<?php
    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT * FROM tb_plans");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Planos Cadastrados -->
<div class="row justify-content-center my-3">

    <?php foreach($plans as $plan): ?>
    <div class="pricing-box col-xl-3 col-md-6">
        <div class="card card-h-full">
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

                <ul class="flex-grow-1 plan-stats list-unstyled mb-0">
                    <?php 
                        // Exibe os módulos acessíveis, se houver
                        $modules = json_decode($plan['accessible_modules'], true);

                        // Mapeamento dos valores para os nomes dos módulos
                        $modulesLabels = [
                            'gdok_entregas'    => $project['name'].' Entregas',
                            'gdok_envios'      => $project['name'].' Envios',
                            'gdok_holerites'   => $project['name'].' Holerites',
                            'gdok_honorarios'  => $project['name'].' Honorários',
                            'gdok_vencimento'  => $project['name'].' Vencimento',
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
            </div>
            
            <div class="card-footer">
                <div class="flex-shrink-0 text-end align-bottom">
                    <a href="<?= INCLUDE_PATH_DASHBOARD; ?>editar-plano/<?= $plan['id']; ?>" class="btn btn-sm bg-primary-subtle text-primary me-1">
                        <i class="mdi mdi-pencil-outline fs-14 text-primary align-middle"></i>
                        Editar
                    </a>
                    <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Deletar Plano" data-id="<?= $plan['id']; ?>" data-name="<?= htmlspecialchars($plan['plan_name']); ?>">
                        <i class="mdi mdi-delete fs-14 text-danger align-middle"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>