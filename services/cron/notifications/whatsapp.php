<?php
// Incluindo o arquivo de configuração para o banco de dados
include('./../../../config.php');

try {
    // Nome do sistema
    $systemName = $project['name'] ?? 'Sistema de Gestão'; // Caso $project['name'] não esteja definido

    // Consulta ao banco de dados para buscar os documentos relevantes
    $sql = "
        SELECT 
            u.id AS user_id,
            u.phone AS user_phone,
            u.firstname AS user_name,
            c.name AS company_name,
            c.phone AS company_phone,
            c.notify_phone,
            d.id AS document_id,
            d.name AS document_name,
            dt.name AS document_type_name,
            d.expiration_date,
            DATE_FORMAT(d.expiration_date, '%d/%m/%Y') AS expiration_date_formatted
        FROM 
            tb_documents d
        INNER JOIN 
            tb_document_types dt ON dt.id = d.document_type_id
        INNER JOIN 
            tb_users u ON u.id = d.user_id
        INNER JOIN
            tb_companies c ON c.id = d.company_id
        WHERE 
            (
                -- Notifica se a data de expiração for hoje (independente de já ter sido notificado)
                d.expiration_date = CURDATE()
                OR 
                -- Notifica uma vez se estiver no período de notificação e ainda não foi notificado
                (DATE_SUB(d.expiration_date, INTERVAL 
                    CASE
                        WHEN d.personalized_advance_notification IS NOT NULL THEN d.personalized_advance_notification
                        ELSE d.advance_notification
                    END DAY) <= CURDATE()
                AND NOT EXISTS (
                    SELECT 1 FROM tb_notifications n
                    WHERE n.user_id = u.id
                    AND n.related_id = d.id
                    AND n.notification_type = 'document_expiration_whatsapp'
                ))
            )
        ORDER BY 
            d.expiration_date ASC; -- Agora o ORDER BY funciona corretamente
    ";

    // Executar a consulta
    $stmt = $conn->query($sql);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Verificar se há documentos para notificar
    if (!empty($documents)) {
        // Agrupar documentos por usuário
        $userDocuments = [];
        foreach ($documents as $document) {
            $userDocuments[$document['user_id']]['user_phone'] = $document['user_phone'];
            $userDocuments[$document['user_id']]['user_name'] = $document['user_name'];
            $userDocuments[$document['user_id']]['documents'][] = $document;
        }

        // Para cada usuário, enviar o WhatsApp com a lista de documentos
        foreach ($userDocuments as $userId => $userData) {
            $userPhone = '+55' . preg_replace('/[()\-\s]/', '', $userData['user_phone']); // Remover caracteres especiais do número de telefone
            $userName = $userData['user_name'];
            $documentsList = $userData['documents'];

            // Montar a lista de documentos
            $documentsMessage = "Olá, $userName! 👋\n\n" .
                "Este é um aviso do *$systemName* para informá-lo que os documentos abaixo estão vencendo:\n\n";

            foreach ($documentsList as $document) {
                $documentsMessage .= 
                    "Empresa: *" . htmlspecialchars_decode(htmlspecialchars($document['company_name'], ENT_QUOTES, 'UTF-8')) . "*\n" .
                    "Documento: *" . htmlspecialchars($document['document_type_name'], ENT_QUOTES, 'UTF-8') . "*\n" .
                    "Vencimento: *" . htmlspecialchars($document['expiration_date_formatted'], ENT_QUOTES, 'UTF-8') . "*\n\n";
            }

            $documentsMessage .= "Obrigado por usar o *$systemName*! 😊";

            $postData = json_encode([
                "number" => $userPhone,
                "text" => $documentsMessage
            ]);

            $ch = curl_init($config['evolution_url'] . "message/sendText/" . $config['evolution_instance']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "apikey: " . $config['evolution_apikey']
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $response = curl_exec($ch);
            curl_close($ch);

            foreach ($documentsList as $document) {

                // Se a empresa tem o campo notify_phone definido, insira o código específico.
                if ($document['notify_phone']) {

                    $companyPhone = '+55' . preg_replace('/[()\-\s]/', '', $document['company_phone']); // Remover caracteres especiais do número de telefone

                    // Montar a lista de documentos
                    $documentsMessage = "Olá, $userName! 👋\n\n" .
                        "Este é um aviso do *$systemName* para informá-lo que os documentos abaixo estão vencendo:\n\n";

                    $documentsMessage .= 
                        "Documento: *" . htmlspecialchars($document['document_type_name'], ENT_QUOTES, 'UTF-8') . "*\n" .
                        "Vencimento: *" . htmlspecialchars($document['expiration_date_formatted'], ENT_QUOTES, 'UTF-8') . "*\n\n";

                    $documentsMessage .= "Obrigado por usar o *$systemName*! 😊";

                    $postData = json_encode([
                        "number" => $companyPhone,
                        "text" => $documentsMessage
                    ]);

                    $ch = curl_init($config['evolution_url'] . "message/sendText/" . $config['evolution_instance']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Content-Type: application/json",
                        "apikey: " . $config['evolution_apikey']
                    ]);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

                    $response = curl_exec($ch);
                    curl_close($ch);

                }

            }

            // Marcar como notificado no banco de dados
            $insertSql = "INSERT INTO tb_notifications (user_id, related_id, title, message, notification_type, created_at)
                          VALUES (:user_id, :related_id, :title, :message, :notification_type, NOW())";
            $stmtInsert = $conn->prepare($insertSql);

            foreach ($documentsList as $document) {
                $stmtInsert->execute([
                    ':user_id' => $userId,
                    ':related_id' => $document['document_id'],
                    ':title' => 'Aviso: Documento Próximo ao Vencimento',
                    ':message' => 'O documento "' . $document['document_name'] . '" está prestes a vencer.',
                    ':notification_type' => 'document_expiration_whatsapp',
                ]);
            }
            $stmtInsert->closeCursor();
        }
    } else {
        echo "Nenhuma notificação a ser enviada no momento.\n";
    }

    // Salvar momento que foi feita a execucao do cron
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process) VALUES ('success', 'send_document_expiration_email')");
    $stmt->execute();
} catch (PDOException $e) {
    echo "Erro no banco de dados: " . $e->getMessage();

    // Em caso de erro, registra no log
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process, error_message) VALUES ('error', 'send_document_expiration_whatsapp', ?)");
    $stmt->execute([$e->getMessage()]);
} catch (Exception $e) {
    echo "Erro ao enviar WhatsApp: " . $e->getMessage();

    // Em caso de erro, registra no log
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process, error_message) VALUES ('error', 'send_document_expiration_whatsapp', ?)");
    $stmt->execute([$e->getMessage()]);
}