<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        if (!$conn) {
            throw new Exception("Conexão inválida com o banco de dados.");
        }

        // Para este exemplo, listamos todos os cupons (pode ser adaptado para filtrar por usuário, se necessário)
        $draw   = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
        $start  = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

        // Consulta total de registros
        $queryTotal = "SELECT COUNT(*) as total FROM tb_coupons";
        $stmtTotal = $conn->prepare($queryTotal);
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // Consulta com filtro de busca, se aplicável
        $query = "SELECT id, name, validity, discount_type, discount_value, code FROM tb_coupons";
        if (!empty($search)) {
            $query .= " WHERE name LIKE :search OR code LIKE :search";
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
            // Formata o desconto conforme o tipo: preço fixo ou porcentagem
            if ($row['discount_type'] === 'fixed') {
                $discount = 'R$ ' . number_format($row['discount_value'], 2, ',', '.');
            } else {
                $discount = $row['discount_value'] . '%';
            }
            $actions = '<a href="' . INCLUDE_PATH_DASHBOARD . 'editar-cupom/' . $row['id'] . '" class="btn btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Editar">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                        <button type="button" class="btn btn-sm bg-danger-subtle btn-delete" data-bs-toggle="tooltip" title="Excluir" data-id="' . $row['id'] . '" data-name="' . $row['name'] . '">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </button>';

            $data[] = [
                'id'       => $row['id'],
                'name'     => $row['name'],
                'validity' => date("d/m/Y", strtotime($row['validity'])),
                'discount' => $discount,
                'code'     => $row['code'],
                'actions'  => $actions
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