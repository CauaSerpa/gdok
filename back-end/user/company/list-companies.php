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

        // Obtém os parâmetros enviados pelo DataTables
        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

        // Consulta total de registros
        $queryTotal = "SELECT COUNT(*) as total FROM tb_companies";
        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // Consulta com filtro de busca, se aplicável
        $query = "SELECT id, name, phone, email, responsible, document, uf, cidade, created_at 
                  FROM tb_companies";

        if (!empty($search)) {
            $query .= " WHERE name LIKE :search OR email LIKE :search OR uf LIKE :search OR cidade LIKE :search";
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
            $id = '<a href="' . INCLUDE_PATH_DASHBOARD . 'editar-empresa/' . $row['id'] . '" class="text-muted">#' . $row['id'] . '</a>';
            $created_at = date('d/m/Y H:i:s', strtotime($row['created_at']));
            $actions = '<a href="editar-empresa/' . $row['id'] . '" class="btn btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Delete" data-id="' . $row['id'] . '" data-name="' . $row['name'] . '">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </button>';

            $data[] = [
                'id' => $id,
                'name' => $row['name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'responsible' => $row['responsible'],
                'document' => $row['document'],
                'uf' => $row['uf'],
                'cidade' => $row['cidade'],
                'created_at' => $created_at,
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