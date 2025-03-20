<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

// Verifica se o método de requisição é GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (!$conn) {
            throw new Exception("Conexão inválida com o banco de dados.");
        }

        $currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        if (!$currentUserId) {
            throw new Exception("Usuário não autenticado.");
        }

        $category = $_GET['category'];
        $company = $_GET['company'];

        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
        $export_all = isset($_GET['export_all']) ? $_GET['export_all'] : false;

        $company_id = isset($_GET['companyFilter']) ? intval($_GET['companyFilter']) : null;
        $category_id = isset($_GET['categoryFilter']) ? intval($_GET['categoryFilter']) : null;
        $start_date = isset($_GET['startDateFilter']) ? $_GET['startDateFilter'] : null;
        $end_date = isset($_GET['endDateFilter']) ? $_GET['endDateFilter'] : null;
        $status = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : null;

        $queryTotal = "SELECT COUNT(*) as total 
                       FROM tb_sending_documents
                       WHERE category_id = :category AND company_id = :company";

        if ($company_id) {
            $queryTotal .= " AND company_id = :company_id";
        }
        if ($category_id) {
            $queryTotal .= " AND category_id = :category_id";
        }

        // Filtros baseados em start_date e end_date
        if ($start_date && $end_date) {
            $queryTotal .= " AND expiration_date BETWEEN :start_date AND :end_date";
        } elseif ($start_date) {
            $queryTotal .= " AND expiration_date >= :start_date";
        } elseif ($end_date) {
            $queryTotal .= " AND expiration_date <= :end_date";
        }

        if ($status) {
            switch ($status) {
                case 'overdue': // Apenas dias anteriores a hoje (ignora o dia atual)
                    $queryTotal .= " AND expiration_date < CURDATE()";
                    $queryTotal .= " AND status IN (1,2)";
                    break;
        
                case 'today': // Apenas registros do dia de hoje, independentemente do horário
                    $queryTotal .= " AND expiration_date >= CURDATE()"; // Começo do dia de hoje (00:00:00)
                    $queryTotal .= " AND expiration_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Antes do começo do próximo dia (00:00:00)
                    $queryTotal .= " AND status = 1";
                    break;
        
                case 'next': // Apenas registros entre amanhã e os próximos 7 dias (não inclui hoje)
                    $queryTotal .= " AND expiration_date >= DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Começa amanhã (00:00:00)
                    $queryTotal .= " AND expiration_date < DATE_ADD(CURDATE(), INTERVAL 8 DAY)"; // Até 7 dias a partir de amanhã
                    $queryTotal .= " AND status = 1";
                    break;
        
                case 'in_day': // Apenas registros futuros (exclui o dia atual)
                    $queryTotal .= " AND expiration_date >= DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Começa amanhã (00:00:00)
                    $queryTotal .= " AND status = 1";
                    break;
        
                case 'all': // Todos os registros com status 1 ou 2, sem restrição de data
                    $queryTotal .= " AND status IN (1,2)";
                    break;
        
                case 'all_parametrized': // Últimos 7 dias a partir de hoje OU registros sem data definida
                    $queryTotal .= " AND (expiration_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR expiration_date IS NULL)";
                    break;
            }
        }

        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->bindValue(':category', $category, PDO::PARAM_INT);
        $stmtTotal->bindValue(':company', $company, PDO::PARAM_INT);

        if ($company_id) {
            $stmtTotal->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        }
        if ($category_id) {
            $stmtTotal->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        }
        if ($start_date) {
            $stmtTotal->bindValue(':start_date', $start_date, PDO::PARAM_STR);
        }
        if ($end_date) {
            $stmtTotal->bindValue(':end_date', $end_date, PDO::PARAM_STR);
        }

        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        $query = "SELECT 
                    d.id, 
                    d.name, 
                    d.reference, 
                    d.price, 
                    d.expiration_date, 
                    d.upload_date, 
                    d.document, 
                    d.is_read, 
                    d.read_in, 
                    depto.name AS depto_name, 
                    cat.name AS category_name
                FROM tb_sending_documents d
                LEFT JOIN tb_companies comp ON d.company_id = comp.id
                LEFT JOIN tb_sending_categories cat ON d.category_id = cat.id
                LEFT JOIN tb_sending_departments depto ON d.department_id = depto.id
                WHERE d.category_id = :category AND d.company_id = :company";

        if ($company_id) {
            $query .= " AND d.company_id = :company_id";
        }
        if ($category_id) {
            $query .= " AND d.category_id = :category_id";
        }

        // Filtros baseados em start_date e end_date
        if ($start_date && $end_date) {
            $query .= " AND d.expiration_date BETWEEN :start_date AND :end_date";
        } elseif ($start_date) {
            $query .= " AND d.expiration_date >= :start_date";
        } elseif ($end_date) {
            $query .= " AND d.expiration_date <= :end_date";
        }

        if ($status) {
            switch ($status) {
                case 'overdue': // Apenas dias anteriores a hoje (ignora o dia atual)
                    $query .= " AND d.expiration_date < CURDATE()";
                    $query .= " AND d.status IN (1,2)";
                    break;
        
                case 'today': // Apenas registros do dia de hoje, independentemente do horário
                    $query .= " AND d.expiration_date >= CURDATE()"; // Começo do dia de hoje (00:00:00)
                    $query .= " AND d.expiration_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Antes do começo do próximo dia (00:00:00)
                    $query .= " AND d.status = 1";
                    break;
        
                case 'next': // Apenas registros entre amanhã e os próximos 7 dias (não inclui hoje)
                    $query .= " AND d.expiration_date >= DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Começa amanhã (00:00:00)
                    $query .= " AND d.expiration_date < DATE_ADD(CURDATE(), INTERVAL 9 DAY)"; // Até 7 dias a partir de amanhã
                    $query .= " AND d.status = 1";
                    break;
        
                case 'in_day': // Apenas registros futuros (exclui o dia atual)
                    $query .= " AND d.expiration_date >= DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Começa amanhã (00:00:00)
                    $query .= " AND d.status = 1";
                    break;
        
                case 'all': // Todos os registros com status 1 ou 2, sem restrição de data
                    $query .= " AND d.status IN (1,2)";
                    break;
        
                case 'all_parametrized': // Últimos 7 dias a partir de hoje OU registros sem data definida
                    $query .= " AND (d.expiration_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR d.expiration_date IS NULL)";
                    break;
            }
        }

        if (!empty($search)) {
            $query .= " AND name LIKE :search";
        }

        $query .= " ORDER BY d.expiration_date ASC";

        if (!$export_all) {
            $query .= " LIMIT :start, :length";
        }

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':category', $category, PDO::PARAM_INT);
        $stmt->bindValue(':company', $company, PDO::PARAM_INT);

        if ($company_id) {
            $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        }
        if ($category_id) {
            $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        }
        if ($start_date) {
            $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
        }
        if ($end_date) {
            $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
        }
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);

        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reference = (!empty($row['reference'])) ? date("m/Y", strtotime($row['reference'])) : "Não informado";

            $price = (!empty($row['price'])) ? "R$ ".number_format($row['price'], 2, ',', '.') : "Não informado";

            $upload_date = (!empty($row['upload_date'])) ? date("d/m/Y H:i:s", strtotime($row['upload_date'])) : "N/C";

            $document = $row['document'] 
                ? '<a href="' . $row['document'] . '" id="markAsViewed" data-document-id="' . $row['id'] . '" class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                     <i class="mdi mdi-open-in-new fs-14 text-white"></i>
                   </a>' 
                : '<span class="text-muted">
                     <i class="mdi mdi-file-document-remove-outline fs-16"></i>
                     N/C
                   </span>';

            if ($row['is_read']) {
                $status = '<span class="badge bg-primary-subtle text-primary fw-semibold">Visualizado</span>';
            } else {
                $status = '<span class="badge bg-warning-subtle text-warning fw-semibold">Não visualizado</span>';
            }

            $read_in = (!empty($row['read_in'])) ? date("d/m/Y H:i:s", strtotime($row['read_in'])) : "--";

            $data[] = [
                'depto' => $row['depto_name'],
                'category' => $row['category_name'],
                'reference' => $reference,
                'price' => $price,
                'expiration_date' => date("d/m/Y", strtotime($row['expiration_date'])),
                'upload_date' => $upload_date,
                'document' => $document,
                'status' => $status,
                'read_in' => $read_in,
            ];
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => empty($search) ? $totalRecords : $stmt->rowCount(),
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}