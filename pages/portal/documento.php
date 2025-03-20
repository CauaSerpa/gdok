<?php
    $host = $_SERVER['HTTP_HOST'];
    $uri = strtok($_SERVER["REQUEST_URI"], '?');
    $urlCompleta = "http://$host$uri";

    if (isset($_GET['documento'])) {

        // Consulta para buscar empresas cadastradas
        $stmt = $conn->prepare("
                            SELECT d.*, c.name AS category, depto.name AS department 
                            FROM tb_sending_documents d 
                            LEFT JOIN tb_sending_categories c ON d.category_id = c.id 
                            LEFT JOIN tb_sending_departments depto ON d.department_id = depto.id 
                            WHERE d.id = ?");
        $stmt->execute([$_GET['documento']]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$document) {
            echo "<div class='alert alert-warning text-center'>Nenhum documento encontrado.</div>";
            exit;
        }

        // Define a cor do status de vencimento
        $today = date('Y-m-d');
        $expirationDate = $document['expiration_date'];
        $statusColor = ($expirationDate < $today) ? 'danger' : (($expirationDate == $today) ? 'warning' : 'primary');

    } else {

        echo "<div class='alert alert-warning text-center'>Nenhum documento encontrado.</div>";
        exit;

    }
?>

<div class="container">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Detalhes do Documento</h4>
        </div>
    
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="<?= $urlCompleta; ?>">Categorias de Envios</a></li>
                <li class="breadcrumb-item active">Detalhes do Documento</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detalhes do Documento "<?= htmlspecialchars($document['name']); ?>"</h5>
                </div><!-- end card header -->
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($document['name']); ?></h5>
                    <p class="card-text"><strong>Departamento:</strong> <?= htmlspecialchars($document['department']); ?></p>
                    <p class="card-text"><strong>Data de Vencimento:</strong> 
                        <span class="badge bg-<?= $statusColor; ?>"><?= date('d/m/Y', strtotime($expirationDate)); ?></span>
                    </p>
                    <?php if (!empty($document['document'])): ?>
                        <div class="mb-3">
                            <label for="current_document" class="form-label"><strong>Documento Atual:</strong></label>
                            <div>
                                <a href="<?= $document['document']; ?>" class="btn btn-primary btn-sm" target="_blank" data-bs-toggle="tooltip" title="Baixar Documento" download>
                                    <i class="mdi mdi-download fs-16 align-middle"></i>
                                    Baixar
                                </a>
                                <a href="<?= $document['document']; ?>" class="btn btn-sm btn-link" target="_blank" data-bs-toggle="tooltip" title="Pré-visualização"><?= basename($document['document']); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <p class="card-text <?= (empty($document['observation'])) ? "d-none" : ""; ?>"><strong>Observação:</strong> <?= nl2br(htmlspecialchars($document['observation'])); ?></p>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="<?= $urlCompleta; ?>" class="btn btn-light">Voltar para Documentos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>