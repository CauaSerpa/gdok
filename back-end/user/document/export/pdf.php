<?php
    session_start();
    include('./../../../../config.php');
    require_once './../../../../vendor/autoload.php';

    header('Content-Type: application/json');

    // Função para gerar o PDF
    function generatePDF($data) {
        // Gera o PDF com TCPDF
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('GDok - Exportação de Documentos. ' . date('d/m/Y H:i'));
        $pdf->SetHeaderData('', 0, 'GDok - Relatório de documentos gerenciados', 'Gerado em: ' . date('d/m/Y H:i'));
        $pdf->Ln(5);

        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 10);

        $pdf->Ln(5);

        $table = '<table cellpadding="6" style="width: 100%;">
                        <thead>
                            <tr style="background-color:#2D4154; color: #fff; font-weight: bold; text-align: left;">
                                <th style="padding: 8px;">
                                    <div style="vertical-align: bottom; text-align: left;">
                                        Empresa
                                    </div>
                                </th>
                                <th style="padding: 8px; text-align: left;">
                                    Tipo de Documento
                                </th>
                                <th style="padding: 8px;">
                                    <div style="vertical-align: bottom; text-align: left;">
                                        Documento
                                    </div>
                                </th>
                                <th style="padding: 8px; text-align: right;">
                                    Data de Expiração
                                </th>
                                <th style="padding: 8px; text-align: right;">
                                    <div style="vertical-align: bottom;">
                                        Notificação
                                    </div>
                                </th>
                                <th style="padding: 8px; text-align: right;">
                                    <div style="vertical-align: bottom;">
                                        Status
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($data as $row) {
            $document = !empty($row['document']) ? "C/D" : "N/C";

            $table .= '<tr>
                            <td style="padding: 8px; text-align: left;">' . htmlspecialchars($row['company']) . '</td>
                            <td style="padding: 8px; text-align: left;">' . htmlspecialchars($row['document_type']) . '</td>
                            <td style="padding: 8px; text-align: left;">' . htmlspecialchars($document) . '</td>
                            <td style="padding: 8px; text-align: right;">' . htmlspecialchars($row['expiration_date']) . '</td>
                            <td style="padding: 8px; text-align: right;">' . htmlspecialchars($row['advance_notification']) . '</td>
                            <td style="padding: 8px; text-align: right;">' . htmlspecialchars($row['status']) . '</td>
                        </tr>';
        }

        $table .= '</tbody></table>';

        // Escreve o conteúdo da tabela no PDF
        $pdf->writeHTML($table, true, false, true, false, '');

        // Adiciona a legenda após a tabela
        $legenda = '<p style="font-size: 8pt; margin-top: 10px;">
                        <strong>Legenda:</strong><br>
                        C/D: CONSTA DOCUMENTO<br>
                        N/C: NADA CONSTA
                    </p>';

        $pdf->writeHTML($legenda, true, false, true, false, '');

        // Saída do PDF para download
        $pdf->Output('documentos_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
    }

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

            $company_id = isset($_GET['companyFilter']) ? intval($_GET['companyFilter']) : null;
            $document_type_id = isset($_GET['documentTypeFilter']) ? intval($_GET['documentTypeFilter']) : null;
            $start_date = isset($_GET['startDateFilter']) ? $_GET['startDateFilter'] : null;
            $end_date = isset($_GET['endDateFilter']) ? $_GET['endDateFilter'] : null;
            $status = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : null;

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
                    WHERE d.user_id = :user_id";

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

            if ($status) {
                switch ($status) {
                    case 'overdue': // Apenas dias anteriores a hoje (ignora o dia atual)
                        $query .= " AND d.expiration_date < CURDATE()";
                        $query .= " AND d.status IN (1,2)";
                        break;

                    case 'today': // Apenas registros do dia de hoje, independentemente do horário
                        $query .= " AND d.expiration_date >= CURDATE()"; // Começo do dia de hoje (00:00:00)
                        $query .= " AND d.expiration_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Antes do começo do próximo dia (00:00:00)
                        $query .= " AND d.status = 1";
                        break;

                    case 'next': // Apenas registros entre amanhã e os próximos 7 dias (não inclui hoje)
                        $query .= " AND d.expiration_date >= DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Começa amanhã (00:00:00)
                        $query .= " AND d.expiration_date < DATE_ADD(CURDATE(), INTERVAL 9 DAY)"; // Até 7 dias a partir de amanhã
                        $query .= " AND d.status = 1";
                        break;

                    case 'in_day': // Apenas registros futuros (exclui o dia atual)
                        $query .= " AND d.expiration_date >= DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; // Começa amanhã (00:00:00)
                        $query .= " AND d.status = 1";
                        break;

                    case 'all': // Todos os registros com status 1 ou 2, sem restrição de data
                        $query .= " AND d.status IN (1,2)";
                        break;

                    case 'all_parametrized': // Últimos 7 dias a partir de hoje OU registros sem data definida
                        $query .= " AND (d.expiration_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR d.expiration_date IS NULL)";
                        break;
                }
            }

            if (!empty($search)) {
                $query .= " AND name LIKE :search";
            }

            $query .= " ORDER BY d.expiration_date ASC";

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
                    'overdue' => 'Vencido',
                    'today'   => 'Vence hoje',
                    'next'    => 'A vencer',
                    'in_day'  => 'Em dia'
                ];

                $key = $isToday ? 'today' : ($isOverdue ? 'overdue' : ($isNext ? 'next' : 'in_day'));

                $status = $texts[$key];

                $data[] = [
                    'company' => $row['company_name'],
                    'document_type' => $row['document_type_name'],
                    'document' => $row['document'],
                    'expiration_date' => date("d/m/Y", strtotime($row['expiration_date'])),
                    'advance_notification' => $advanceNotification,
                    'status' => $status
                ];
            }

            // Gera o arquivo DOCX com os dados
            $fileContent = generatePDF($data);
            exit;

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }