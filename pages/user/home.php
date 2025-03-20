<?php
    // Consultar número total de empresas do usuário no mês atual
    $queryTotalCompanies = "SELECT COUNT(*) as total FROM tb_companies WHERE user_id = ?";
    $resultCompanies = $conn->prepare($queryTotalCompanies);
    $resultCompanies->execute([$_SESSION['user_id']]);
    $totalCompanies = $resultCompanies->fetch(PDO::FETCH_ASSOC)['total'];

    // Consultar número total de empresas do mês passado
    $queryTotalCompaniesLastMonth = "SELECT COUNT(*) as total 
                                    FROM tb_companies 
                                    WHERE user_id = ? 
                                    AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 2 MONTH) AND DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $resultCompaniesLastMonth = $conn->prepare($queryTotalCompaniesLastMonth);
    $resultCompaniesLastMonth->execute([$_SESSION['user_id']]);
    $totalCompaniesLastMonth = $resultCompaniesLastMonth->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Consultar número de documentos vencidos há 30 dias no mês atual
    $queryExpiredDocs = "SELECT COUNT(*) as total 
                        FROM tb_documents
                        WHERE user_id = ?
                        AND expiration_date BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()";
    $resultExpired = $conn->prepare($queryExpiredDocs);
    $resultExpired->execute([$_SESSION['user_id']]);
    $totalExpiredDocs = $resultExpired->fetch(PDO::FETCH_ASSOC)['total'];

    // Consultar número de documentos vencidos há 30 dias no mês passado
    $queryExpiredDocsLastMonth = "SELECT COUNT(*) as total 
                                FROM tb_documents
                                WHERE user_id = ?
                                AND expiration_date BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $resultExpiredLastMonth = $conn->prepare($queryExpiredDocsLastMonth);
    $resultExpiredLastMonth->execute([$_SESSION['user_id']]);
    $totalExpiredDocsLastMonth = $resultExpiredLastMonth->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Consultar número de documentos que vencerão em 30 dias
    $queryExpiringDocs = "SELECT COUNT(*) as total 
                        FROM tb_documents
                        WHERE user_id = ?
                        AND expiration_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)";
    $resultExpiring = $conn->prepare($queryExpiringDocs);
    $resultExpiring->execute([$_SESSION['user_id']]);
    $totalExpiringDocs = $resultExpiring->fetch(PDO::FETCH_ASSOC)['total'];

    // Consultar número total de empresas do mês passado
    $queryTotalCompaniesLastMonth = "SELECT COUNT(*) as total 
                                    FROM tb_companies 
                                    WHERE user_id = ? 
                                    AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
                                    AND YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
    $resultCompaniesLastMonth = $conn->prepare($queryTotalCompaniesLastMonth);
    $resultCompaniesLastMonth->execute([$_SESSION['user_id']]);
    $totalCompaniesLastMonth = $resultCompaniesLastMonth->fetch(PDO::FETCH_ASSOC)['total'];

    // Consultar número de documentos vencidos há 30 dias (mês passado)
    $queryExpiredDocsLastMonth = "SELECT COUNT(*) as total 
                                FROM tb_documents
                                WHERE user_id = ? 
                                AND expiration_date <= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                AND MONTH(expiration_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
                                AND YEAR(expiration_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
    $resultExpiredLastMonth = $conn->prepare($queryExpiredDocsLastMonth);
    $resultExpiredLastMonth->execute([$_SESSION['user_id']]);
    $totalExpiredDocsLastMonth = $resultExpiredLastMonth->fetch(PDO::FETCH_ASSOC)['total'];

    // Consultar número de documentos que vencerão em 30 dias (mês passado)
    $queryExpiringDocsLastMonth = "SELECT COUNT(*) as total 
                                FROM tb_documents
                                WHERE user_id = ? 
                                AND expiration_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
                                AND MONTH(expiration_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
                                AND YEAR(expiration_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
    $resultExpiringLastMonth = $conn->prepare($queryExpiringDocsLastMonth);
    $resultExpiringLastMonth->execute([$_SESSION['user_id']]);
    $totalExpiringDocsLastMonth = $resultExpiringLastMonth->fetch(PDO::FETCH_ASSOC)['total'];

    // Calcular a diferença percentual
    $diffCompaniesPercentage = $totalCompaniesLastMonth > 0 ? (($totalCompanies - $totalCompaniesLastMonth) / $totalCompaniesLastMonth) * 100 : 0;
    $diffExpiredDocsPercentage = $totalExpiredDocsLastMonth > 0 ? (($totalExpiredDocs - $totalExpiredDocsLastMonth) / $totalExpiredDocsLastMonth) * 100 : 0;
    $diffExpiringDocsPercentage = $totalExpiringDocsLastMonth > 0 ? (($totalExpiringDocs - $totalExpiringDocsLastMonth) / $totalExpiringDocsLastMonth) * 100 : 0;

    // Criar um array com os resultados e as diferenças percentuais
    $cardsData = [
        "total_companies" => $totalCompanies,
        "total_companies_diff_percentage" => round($diffCompaniesPercentage, 2),
        "expired_documents" => $totalExpiredDocs,
        "expired_documents_diff_percentage" => round($diffExpiredDocsPercentage, 2),
        "expiring_documents" => $totalExpiringDocs,
        "expiring_documents_diff_percentage" => round($diffExpiringDocsPercentage, 2)
    ];
?>

<?php
    // Preparar a consulta para obter o número de documentos vencendo em cada mês dos próximos 12 meses
    $queryDocsByMonth = "
        SELECT 
            COUNT(*) as total, 
            MONTH(expiration_date) as month, 
            YEAR(expiration_date) as year
        FROM tb_documents
        WHERE user_id = :user_id 
        AND expiration_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND DATE_ADD(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY YEAR(expiration_date), MONTH(expiration_date)
        ORDER BY YEAR(expiration_date), MONTH(expiration_date)
    ";

    // Preparar a consulta
    $resultDocsByMonth = $conn->prepare($queryDocsByMonth);
    $resultDocsByMonth->execute(['user_id' => $_SESSION['user_id']]);

    // Inicializar os arrays
    $months = [];
    $documentCounts = [];

    // Array de meses traduzidos
    $monthNames = [
        1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 
        7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez"
    ];

    // Inicializar o array com todos os meses do próximo ano
    $allMonths = [];
    for ($i = 0; $i < 12; $i++) {
        $monthNumber = (date('n') + $i - 1) % 12 + 1; // Calcula o mês correto
        $year = date('Y') + floor(($i + date('n') - 1) / 12); // Ajusta o ano se necessário
        $allMonths[] = $monthNames[$monthNumber] . " " . $year; // Adiciona o mês no formato "M Y"
        $documentCounts[] = 0; // Inicializa o número de documentos como 0
    }

    // Preencher os arrays com os dados reais
    while ($row = $resultDocsByMonth->fetch(PDO::FETCH_ASSOC)) {
        $monthName = $monthNames[$row['month']] . " " . $row['year'];
        $key = array_search($monthName, $allMonths); // Encontrar o índice do mês real

        if ($key !== false) {
            $documentCounts[$key] = (int)$row['total']; // Atualiza o número de documentos para o mês correspondente
        }
    }

    // Estruturar os dados em formato JSON para o ApexCharts
    $chartDataValidityDocuments = [
        "categories" => $allMonths,
        "series" => [
            [
                "name" => "Documentos para vencer",
                "data" => $documentCounts
            ]
        ]
    ];
?>

<?php
    // Consulta para listar até 5 tipos de documentos com mais documentos associados
    $query = "
        SELECT dt.id, dt.name, dtc.name AS category_name, COUNT(d.id) AS document_count
        FROM tb_document_types dt
        LEFT JOIN tb_document_type_categories dtc ON dt.category_id = dtc.id
        LEFT JOIN tb_documents d ON d.document_type_id = dt.id
        WHERE dt.user_id = :user_id
        GROUP BY dt.id, dt.name, dtc.name
        ORDER BY document_count DESC
        LIMIT 5
    ";

    // Preparar a consulta
    $result = $conn->prepare($query);
    $result->execute(['user_id' => $_SESSION['user_id']]);

    // Inicializar o array para os dados dos tipos de documentos
    $documentTypes = [];
    $documentCounts = [];

    // Preencher o array com os dados dos tipos de documentos
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $documentTypes[] = $row['name'];
        $documentCounts[] = (int)$row['document_count'];
    }

    // Estruturar os dados em formato JSON para o gráfico de pizza
    $chartDataDocumentsTypes = [
        "categories" => $documentTypes,
        "series" => $documentCounts
    ];
?>

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
    </div>
</div>

<div class="col-md-12 col-xl-12">
    <div class="row g-3">
        
        <div class="col-md-4 col-xl-4">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="widget-first">

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-primary border-opacity-10 bg-primary-subtle rounded-pill me-2">
                                    <div class="bg-primary rounded-circle widget-size text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 640 512"><path fill="#ffffff" d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64s-64 28.7-64 64s28.7 64 64 64m448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64s-64 28.7-64 64s28.7 64 64 64m32 32h-64c-17.6 0-33.5 7.1-45.1 18.6c40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64m-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32S208 82.1 208 144s50.1 112 112 112m76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2m-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4"/></svg>
                                    </div>
                                </div>
                                <p class="mb-0 text-dark fs-15">Clientes gerenciados</p>
                            </div>
                            <h3 class="mb-0 fs-22 text-black me-3"><?= $cardsData['total_companies']; ?></h3>
                        </div>

                        <!-- <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fs-22 text-black me-3"><?= $cardsData['total_companies']; ?></h3>

                            <div class="text-center">
                                <span class="<?= $cardsData['total_companies_diff_percentage'] >= 0 ? "text-primary" : "text-danger"; ?> fs-14"><i class="mdi <?= $cardsData['total_companies_diff_percentage'] >= 0 ? "mdi-trending-up" : "mdi-trending-down"; ?> fs-14"></i> <?= $cardsData['total_companies_diff_percentage']; ?>%</span>
                                <p class="text-dark fs-13 mb-0">Últimos 30 dias</p>
                            </div>
                        </div> -->
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="widget-first">

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-danger border-opacity-10 bg-danger-subtle rounded-pill me-2">
                                    <div class="bg-danger rounded-circle widget-size text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" class="feather feather-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                </div>
                                <p class="mb-0 text-dark fs-15">Docs vencidos (30 dias)</p>
                            </div>
                            <h3 class="mb-0 fs-22 text-black me-3"><?= $cardsData['expired_documents']; ?></h3>
                        </div>

                        <!-- <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fs-22 text-black me-3"><?= $cardsData['expired_documents']; ?></h3>

                            <div class="text-center">
                                <span class="<?= $cardsData['expired_documents_diff_percentage'] >= 0 ? "text-primary" : "text-danger"; ?> fs-14 me-2"><i class="mdi <?= $cardsData['expired_documents_diff_percentage'] >= 0 ? "mdi-trending-up" : "mdi-trending-down"; ?> fs-14"></i> <?= $cardsData['expired_documents_diff_percentage']; ?>%</span>
                                <p class="text-dark fs-13 mb-0">Últimos 30 dias</p>
                            </div>
                        </div> -->

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="widget-first">

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-warning border-opacity-10 bg-warning-subtle rounded-pill me-2">
                                    <div class="bg-warning rounded-circle widget-size text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    </div>
                                </div>
                                <p class="mb-0 text-dark fs-15">Docs a vencer (30 dias)</p>
                            </div>
                            <h3 class="mb-0 fs-22 text-black me-3"><?= $cardsData['expiring_documents']; ?></h3>
                        </div>

                        <!-- <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fs-22 text-black me-3"><?= $cardsData['expiring_documents']; ?></h3>

                            <div class="text-muted">
                                <span class="<?= $cardsData['expiring_documents_diff_percentage'] >= 0 ? "text-primary" : "text-danger"; ?> fs-14 me-2"><i class="mdi <?= $cardsData['expiring_documents_diff_percentage'] >= 0 ? "mdi-trending-up" : "mdi-trending-down"; ?> fs-14"></i> <?= $cardsData['expiring_documents_diff_percentage']; ?>%</span>
                                <p class="text-dark fs-13 mb-0">Últimos 30 dias</p>
                            </div>
                        </div> -->

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <!-- Documentos a vencer por mês -->
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black mb-0">Documentos a vencer por mês</h5>
                </div>
            </div>

            <div class="card-body">
                <div id="months_validity_documents" class="apex-charts"></div>
            </div>
        </div>  
    </div>

    <div class="col-xl-5">
        <div class="card overflow-hidden">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black mb-0">Tipo de documentos mais gerenciados</h5>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- <div class="col-xxl-12">
                        <div id="document_types_chart" class="apex-charts"></div>        
                    </div> -->

                    <!-- <div class="col-xxl-12 align-self-center">
                        <h3 class="fs-18 fw-semibold text-black mb-3">Data Statistic</h3>
                        <ul class="list-unstyled mb-0">
                            
                            <li class="list-item mb-2 align-self-center">
                                <div class="d-flex align-items-center justify-content-between fs-15">
                                    <div class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" class="me-0"><path fill="#2786f1" d="M4 8a4 4 0 1 1 8 0a4 4 0 0 1-8 0m4-2.5a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5"/></svg>
                                        <span class="text-black fw-normal">To Be Packed </span> 
                                    </div>
                                    <p class="mb-0 text-muted">157.880</p>
                                </div>
                            </li>

                            <li class="list-item mb-2 align-self-center">
                                <div class="d-flex align-items-center justify-content-between fs-15">
                                    <div class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16"><path fill="#f59440" d="M4 8a4 4 0 1 1 8 0a4 4 0 0 1-8 0m4-2.5a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5"/></svg>
                                        <span class="text-black fw-normal">Process Delivery </span>
                                    </div>
                                    <p class="mb-0 text-muted">198.254</p>
                                </div>
                            </li>

                            <li class="list-item text-black align-self-center fs-15">
                                <div class="d-flex align-items-center justify-content-between fs-15">
                                    <div class="">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16"><path fill="#522c8f" d="M4 8a4 4 0 1 1 8 0a4 4 0 0 1-8 0m4-2.5a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5"/></svg>
                                        <span class="text-black fw-normal">Delivery Done</span>
                                    </div>
                                    <p class="mb-0 text-muted">142.278</p>
                                </div>
                            </li>

                        </ul>
                    </div> -->
                    <div class="col-xxl-12">
                        <div id="document_types_chart" class="apex-charts"></div>
                    </div>

                    <div class="col-xxl-12 align-self-center">
                        <ul class="list-unstyled mb-0">
                            <?php 
                            $colors = ["#287F71", "#963b68", "#2786f1", "#f59440", "#ec344c", "#4a5a6b"];
                            foreach ($documentTypes as $index => $type): 
                            ?>
                                <li class="list-item mb-2 align-self-center">
                                    <div class="d-flex align-items-center justify-content-between fs-15">
                                        <div class="">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" class="me-0">
                                                <path fill="<?php echo $colors[$index]; ?>" d="M4 8a4 4 0 1 1 8 0a4 4 0 0 1-8 0m4-2.5a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5"/>
                                            </svg>
                                            <span class="text-black fw-normal"><?php echo $type; ?></span>
                                        </div>
                                        <p class="mb-0 text-muted"><?php echo $documentCounts[$index]; ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Dados do gráfico gerados pelo PHP
        var optionsValidityDocuments = {
            series: [{
                name: "Documentos para vencer",
                data: <?php echo json_encode($chartDataValidityDocuments['series'][0]['data']); ?>
            }],
            chart: {
                height: 380,
                type: "line",
                parentHeightOffset: 0,
                zoom: {
                    enabled: !1
                },
                toolbar: {
                    show: !1
                }
            },
            markers: {
                size: 4
            },
            dataLabels: {
                enabled: !1
            },
            stroke: {
                curve: "straight"
            },
            colors: ["#287F71"],
            grid: {
                row: {
                    colors: ["#f3f3f3", "transparent"],
                    opacity: .5
                }
            },
            xaxis: {
                categories: <?php echo json_encode($chartDataValidityDocuments['categories']); ?>
            },
            responsive: [{
                breakpoint: 600,
                options: {
                    chart: {
                        toolbar: {
                            show: !1
                        }
                    },
                    legend: {
                        show: !1
                    }
                }
            }]
        };

        var chartValidityDocuments = new ApexCharts(document.querySelector("#months_validity_documents"), optionsValidityDocuments);
        chartValidityDocuments.render();
    });

    $(document).ready(function() {
        var optionsDocumentsTypes = {
            chart: {
                height: 200,
                type: "donut"
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: "75%"
                    }
                }
            },
            dataLabels: {
                enabled: !1
            },
            stroke: {
                show: !0,
                width: 2,
                colors: ["transparent"]
            },
            series: <?php echo json_encode($chartDataDocumentsTypes['series']); ?>,
            legend: {
                show: !1,
                position: "bottom",
                horizontalAlign: "center",
                verticalAlign: "middle",
                floating: !1,
                fontSize: "14px",
                offsetX: 0,
                offsetY: 0
            },
            labels: <?php echo json_encode($chartDataDocumentsTypes['categories']); ?>,
            colors: ["#287F71", "#963b68", "#2786f1", "#f59440", "#ec344c", "#4a5a6b"]
        };

        var chartDocumentsTypes = new ApexCharts(document.querySelector("#document_types_chart"), optionsDocumentsTypes);
        chartDocumentsTypes.render();
    });
</script>