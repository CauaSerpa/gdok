<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

// Função para traduzir a prioridade
function translatePriority($priority) {
    $translations = [
        'low' => 'Baixa',
        'average' => 'Média',
        'high' => 'Alta',
    ];
    return isset($translations[$priority]) ? $translations[$priority] : $priority;
}

// Verifica se o método de requisição é GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (!$conn) {
            throw new Exception("Conexão inválida com o banco de dados.");
        }

        // Obtém o ID do usuário logado da sessão
        $currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        if (!$currentUserId) {
            throw new Exception("Usuário não autenticado.");
        }

        // Obtém os parâmetros enviados pelo DataTables
        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

        // Consulta total de registros para o usuário atual
        $queryTotal = "SELECT COUNT(*) as total 
                       FROM tb_document_types 
                       WHERE user_id = :user_id";
        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // Consulta com filtro de busca, se aplicável
        $query = "SELECT dt.id, dt.name, dtc.name AS category_name, dt.priority, dt.advance_notification, dt.personalized_advance_notification
                  FROM tb_document_types dt
                  LEFT JOIN tb_document_type_categories dtc ON dt.category_id = dtc.id
                  WHERE dt.user_id = :user_id";

        if (!empty($search)) {
            $query .= " AND (dt.name LIKE :search OR dtc.name LIKE :search)";
        }

        // Aqui usamos parâmetros para o LIMIT de forma segura
        $query .= " ORDER BY dt.id ASC LIMIT :start, :length";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        // Bind dos parâmetros de LIMIT
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);

        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $advance_notification = ($row['advance_notification'] == 'personalized') ? $row['personalized_advance_notification'] . " dias" : $row['advance_notification'] . " dias";

            $actions = '<a href="' . INCLUDE_PATH_DASHBOARD . 'editar-tipo-documento/' . $row['id'] . '" class="btn btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Delete" data-id="' . $row['id'] . '" data-name="' . $row['name'] . '">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </button>';

            $data[] = [
                'name' => $row['name'],
                'category_name' => $row['category_name'],
                'priority' => translatePriority($row['priority']),
                'advance_notification' => $advance_notification,
                'actions' => $actions
            ];
        }

        // Resposta em formato JSON para o DataTables
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => empty($search) ? $totalRecords : $stmt->rowCount(),
            'data' => $data
        ]);
    } catch (Exception $e) {
        // Retorna erro em caso de falha
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    exit;
}
