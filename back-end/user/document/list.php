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

        $company_id = isset($_GET['companyFilter']) ? intval($_GET['companyFilter']) : null;
        $document_type_id = isset($_GET['documentTypeFilter']) ? intval($_GET['documentTypeFilter']) : null;
        $start_date = isset($_GET['startDateFilter']) ? $_GET['startDateFilter'] : null;
        $end_date = isset($_GET['endDateFilter']) ? $_GET['endDateFilter'] : null;

        $queryTotal = "SELECT COUNT(*) as total 
                       FROM tb_documents
                       WHERE user_id = :user_id";

        if ($company_id) {
            $queryTotal .= " AND company_id = :company_id";
        }
        if ($document_type_id) {
            $queryTotal .= " AND document_type_id = :document_type_id";
        }

        // Filtros baseados em start_date e end_date
        if ($start_date && $end_date) {
            $queryTotal .= " AND expiration_date BETWEEN :start_date AND :end_date";
        } elseif ($start_date) {
            $queryTotal .= " AND expiration_date >= :start_date";
        } elseif ($end_date) {
            $queryTotal .= " AND expiration_date <= :end_date";
        }

        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);

        if ($company_id) {
            $stmtTotal->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        }
        if ($document_type_id) {
            $stmtTotal->bindValue(':document_type_id', $document_type_id, PDO::PARAM_INT);
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
                    d.company_id, 
                    d.document_type_id, 
                    d.document, 
                    d.name, 
                    d.expiration_date, 
                    d.advance_notification, 
                    d.personalized_advance_notification, 
                    d.observation,
                    c.name AS company_name,
                    t.name AS document_type_name
                  FROM tb_documents d
                  LEFT JOIN tb_companies c ON d.company_id = c.id
                  LEFT JOIN tb_document_types t ON d.document_type_id = t.id
                  WHERE d.user_id = :user_id AND status = 1";

        if ($company_id) {
            $query .= " AND d.company_id = :company_id";
        }
        if ($document_type_id) {
            $query .= " AND d.document_type_id = :document_type_id";
        }

        // Filtros baseados em start_date e end_date
        if ($start_date && $end_date) {
            $query .= " AND d.expiration_date BETWEEN :start_date AND :end_date";
        } elseif ($start_date) {
            $query .= " AND d.expiration_date >= :start_date";
        } elseif ($end_date) {
            $query .= " AND d.expiration_date <= :end_date";
        }

        if (!empty($search)) {
            $query .= " AND name LIKE :search";
        }

        $query .= " ORDER BY d.expiration_date ASC LIMIT :start, :length";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);

        if ($company_id) {
            $stmt->bindValue(':company_id', $company_id, PDO::PARAM_INT);
        }
        if ($document_type_id) {
            $stmt->bindValue(':document_type_id', $document_type_id, PDO::PARAM_INT);
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
            $advanceNotification = empty($row['advance_notification']) ? "--" : (($row['advance_notification'] == 'personalized') ? $row['personalized_advance_notification'] . " dias" : $row['advance_notification'] . " dias");

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

            $today = new DateTime();
            $dueDate = new DateTime($row['expiration_date']);
            $interval = $today->diff($dueDate)->days;
            $isOverdue = $today > $dueDate;
            $isToday = $today->format('Y-m-d') === $dueDate->format('Y-m-d');
            $isNext = !$isOverdue && !$isToday && $interval <= 7;

            $icons = [
                'overdue' => $alertSvg,
                'today'   => $alertSvg,
                'next'    => $loaderSvg,
                'in_day'  => $checkSvg
            ];

            $texts = [
                'overdue' => '<span class="badge bg-danger-subtle text-danger fw-semibold">Vencido</span>',
                'today'   => '<span class="badge bg-warning-subtle text-warning fw-semibold">Vence hoje</span>',
                'next'    => '<span class="badge bg-warning-subtle text-warning fw-semibold">Próximo ao vencimento</span>',
                'in_day'  => '<span class="badge bg-primary-subtle text-primary fw-semibold">Em dia</span>'
            ];

            $key = $isToday ? 'today' : ($isOverdue ? 'overdue' : ($isNext ? 'next' : 'in_day'));

            $status = $texts[$key];

            $actions = '<button class="btn btn-sm bg-info-subtle fs-14 me-1 btn-renew" data-bs-toggle="tooltip" title="Informar Renovação" data-id="' . $row['id'] . '" data-name="' . $row['name'] . '">Informar Renovação</button>
                        <a href="' . INCLUDE_PATH_DASHBOARD . 'editar-documento/' . $row['id'] . '" class="btn btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Delete" data-id="' . $row['id'] . '" data-name="document">
                            <i class="mdi mdi-delete-outline fs-14 text-danger"></i>
                        </button>';

            $data[] = [
                'company' => $row['company_name'],
                'document_type' => $row['document_type_name'],
                'document' => $document,
                'expiration_date' => date("d/m/Y", strtotime($row['expiration_date'])),
                'advance_notification' => $advanceNotification,
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