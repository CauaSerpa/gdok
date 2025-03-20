<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && ($_POST['action'] === "register-company" ||  $_POST['action'] === "register-company-modal")) {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $notify_phone = isset($_POST['notify_phone']) ? 1 : 0;
            $email = $_POST['email'];
            $notify_email = isset($_POST['notify_email']) ? 1 : 0;
            $responsible = $_POST['responsible'];
            $document = $_POST['document'];
            $uf = $_POST['uf'];
            $cidade = $_POST['cidade'];
            $active_token = generateToken();

            // Capturando os canais habilitados (array com os valores: email, whatsapp, portal)
            $channels = isset($_POST['channels']) ? $_POST['channels'] : array();
            // Podemos salvar como JSON ou como uma string separada por vírgulas
            $channels_str = json_encode($channels);

            // Para o canal de e-mail:
            // Se o checkbox "use_same_email_envios" estiver marcado, usamos o e-mail principal;
            // caso contrário, usamos o campo "email_envios" enviado.
            if (in_array("email", $channels)) {
                $use_same_email_envios = isset($_POST['use_same_email_envios']) ? 1 : 0;
                $email_envios = $use_same_email_envios ? $email : (isset($_POST['email_envios']) ? trim($_POST['email_envios']) : '');
            } else {
                $use_same_email_envios = 1;
                $email_envios = null;
            }

            // Para o canal de WhatsApp:
            if (in_array("whatsapp", $channels)) {
                $use_same_whatsapp_envios = isset($_POST['use_same_whatsapp_envios']) ? 1 : 0;
                $whatsapp_envios = $use_same_whatsapp_envios ? $phone : (isset($_POST['whatsapp_envios']) ? trim($_POST['whatsapp_envios']) : '');
            } else {
                $use_same_whatsapp_envios = 1;
                $whatsapp_envios = null;
            }

            // Para o canal de e-mail do portal:
            if (in_array("portal", $channels)) {
                $use_same_portal_envios = isset($_POST['use_same_portal_envios']) ? 1 : 0;
                $portal_envios = $use_same_portal_envios ? $email : (isset($_POST['portal_envios']) ? trim($_POST['portal_envios']) : '');
            } else {
                $use_same_portal_envios = 1;
                $portal_envios = null;
            }

            // Consulta para buscar documentos de envio cadastradas
            $stmt = $conn->prepare("SELECT * FROM tb_users WHERE email = ?");
            $stmt->execute([$portal_envios]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'O e-mail já está cadastrado. Por favor, utilize outro e-mail para envio.']);
                exit;
            }

            // Buscar escritorio que gerencia a empresa do usuario atual
            $stmt = $conn->prepare("
                SELECT o.*
                FROM tb_office_users ou
                JOIN tb_offices o ON o.id = ou.office_id
                WHERE ou.user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $office = $stmt->fetch(PDO::FETCH_ASSOC);

            try {
                // Iniciar transação
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                if ($user) {
                    throw new Exception("O e-mail já está cadastrado. Por favor, utilize outro e-mail para envio.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Inserir dados da empresa
                $stmt = $conn->prepare("INSERT INTO tb_companies (user_id, name, phone, notify_phone, email, notify_email, responsible, document, uf, cidade, channels, use_same_email_envios, email_envios, use_same_whatsapp_envios, whatsapp_envios) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $name,
                    $phone,
                    $notify_phone,
                    $email,
                    $notify_email,
                    $responsible,
                    $document,
                    $uf,
                    $cidade,
                    $channels_str,
                    $use_same_email_envios,
                    $email_envios,
                    $use_same_whatsapp_envios,
                    $whatsapp_envios
                ]);
                $company_id = $conn->lastInsertId();

                if (in_array("portal", $channels)) {
                    // Inserir dados do usuário
                    $stmt = $conn->prepare("INSERT INTO tb_users (role, firstname, email, phone, active_token) VALUES (3, ?, ?, ?, ?)");
                    $stmt->execute([$responsible, $portal_envios, $whatsapp_envios, $active_token]);
                    $user_id = $conn->lastInsertId();

                    // Associar o usuário ao escritório como 'owner'
                    $stmt = $conn->prepare("INSERT INTO tb_company_users (company_id, user_id, role) VALUES (?, ?, ?)");
                    $stmt->execute([$company_id, $user_id, 'owner']);

                    // Enviar e-mail de verificação
                    $verification_link = INCLUDE_PATH_AUTH . "finalizar-cadastro/" . $active_token;
                    $subject = "Bem-vindo ao " . $project['name'];
                    $content = array("layout" => "finalize-registration", "content" => array("office" => $office['name'], "firstname" => $responsible, "link" => $verification_link));
                    sendMail($responsible, $portal_envios, $subject, $content);
                }

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                if ($_POST['action'] === "register-company-modal") {
                    echo json_encode(['status' => 'success', 'message' => 'Empresa registrada com sucesso.', 'company' => ['id' => $company_id,'name' => $name]]);
                } else {
                    echo json_encode(['status' => 'success', 'message' => 'Empresa registrada com sucesso.']);
                }

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Empresa registrada com sucesso.');
                $_SESSION['msg'] = $message;

            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao registrar a empresa: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar a empresa', 'error' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }