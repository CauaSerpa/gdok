<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

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

        $queryTotal = "SELECT COUNT(*) as total FROM tb_users WHERE role = 1";
        
        $where = " AND 1=1";
        if (!empty($search)) {
            $where .= " AND (u.name LIKE :search OR u.email LIKE :search)";
        }
        
        $queryTotal .= $where;
        $stmtTotal = $conn->prepare($queryTotal);
        
        if (!empty($search)) {
            $stmtTotal->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();
        
        $query = "
            SELECT 
                u.id, 
                u.firstname, 
                u.lastname, 
                o.name AS office_name, 
                u.email, 
                u.document, 
                u.phone, 
                u.active_status, 
                u.created_at 
            FROM tb_users u 
            LEFT JOIN tb_office_users ou ON ou.user_id = u.id
            LEFT JOIN tb_offices o ON o.id = ou.office_id
            WHERE u.role = 1" . $where . " 
            ORDER BY created_at 
            DESC LIMIT :start, :length
        ";

        $stmt = $conn->prepare($query);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $name = $row['firstname'] . " " . $row['lastname'];

            $status = $row['active_status'] ? 'Ativo' : 'Inativo';

            $actions = '<button type="button" class="btn btn-sm bg-primary-subtle fs-14 me-1 btn-view" data-id="' . $row['id'] . '" data-bs-toggle="tooltip" title="Visualizar Usuário">
                            <i class="mdi mdi-eye-outline fs-14 text-primary"></i>
                        </button>';

            $data[] = [
                'id'            => $row['id'],
                'name'          => $name,
                'office'        => $row['office_name'] ?? "--",
                'email'         => $row['email'],
                'document'      => $row['document'] ?? "--",
                'phone'         => $row['phone'] ?? "--",
                'status'        => $status,
                'created_at'    => $row['created_at'],
                'actions'       => $actions,
            ];
        }
        
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método não permitido.']);
}