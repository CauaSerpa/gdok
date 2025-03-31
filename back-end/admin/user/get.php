<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];

    // Consulta os dados do usuario
    $stmtUser = $conn->prepare("SELECT * FROM tb_users WHERE id = ?");
    $stmtUser->execute([$id]);

    if ($stmtUser->rowCount() > 0) {
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // Consulta os dados do escritório relacionado ao usuario
        $stmtAddress = $conn->prepare("SELECT * FROM tb_address WHERE user_id = ?");
        $stmtAddress->execute([$user['id']]);

        // Se não encontrar, utiliza valores vazios
        $address = $stmtAddress->rowCount() > 0 ? $stmtAddress->fetch(PDO::FETCH_ASSOC) : [];

        // Consulta os dados do escritório relacionado ao usuario
        $stmtOffice = $conn->prepare("
            SELECT o.*
            FROM tb_office_users ou
            LEFT JOIN tb_offices o ON o.id = ou.office_id
            WHERE ou.user_id = ?
        ");
        $stmtOffice->execute([$user['id']]);

        // Se não encontrar, utiliza valores vazios
        $office = $stmtOffice->rowCount() > 0 ? $stmtOffice->fetch(PDO::FETCH_ASSOC) : [];

        if ($office) {
            // Consulta os dados do escritório relacionado ao usuario
            $stmtOfficeAddress = $conn->prepare("SELECT * FROM tb_office_addresses WHERE office_id = ?");
            $stmtOfficeAddress->execute([$office['id']]);
        }

        // Se não encontrar, utiliza valores vazios
        $officeAddress = isset($stmtOfficeAddress) && $stmtOfficeAddress->rowCount() > 0 ? $stmtOfficeAddress->fetch(PDO::FETCH_ASSOC) : [];

        // Consulta as empresas cadastradas do usuário
        $stmtCompanies = $conn->prepare("
            SELECT c.*, COUNT(d.id) AS document_count 
            FROM tb_companies c 
            LEFT JOIN tb_documents d 
            ON c.id = d.company_id 
            WHERE c.user_id = ?
        ");
        $stmtCompanies->execute([$user['id']]);
        $companies = $stmtCompanies->rowCount() > 0 ? $stmtCompanies->fetchAll(PDO::FETCH_ASSOC) : [];

        $fullname = $user['firstname'] . " " . $user['lastname'];

        // Monta o array de resposta com os dados do usuário, escritório e empresas
        $responseData = [
            "user" => [
                "name"           => $fullname ?: "-",
                "email"          => !empty($user['email']) ? $user['email'] : "-",
                "phone"          => !empty($user['phone']) ? $user['phone'] : "-",
                "dateCreate"     => !empty($user['created_at']) ? date("d/m/Y", strtotime($user['created_at'])) : "-",
                "cep"            => !empty($address['postal_code']) ? $address['postal_code'] : "-",
                "address"        => !empty($address['street']) ? $address['street'] : "-",
                "addressNumber"  => !empty($address['number']) ? $address['number'] : "-",
                "complement"     => !empty($address['complement']) ? $address['complement'] : "-",
                "city"           => !empty($address['city']) ? $address['city'] : "-",
                "state"          => !empty($address['state']) ? $address['state'] : "-"
            ],
            "office" => [
                "name"           => !empty($office['name']) ? $office['name'] : "-",
                "email"          => !empty($office['email']) ? $office['email'] : "-",
                "phone"          => !empty($office['phone']) ? $office['phone'] : "-",
                "cep"            => !empty($officeAddress['cep']) ? $officeAddress['cep'] : "-",
                "address"        => !empty($officeAddress['address']) ? $officeAddress['address'] : "-",
                "addressNumber"  => (!empty($officeAddress['number']) && $officeAddress['number'] != 0) ? $officeAddress['number'] : "-",
                "complement"     => !empty($officeAddress['complement']) ? $officeAddress['complement'] : "-",
                "city"           => !empty($officeAddress['city']) ? $officeAddress['city'] : "-",
                "state"          => !empty($officeAddress['state']) ? $officeAddress['state'] : "-"
            ],
            "companies" => [] // Inicia com array vazio
        ];

        // Formata os dados das empresas para a resposta
        if (!empty($companies)) {
            foreach ($companies as $company) {
                $responseData["companies"][] = [
                    "name"             => !empty($company['name']) ? $company['name'] : "-",
                    "responsible_name" => !empty($company['responsible_name']) ? $company['responsible_name'] : "-",
                    "document"         => !empty($company['document']) ? $company['document'] : "-",
                    "document"         => !empty($company['document']) ? $company['document'] : "-",
                    "documents_count"  => isset($company['documents_count']) ? $company['documents_count'] : "-",
                    "date_create"      => !empty($company['created_at']) ? date("d/m/Y", strtotime($company['created_at'])) : "-"
                ];
            }
        }

        echo json_encode([
            "status" => "success",
            "data"   => $responseData
        ]);
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Usuário não encontrado."
        ]);
    }
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Método de requisição inválido."
    ]);
}