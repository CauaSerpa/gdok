<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

// Verifica se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];

    $stmt = $conn->prepare("
        SELECT d.*, c.name AS company_name, dt.name AS document_type_name
        FROM tb_documents d
        LEFT JOIN tb_companies c ON c.id = d.company_id
        LEFT JOIN tb_document_types dt ON dt.id = d.document_type_id
        WHERE d.id = ?
    ");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        $advanceNotification = empty($document['advance_notification']) ? "-" : (($document['advance_notification'] == 'personalized') ? $document['personalized_advance_notification'] . " dias" : $document['advance_notification'] . " dias");

        $alertSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle text-warning me-2" style="height: 16px; width: 16px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        $loaderSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-loader text-warning me-2" style="height: 16px; width: 16px;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>';
        $checkSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle text-primary me-2" style="height: 16px; width: 16px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';

        $today = new DateTime();
        $dueDate = new DateTime($document['expiration_date']);
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
            'next'    => '<span class="badge bg-warning-subtle text-warning fw-semibold">A vencer</span>',
            'in_day'  => '<span class="badge bg-primary-subtle text-primary fw-semibold">Em dia</span>'
        ];

        $key = $isToday ? 'today' : ($isOverdue ? 'overdue' : ($isNext ? 'next' : 'in_day'));

        $status = $texts[$key];


        $documentFile = [
            "path" => $document['document'],
            "file" => basename($document['document'])
        ];

        $documentData = [
            "name" => !empty($document['name']) ? $document['name'] : "-",
            "document" => $documentFile,
            "company" => $document['company_name'],
            "document_type" => $document['document_type_name'],
            'expiration_date' => date("d/m/Y", strtotime($document['expiration_date'])),
            'advance_notification' => $advanceNotification,
            'status' => $status,
            'observation' => !empty($document['observation']) ? $document['observation'] : "-",
        ];

        echo json_encode([
            'status' => 'success',
            'data' => $documentData
        ]);
    } else {
        return false;
    }
}