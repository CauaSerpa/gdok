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

        // Filtros adicionais
        $companyFilter = isset($_GET['companyFilter']) ? $_GET['companyFilter'] : '';
        $responsibleFilter = isset($_GET['responsibleFilter']) ? $_GET['responsibleFilter'] : '';
        $documentFilter = isset($_GET['documentFilter']) ? $_GET['documentFilter'] : '';

        // Consulta total de registros para o usuário atual
        $queryTotal = "SELECT COUNT(*) as total FROM tb_companies WHERE user_id = :user_id";
        
        // Adiciona filtros à consulta total
        if (!empty($companyFilter)) {
            $queryTotal .= " AND name LIKE :companyFilter";
        }
        if (!empty($responsibleFilter)) {
            $queryTotal .= " AND responsible LIKE :responsibleFilter";
        }
        if (!empty($documentFilter)) {
            $queryTotal .= " AND document LIKE :documentFilter";
        }

        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);

        // Vincula os filtros, caso existam
        if (!empty($companyFilter)) {
            $stmtTotal->bindValue(':companyFilter', "%$companyFilter%", PDO::PARAM_STR);
        }
        if (!empty($responsibleFilter)) {
            $stmtTotal->bindValue(':responsibleFilter', "%$responsibleFilter%", PDO::PARAM_STR);
        }
        if (!empty($documentFilter)) {
            $stmtTotal->bindValue(':documentFilter', "%$documentFilter%", PDO::PARAM_STR);
        }
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // Consulta com filtro de busca, se aplicável
        $query = "SELECT id, name, phone, email, responsible, document, uf, cidade, created_at 
                  FROM tb_companies 
                  WHERE user_id = :user_id";

        // Filtros adicionais
        if (!empty($companyFilter)) {
            $query .= " AND name LIKE :companyFilter";
        }
        if (!empty($responsibleFilter)) {
            $query .= " AND responsible LIKE :responsibleFilter";
        }
        if (!empty($documentFilter)) {
            $query .= " AND document LIKE :documentFilter";
        }

        if (!empty($search)) {
            $query .= " AND (name LIKE :search OR email LIKE :search OR uf LIKE :search OR cidade LIKE :search)";
        }

        $query .= " ORDER BY name ASC LIMIT :start, :length";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);

        // Vincula os filtros, caso existam
        if (!empty($companyFilter)) {
            $stmt->bindValue(':companyFilter', "%$companyFilter%", PDO::PARAM_STR);
        }
        if (!empty($responsibleFilter)) {
            $stmt->bindValue(':responsibleFilter', "%$responsibleFilter%", PDO::PARAM_STR);
        }
        if (!empty($documentFilter)) {
            $stmt->bindValue(':documentFilter', "%$documentFilter%", PDO::PARAM_STR);
        }

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actions = '<a href="editar-empresa/' . $row['id'] . '" class="btn btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Delete" data-id="' . $row['id'] . '" data-name="' . $row['name'] . '">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </button>';

            $data[] = [
                'name' => $row['name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'responsible' => $row['responsible'],
                'document' => $row['document'],
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
?>