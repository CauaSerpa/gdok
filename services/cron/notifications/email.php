<?php 
// Incluindo o arquivo de configuração para o banco de dados
include('./../../../config.php');

// Incluindo a biblioteca PHPMailer para envio de e-mails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

try {
    // Informações para PHPMailer
    $smtp_host = $_ENV['SMTP_HOST'];
    $smtp_username = $_ENV['SMTP_USERNAME'];
    $smtp_password = $_ENV['SMTP_PASSWORD'];
    $smtp_secure = $_ENV['SMTP_SECURE'];
    $smtp_port = $_ENV['SMTP_PORT'];
    $smtp_email = $_ENV['SMTP_EMAIL'];
    $smtp_title = $_ENV['SMTP_TITLE'];

    // Nome do sistema
    $systemName = $project['name'] ?? 'GDok - Alertas';

    // Configuração do PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = $smtp_secure;
    $mail->Port       = $smtp_port;

    // Defina os detalhes do remetente
    $mail->setFrom($smtp_email, $smtp_title);
    $mail->addReplyTo($smtp_email, $systemName);

    // Consulta ao banco de dados para buscar os documentos relevantes
    // (A query foi adaptada para buscar documentos num intervalo maior; a lógica específica será feita em PHP)
    $sql = "
        SELECT 
            u.id AS user_id,
            u.email AS user_email,
            u.firstname AS user_name,
            c.office_id,
            c.name AS company_name,
            c.email AS company_email,
            c.notify_email,
            d.id AS document_id,
            d.name AS document_name,
            dt.name AS document_type_name,
            d.expiration_date,
            d.advance_notification,
            d.personalized_advance_notification,
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
            d.id IS NOT NULL
            AND CURDATE() BETWEEN DATE_SUB(d.expiration_date, INTERVAL 
                CASE 
                    WHEN d.personalized_advance_notification IS NOT NULL THEN d.personalized_advance_notification 
                    ELSE d.advance_notification 
                END DAY)
            AND DATE_ADD(d.expiration_date, INTERVAL 7 DAY)
        ORDER BY 
            d.expiration_date ASC;
    ";

    $stmt = $conn->query($sql);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Verificar se há documentos para notificar
    if (!empty($documents)) {
        // Para evitar consultas repetidas, vamos agrupar as configurações por office_id
        $officeSettings = [];

        // Agrupar documentos por usuário
        $userDocuments = [];
        foreach ($documents as $document) {
            // Recupera (ou busca) as configurações de notificação do escritório
            $officeId = $document['office_id'];
            if (!isset($officeSettings[$officeId])) {
                $stmtSetting = $conn->prepare("SELECT office_id, is_active, contact, send_type, start_days_before, after_due_days 
                    FROM tb_office_notification_settings 
                    WHERE office_id = ? AND channel = 'email'");
                $stmtSetting->execute([$officeId]);
                $officeSettings[$officeId] = $stmtSetting->fetch(PDO::FETCH_ASSOC);
            }
            $settingsEmail = $officeSettings[$officeId];

            // Se a notificação por e-mail não estiver ativa, pula este documento
            if (!$settingsEmail || $settingsEmail['is_active'] != 1) {
                continue;
            }

            // Define a lógica de envio de acordo com o tipo parametrizado
            // Obtém os valores de configuração (padrão se não estiverem preenchidos)
            $sendType       = $settingsEmail['send_type'] ?? 'once_due';
            $startDaysBefore = (int)($settingsEmail['start_days_before'] ?? ($document['personalized_advance_notification'] ?? $document['advance_notification']));
            $afterDueDays    = (int)($settingsEmail['after_due_days'] ?? 7);

            // Converte datas para DateTime para facilitar comparações
            $today        = new DateTime();
            $expiration   = new DateTime($document['expiration_date']);
            $notificationStart = (clone $expiration)->sub(new DateInterval("P{$startDaysBefore}D"));
            $notificationEnd   = (clone $expiration)->add(new DateInterval("P{$afterDueDays}D"));

            // Inicialmente, decide não enviar
            $shouldSend = false;

            switch($sendType) {
                case 'once_due':
                    // Notifica uma vez, no início do período ou no próprio dia de vencimento
                    if ($today->format('Y-m-d') == $notificationStart->format('Y-m-d') ||
                        $today->format('Y-m-d') == $expiration->format('Y-m-d')) {
                        // Verifica se já foi notificado (a query original usava NOT EXISTS)
                        $checkStmt = $conn->prepare("SELECT 1 FROM tb_notifications n WHERE n.user_id = ? AND n.related_id = ? AND n.notification_type = 'document_expiration_email'");
                        $checkStmt->execute([$document['user_id'], $document['document_id']]);
                        if (!$checkStmt->fetch()) {
                            $shouldSend = true;
                        }
                    }
                    break;

                case 'daily_until_due':
                    // Notifica diariamente desde o início do período até o dia do vencimento
                    if ($today >= $notificationStart && $today <= $expiration) {
                        $shouldSend = true;
                    }
                    break;

                case 'daily_until_after':
                    // Notifica diariamente desde o início do período até N dias após o vencimento
                    if ($today >= $notificationStart && $today <= $notificationEnd) {
                        $shouldSend = true;
                    }
                    break;

                case 'predefined_dates':
                    // Notifica somente em datas específicas: no início do período, no dia de vencimento e exatamente após o período definido
                    if (
                        $today->format('Y-m-d') == $notificationStart->format('Y-m-d') ||
                        $today->format('Y-m-d') == $expiration->format('Y-m-d') ||
                        $today->format('Y-m-d') == $notificationEnd->format('Y-m-d')
                    ) {
                        $shouldSend = true;
                    }
                    break;

                case 'due_date':
                    // Notifica apenas no dia do vencimento
                    if ($today->format('Y-m-d') == $expiration->format('Y-m-d')) {
                        $shouldSend = true;
                    }
                    break;

                case 'personalized':
                    // Notifica somente no dia definido pelo usuário (usando o campo personalizado do documento)
                    if ($document['personalized_advance_notification'] !== null) {
                        $personalizedNotificationDate = (clone $expiration)->sub(new DateInterval("P" . $document['personalized_advance_notification'] . "D"));
                        if ($today->format('Y-m-d') == $personalizedNotificationDate->format('Y-m-d')) {
                            $shouldSend = true;
                        }
                    }
                    break;
            }

            // Se a lógica definir que o e-mail deve ser enviado, agrupa o documento para o usuário
            if ($shouldSend) {
                $userDocuments[$document['user_id']]['user_email'] = $document['user_email'];
                $userDocuments[$document['user_id']]['user_name'] = $document['user_name'];
                $userDocuments[$document['user_id']]['company_name'] = $document['company_name'];
                $userDocuments[$document['user_id']]['company_email'] = $document['company_email'];
                $userDocuments[$document['user_id']]['notify_email'] = $document['notify_email'];
                $userDocuments[$document['user_id']]['documents'][] = $document;
            }
        }

        // Para cada usuário, enviar o e-mail com a lista de documentos
        foreach ($userDocuments as $userId => $userData) {
            $userEmail    = $userData['user_email'];
            $userName     = $userData['user_name'];
            $companyName  = $userData['company_name'];
            $documentsList = $userData['documents'];

            // Montar a tabela de documentos
            $documentsTable = "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <thead>
                    <tr>
                        <th style='text-align: left;'>Empresa</th>
                        <th style='text-align: left;'>Documento</th>
                        <th style='text-align: left;'>Data de Vencimento</th>
                    </tr>
                </thead>
                <tbody>";

            foreach ($documentsList as $document) {
                $documentsTable .= "<tr>
                    <td>" . htmlspecialchars($document['company_name'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($document['document_type_name'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars(DateTime::createFromFormat('Y-m-d', $document['expiration_date'])->format('d/m/Y'), ENT_QUOTES, 'UTF-8') . "</td>
                </tr>";
            }
            $documentsTable .= "</tbody></table>";

            $messageBody = "
                <!DOCTYPE html>
                <html lang='pt-BR'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Aviso de Vencimento de Documentos</title>
                    <style>
                        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                        .email-container { background-color: #ffffff; max-width: 600px; margin: 20px auto; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                        .email-header { color: #ffffff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; background-image: linear-gradient(to right, #287F71, #06162E); }
                        img.logo { max-width: 150px; }
                        .email-header h1 { margin: 0; font-size: 24px; }
                        .email-body { padding: 20px; line-height: 1.6; color: #333333; }
                        .email-body h2 { font-size: 20px; margin-bottom: 10px; }
                        .email-footer { text-align: center; color: #777777; font-size: 12px; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>
                            <img src='" . INCLUDE_PATH_DASHBOARD . "assets/images/logo-light.png' class='logo'>
                        </div>
                        <div class='email-body'>
                            <h2>Olá, " . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . "!</h2>
                            <p>Segue abaixo a listagem dos documentos que estão próximos do vencimento em sua empresa <strong>" . htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') . "</strong>:</p>
                            $documentsTable
                        </div>
                        <div class='email-footer'>
                            <p>Este é um e-mail automático. Por favor, não responda.</p>
                            <p>&copy; " . htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') . " " . date('Y') . "</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Configura o conteúdo do e-mail
            $mail->clearAddresses(); // limpa endereços anteriores
            $mail->addAddress($userEmail);
            $mail->Subject = "Aviso: Documentos Próximos ao Vencimento";
            $mail->Body    = $messageBody;
            $mail->AltBody = strip_tags($messageBody);

            if ($mail->send()) {

                // Registra cada notificação enviada para cada documento
                $insertSql = "INSERT INTO tb_notifications (user_id, related_id, title, message, notification_type, created_at)
                              VALUES (:user_id, :related_id, :title, :message, :notification_type, NOW())";
                $stmtInsert = $conn->prepare($insertSql);
                foreach ($documentsList as $document) {
                    $stmtInsert->execute([
                        ':user_id' => $userId,
                        ':related_id' => $document['document_id'],
                        ':title' => 'Aviso: Documentos Próximos ao Vencimento',
                        ':message' => $messageBody,
                        ':notification_type' => 'document_expiration_email',
                    ]);
                }
            } else {
                echo "Falha ao enviar e-mail para {$userEmail}.\n";
            }
        }
    } else {
        echo "Nenhuma notificação a ser enviada no momento.\n";
    }

    // Salvar no log o momento que foi feita a execução do cron
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process) VALUES ('success', 'send_document_expiration_email')");
    $stmt->execute();

} catch (PDOException $e) {
    echo "Erro no banco de dados: " . $e->getMessage();
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process, error_message) VALUES ('error', 'send_document_expiration_email', ?)");
    $stmt->execute([$e->getMessage()]);
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: " . $e->getMessage();
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process, error_message) VALUES ('error', 'send_document_expiration_email', ?)");
    $stmt->execute([$e->getMessage()]);
}