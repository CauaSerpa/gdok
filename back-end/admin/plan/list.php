<?php 
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (!$conn) {
            throw new Exception("Conexão inválida com o banco de dados.");
        }

        // Obtém o ID do usuário logado (ou adapte conforme sua lógica)
        $currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        if (!$currentUserId) {
            throw new Exception("Usuário não autenticado.");
        }

        // Parâmetros do DataTables
        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

        // Consulta para obter o total de registros
        $queryTotal = "SELECT COUNT(*) as total FROM tb_plans";
        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // Consulta com filtro de busca (se aplicável)
        $query = "SELECT id, plan_name, plan_description, plan_price, billing_period FROM tb_plans";
        if (!empty($search)) {
            $query .= " AND (plan_name LIKE :search OR plan_description LIKE :search)";
        }
        $query .= " ORDER BY id ASC LIMIT :start, :length";

        $stmt = $conn->prepare($query);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Ações com botões de editar e excluir
            $actions = '<a href="' . INCLUDE_PATH_DASHBOARD . 'editar-plano/' . $row['id'] . '" class="btn btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Editar">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Excluir" data-id="' . $row['id'] . '" data-name="' . $row['plan_name'] . '">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </button>';

            $data[] = [
                'plan_name'        => $row['plan_name'],
                'plan_description' => $row['plan_description'],
                'price'            => 'R$ ' . number_format($row['plan_price'], 2, ',', '.'),
                'billing_period'   => ucfirst($row['billing_period']),
                'actions'          => $actions
            ];
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => empty($search) ? $totalRecords : $stmt->rowCount(),
            'data'            => $data
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    exit;
}