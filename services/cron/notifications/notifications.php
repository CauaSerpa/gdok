<?php
    // Configuração do banco de dados
    $host = 'auth-db1577.hstgr.io';
    $dbname = 'u893992407_gdok';
    $username = 'u893992407_gdok';
    $password = 'Banc@123@';

    try {
        // Conexão com o banco de dados
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Código SQL para inserir notificações
        $sql = "
            INSERT INTO tb_notifications (user_id, title, message, notification_type, related_id, created_at)
            SELECT 
                u.id AS user_id,
                'Aviso: Documento Próximo ao Vencimento' AS title,
                CONCAT(
                    'O documento \"', d.name, '\" está próximo do vencimento. Data de vencimento: ',
                    DATE_FORMAT(d.expiration_date, '%d/%m/%Y'), '.'
                ) AS message,
                'document_expiration' AS notification_type,
                d.id AS related_id,
                NOW() AS created_at
            FROM 
                tb_documents d
            INNER JOIN 
                tb_users u ON u.id = d.user_id
            WHERE 
                DATE_SUB(d.expiration_date, INTERVAL 
                    CASE
                        WHEN d.personalized_advance_notification IS NOT NULL THEN d.personalized_advance_notification
                        ELSE d.advance_notification
                    END DAY) <= CURDATE()
                AND NOT EXISTS (
                    SELECT 1
                    FROM tb_notifications n
                    WHERE n.user_id = u.id
                    AND n.related_id = d.id
                    AND n.notification_type = 'document_expiration'
                );
        ";

        // Executar o código SQL
        $conn->exec($sql);
        echo "Notificações inseridas com sucesso!";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
?>