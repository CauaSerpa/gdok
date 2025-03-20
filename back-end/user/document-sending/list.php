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

        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
        $export_all = isset($_GET['export_all']) ? $_GET['export_all'] : false;

        // Filtros enviados pelo DataTable
        $company_id = isset($_GET['companyFilter']) ? intval($_GET['companyFilter']) : null;
        $department_id = isset($_GET['departmentFilter']) ? intval($_GET['departmentFilter']) : null;
        $category_id = isset($_GET['categoryFilter']) ? intval($_GET['categoryFilter']) : null;
        $start_date = isset($_GET['startDateFilter']) ? $_GET['startDateFilter'] : null;
        $end_date = isset($_GET['endDateFilter']) ? $_GET['endDateFilter'] : null;

        $start_date_reference = isset($_GET['startDateReferenceFilter']) ? $_GET['startDateReferenceFilter'] : null;
        if ($start_date_reference) {
            $date = DateTime::createFromFormat("m/Y", $start_date_reference);
            $start_date_reference = $date->format("Y-m");
        }

        $end_date_reference = isset($_GET['endDateReferenceFilter']) ? $_GET['endDateReferenceFilter'] : null;
        if ($end_date_reference) {
            $date = DateTime::createFromFormat("m/Y", $end_date_reference);
            $end_date_reference = $date->format("Y-m");
        }

        $minPrice = isset($_GET['minPriceFilter']) ? trim($_GET['minPriceFilter']) : null;
        if ($minPrice) {
            $minPrice = str_replace('.', '', $minPrice);
            $minPrice = str_replace(',', '.', $minPrice);
        }

        $maxPrice = isset($_GET['maxPriceFilter']) ? trim($_GET['maxPriceFilter']) : null;
        if ($maxPrice) {
            $maxPrice = str_replace('.', '', $maxPrice);
            $maxPrice = str_replace(',', '.', $maxPrice);
        }

        // Query total
        $queryTotal = "SELECT COUNT(*) as total 
                       FROM tb_sending_documents
                       WHERE user_id = :user_id";

        if ($company_id) {
            $queryTotal .= " AND company_id = :company_id";
        }
        if ($department_id) {
            $queryTotal .= " AND department_id = :department_id";
        }
        if ($category_id) {
            $queryTotal .= " AND category_id = :category_id";
        }
        if ($start_date && $end_date) {
            $queryTotal .= " AND expiration_date BETWEEN :start_date AND :end_date";
        } elseif ($start_date) {
            $queryTotal .= " AND expiration_date >= :start_date";
        } elseif ($end_date) {
            $queryTotal .= " AND expiration_date <= :end_date";
        }
        if ($start_date_reference && $end_date_reference) {
            $queryTotal .= " AND reference BETWEEN :start_date_reference AND :end_date_reference";
        } elseif ($start_date_reference) {
            $queryTotal .= " AND reference >= :start_date_reference";
        } elseif ($end_date_reference) {
            $queryTotal .= " AND reference <= :end_date_reference";
        }
        if ($minPrice) {
            $queryTotal .= " AND price >= :minPrice";
        }
        if ($maxPrice) {
            $queryTotal .= " AND price <= :maxPrice";
        }
        if (!empty($search)) {
            $queryTotal .= " AND name LIKE :search";
        }

        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
        if ($company_id) {
            $stmtTotal->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        }
        if ($department_id) {
            $stmtTotal->bindValue(':department_id', $department_id, PDO::PARAM_INT);
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
        if ($start_date_reference) {
            $stmtTotal->bindValue(':start_date_reference', $start_date_reference, PDO::PARAM_STR);
        }
        if ($end_date_reference) {
            $stmtTotal->bindValue(':end_date_reference', $end_date_reference, PDO::PARAM_STR);
        }
        if ($minPrice) {
            $stmtTotal->bindValue(':minPrice', $minPrice, PDO::PARAM_STR);
        }
        if ($maxPrice) {
            $stmtTotal->bindValue(':maxPrice', $maxPrice, PDO::PARAM_STR);
        }
        if (!empty($search)) {
            $stmtTotal->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // Query principal
        $query = "SELECT 
                    d.id, 
                    d.company_id, 
                    d.category_id, 
                    d.department_id, 
                    d.document, 
                    d.name, 
                    d.reference, 
                    d.price, 
                    d.expiration_date, 
                    d.observation,
                    d.is_read, 
                    d.read_in,
                    comp.name AS company_name,
                    cat.name AS category_name,
                    depto.name AS department_name,
                    d.price
                FROM tb_sending_documents d
                LEFT JOIN tb_companies comp ON d.company_id = comp.id
                LEFT JOIN tb_sending_categories cat ON d.category_id = cat.id
                LEFT JOIN tb_sending_departments depto ON d.department_id = depto.id
                WHERE d.user_id = :user_id";

        if ($company_id) {
            $query .= " AND d.company_id = :company_id";
        }
        if ($department_id) {
            $query .= " AND d.department_id = :department_id";
        }
        if ($category_id) {
            $query .= " AND d.category_id = :category_id";
        }
        if ($start_date && $end_date) {
            $query .= " AND expiration_date BETWEEN :start_date AND :end_date";
        } elseif ($start_date) {
            $query .= " AND expiration_date >= :start_date";
        } elseif ($end_date) {
            $query .= " AND expiration_date <= :end_date";
        }
        if ($start_date_reference && $end_date_reference) {
            $query .= " AND reference BETWEEN :start_date_reference AND :end_date_reference";
        } elseif ($start_date_reference) {
            $query .= " AND reference >= :start_date_reference";
        } elseif ($end_date_reference) {
            $query .= " AND reference <= :end_date_reference";
        }
        if ($minPrice) {
            $query .= " AND d.price >= :minPrice";
        }
        if ($maxPrice) {
            $query .= " AND d.price <= :maxPrice";
        }
        if (!empty($search)) {
            $query .= " AND d.name LIKE :search";
        }

        $query .= " ORDER BY d.expiration_date ASC";

        if (!$export_all) {
            $query .= " LIMIT :start, :length";
        }

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
        if ($company_id) {
            $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        }
        if ($department_id) {
            $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
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
        if ($start_date_reference) {
            $stmt->bindValue(':start_date_reference', $start_date_reference, PDO::PARAM_STR);
        }
        if ($end_date_reference) {
            $stmt->bindValue(':end_date_reference', $end_date_reference, PDO::PARAM_STR);
        }
        if ($minPrice) {
            $stmt->bindValue(':minPrice', $minPrice, PDO::PARAM_STR);
        }
        if ($maxPrice) {
            $stmt->bindValue(':maxPrice', $maxPrice, PDO::PARAM_STR);
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

            $document = $row['document'] 
                ? '<a href="' . $row['document'] . '" class="btn btn-sm btn-primary" target="_blank" title="Download">
                     <i class="mdi mdi-download fs-14 text-white"></i>
                   </a>' 
                : '<span class="text-muted">
                     <i class="mdi mdi-file-document-remove-outline fs-16"></i>
                     N/C
                   </span>';

            $alertSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle text-warning me-2" style="height: 16px; width: 16px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
            $loaderSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-loader text-warning me-2" style="height: 16px; width: 16px;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>';
            $checkSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle text-primary me-2" style="height: 16px; width: 16px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';

            // Definir o status conforme alguma lógica, se necessário (aqui omitimos status personalizado)
            if ($row['is_read']) {
                $read_in = date("d/m/Y H:i:s", strtotime($row['read_in']));

                $status = '
                    <span class="badge bg-primary-subtle text-primary fw-semibold" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        data-bs-title="Data de visualização: ' . $read_in . '" 
                        title="Data de visualização: ' . $read_in . '">
                        Visualizado
                    </span>';
            } else {
                $status = '<span class="badge bg-warning-subtle text-warning fw-semibold">Não visualizado</span>';
            }

            $actions = '<a href="' . INCLUDE_PATH_DASHBOARD . 'editar-documento-envio/' . $row['id'] . '" class="btn btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-file-document-edit-outline fs-14 text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Delete" data-id="' . $row['id'] . '" data-name="document">
                            <i class="mdi mdi-file-document-remove-outline fs-14 text-danger"></i>
                        </button>';

            $data[] = [
                'company' => $row['company_name'],
                'category' => $row['category_name'],
                'department' => $row['department_name'],
                'document' => $document,
                'reference' => $reference,
                'price' => $price,
                'expiration_date' => date("d/m/Y", strtotime($row['expiration_date'])),
                'status' => $status,
                'actions' => $actions
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