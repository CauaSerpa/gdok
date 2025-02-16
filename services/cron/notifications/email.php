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
    $systemName = $project['name'] ?? 'GDok - Alertas'; // Caso $project['name'] não esteja definido

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
    $sql = "
        SELECT 
            u.id AS user_id,
            u.email AS user_email,
            u.firstname AS user_name,
            c.name AS company_name,
            c.email AS company_email,
            c.notify_email,
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
            d.id IS NOT NULL
            AND (
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
                    AND n.notification_type = 'document_expiration_email'
                ))
            )
        ORDER BY 
            d.expiration_date ASC;
    ";

    // Executar a consulta
    $stmt = $conn->query($sql);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fechar o cursor após buscar os resultados
    $stmt->closeCursor();

    // Verificar se há documentos para notificar
    if (!empty($documents)) {
        $userDocuments = [];

        // Agrupar documentos por usuário
        foreach ($documents as $document) {
            $userDocuments[$document['user_id']]['user_email'] = $document['user_email'];
            $userDocuments[$document['user_id']]['user_name'] = $document['user_name'];
            $userDocuments[$document['user_id']]['company_name'] = $document['company_name'];
            $userDocuments[$document['user_id']]['company_email'] = $document['company_email'];
            $userDocuments[$document['user_id']]['notify_email'] = $document['notify_email'];
            $userDocuments[$document['user_id']]['documents'][] = $document;
        }

        // Para cada usuário, enviar o e-mail com a lista de documentos
        foreach ($userDocuments as $userId => $userData) {
            $userEmail = $userData['user_email'];
            $userName = $userData['user_name'];
            $companyName = $userData['company_name'];
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
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f4;
                            margin: 0;
                            padding: 0;
                        }
                        .email-container {
                            background-color: #ffffff;
                            max-width: 600px;
                            margin: 20px auto;
                            padding: 20px;
                            border-radius: 8px;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        .email-header {
                            color: #ffffff;
                            padding: 20px;
                            text-align: center;
                            border-radius: 8px 8px 0 0;
                            background-image: linear-gradient(to right, #287F71, #06162E);
                        }
                        img.logo {
                            max-width: 150px;
                        }
                        .email-header h1 {
                            margin: 0;
                            font-size: 24px;
                        }
                        .email-body {
                            padding: 20px;
                            line-height: 1.6;
                            color: #333333;
                        }
                        .email-body h2 {
                            font-size: 20px;
                            margin-bottom: 10px;
                        }
                        .email-footer {
                            text-align: center;
                            color: #777777;
                            font-size: 12px;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>
                            <img src='" . INCLUDE_PATH_DASHBOARD . "assets/images/logo-light.png' class='logo'>
                        </div>
                        <div class='email-body'>
                            <h2>Olá, " . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . "!</h2>
                            <p>Esta é uma listagem dos documentos que estão próximos do vencimento em sua empresa <strong>" . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . "</strong>.</p>
                            <p>Por favor, veja a tabela abaixo com os detalhes de vencimento:</p>
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
            $mail->addAddress($userEmail);
            $mail->Subject = "Aviso: Documentos Próximos ao Vencimento";
            $mail->Body    = $messageBody;
            $mail->AltBody = strip_tags($messageBody);

            // Envia o e-mail
            if ($mail->send()) {

                foreach ($documentsList as $document) {

                    // Se a empresa tem o campo notify_email definido, insira o código específico.
                    if ($document['notify_email']) {

                        $documentNameContent = !empty($document['document_name']) ? "o seu documento <strong>" . htmlspecialchars($document['document_name'], ENT_QUOTES, 'UTF-8') . "</strong>" : "um documento";

                        $messageBody = "
                            <!DOCTYPE html>
                            <html lang='pt-BR'>
                            <head>
                                <meta charset='UTF-8'>
                                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                                <title>Aviso de Vencimento de Documento</title>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        background-color: #f4f4f4;
                                        margin: 0;
                                        padding: 0;
                                    }
                                    .email-container {
                                        background-color: #ffffff;
                                        max-width: 600px;
                                        margin: 20px auto;
                                        padding: 20px;
                                        border-radius: 8px;
                                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                    }
                                    .email-header {
                                        color: #ffffff;
                                        padding: 20px;
                                        text-align: center;
                                        border-radius: 8px 8px 0 0;
                                        background-image: linear-gradient(to right, #287F71, #06162E);
                                    }
                                    img.logo {
                                        max-width: 150px;
                                    }
                                    .email-header h1 {
                                        margin: 0;
                                        font-size: 24px;
                                    }
                                    .email-body {
                                        padding: 20px;
                                        line-height: 1.6;
                                        color: #333333;
                                    }
                                    .email-body h2 {
                                        font-size: 20px;
                                        margin-bottom: 10px;
                                    }
                                    .email-body p {
                                        margin: 10px 0;
                                    }
                                    .expiration-date {
                                        font-size: 18px;
                                        font-weight: bold;
                                        color: #287F71;
                                        margin: 10px 0;
                                    }
                                    .cta-button {
                                        display: inline-block;
                                        padding: 12px 24px;
                                        font-size: 16px;
                                        color: #ffffff;
                                        background-color: #287F71;
                                        border-radius: 5px;
                                        text-decoration: none;
                                        margin-top: 20px;
                                    }
                                    .cta-button:hover {
                                        background-color: #1e6f5d;
                                    }
                                    .email-footer {
                                        text-align: center;
                                        color: #777777;
                                        font-size: 12px;
                                        margin-top: 20px;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class='email-container'>
                                    <div class='email-header'>
                                        <img src='" . INCLUDE_PATH_DASHBOARD . "assets/images/logo-light.png' class='logo'>
                                    </div>
                                    <div class='email-body'>
                                        <h2>Olá, " . htmlspecialchars($document['company_name'], ENT_QUOTES, 'UTF-8') . "!</h2>
                                        <p>Estamos enviando este aviso para lembrá-los de que $documentNameContent está próximo de vencer.</p>
                                        <p>Data de vencimento: <span class='expiration-date'>" . htmlspecialchars(DateTime::createFromFormat('Y-m-d', $document['expiration_date'])->format('d/m/Y'), ENT_QUOTES, 'UTF-8') . "</span></p>
                                        <p>Por favor, tome as medidas necessárias para garantir que o documento seja renovado a tempo.</p>
                                    </div>
                                    <div class='email-footer'>
                                        <p>Este é um e-mail automático. Por favor, não responda.</p>
                                        <p>Direitos autorais &copy; " . htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') . " " . date('Y') . "</p>
                                    </div>
                                </div>
                            </body>
                            </html>
                        ";

                        // Configura o conteúdo do e-mail
                        $mail->addAddress($document['company_email']);
                        $mail->Subject = "Aviso: Documentos Próximos ao Vencimento";
                        $mail->Body    = $messageBody;
                        $mail->AltBody = strip_tags($messageBody);
                        $mail->send();

                    }

                }

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

    // Salvar momento que foi feita a execucao do cron
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process) VALUES ('success', 'send_document_expiration_email')");
    $stmt->execute();
} catch (PDOException $e) {
    echo "Erro no banco de dados: " . $e->getMessage();

    // Em caso de erro, registra no log
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process, error_message) VALUES ('error', 'send_document_expiration_email', ?)");
    $stmt->execute([$e->getMessage()]);
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: " . $e->getMessage();

    // Em caso de erro, registra no log
    $stmt = $conn->prepare("INSERT INTO tb_cron_logs (status, process, error_message) VALUES ('error', 'send_document_expiration_email', ?)");
    $stmt->execute([$e->getMessage()]);
}
?>