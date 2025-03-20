<?php
    // Incluindo o arquivo de configuração para o banco de dados
    include('./../../../config.php');

    try {
        // Código SQL para inserir notificações
        $sql = "
            INSERT INTO tb_notifications (user_id, title, message, notification_type, related_id, created_at)
            SELECT 
                u.id AS user_id,
                'Aviso: Documento Próximo ao Vencimento' AS title,
                CONCAT(
                    'O documento \"', dt.name, '\" está próximo do vencimento. Data de vencimento: ',
                    DATE_FORMAT(d.expiration_date, '%d/%m/%Y'), '.'
                ) AS message,
                'document_expiration_system' AS notification_type,
                d.id AS related_id,
                NOW() AS created_at
            FROM 
                tb_documents d
            INNER JOIN 
                tb_users u ON u.id = d.user_id
            INNER JOIN 
                tb_document_types dt ON dt.id = d.document_type_id
            WHERE 
                (
                    DATE_SUB(
                        d.expiration_date, 
                        INTERVAL 
                            CASE
                                WHEN d.personalized_advance_notification IS NOT NULL THEN d.personalized_advance_notification
                                ELSE d.advance_notification
                            END DAY
                    ) <= CURDATE()
                    OR d.expiration_date < CURDATE()
                )
                AND NOT EXISTS (
                    SELECT 1
                    FROM tb_notifications n
                    WHERE n.user_id = u.id
                    AND n.related_id = d.id
                    AND n.notification_type = 'document_expiration_system'
                );
        ";

        // Executar a query e capturar quantas linhas foram inseridas
        $rowsInserted = $conn->exec($sql);

        if ($rowsInserted > 0) {
            echo "Notificações inseridas com sucesso! Total: $rowsInserted notificações adicionadas.";
        } else {
            echo "Nenhuma notificação foi inserida, pois já existem notificações para os documentos em questão.";
        }

        // Salvar momento que foi feita a execucao do cron
        $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process) VALUES ('success', 'send_document_expiration_email')");
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
?>