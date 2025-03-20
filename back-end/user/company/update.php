<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-company") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $company_id = $_POST['company_id'];
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

            // Consulta para buscar o usuário com o email de envio informado
            $stmt = $conn->prepare("SELECT * FROM tb_users WHERE email = ?");
            $stmt->execute([$email_envios]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Consulta para buscar o email de envio cadastrado para a empresa
            $stmt = $conn->prepare("
                SELECT u.*, u.email AS email_envios 
                FROM tb_company_users cu
                JOIN tb_users u ON u.id = cu.user_id
                WHERE cu.company_id = ?
            ");
            $stmt->execute([$company_id]);
            $company_users = $stmt->fetch(PDO::FETCH_ASSOC);

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Se o email informado for diferente do email cadastrado para a empresa e já existir em outro usuário
                if ($company_users['email_envios'] != $email_envios && $user) {
                    throw new Exception("O e-mail já está cadastrado. Por favor, utilize outro e-mail para envio.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    UPDATE tb_companies SET
                    name = ?, phone = ?, notify_phone = ?, email = ?, notify_email = ?, responsible = ?, document = ?, uf = ?, cidade = ?, channels = ?, use_same_email_envios = ?, email_envios = ?, use_same_whatsapp_envios = ?, whatsapp_envios = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([
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
                    $whatsapp_envios,
                    $company_id,
                    $_SESSION['user_id']
                ]);

                // if (in_array("email", $channels)) {
                //     if ($email_envios != $company_users['email_envios']) {

                //         // Inserir dados do usuário
                //         $stmt = $conn->prepare("INSERT INTO tb_users (role, firstname, email, phone, active_token) VALUES (3, ?, ?, ?, ?)");
                //         $stmt->execute([$responsible, $email_envios, $whatsapp_envios, $active_token]);
                //         $user_id = $conn->lastInsertId();

                //         // Associar o usuário ao escritório como 'owner'
                //         $stmt = $conn->prepare("INSERT INTO tb_company_users (company_id, user_id, role) VALUES (?, ?, ?)");
                //         $stmt->execute([$company_id, $user_id, 'owner']);

                //         // Enviar e-mail de verificação
                //         $verification_link = INCLUDE_PATH_AUTH . "finalizar-cadastro/" . $active_token;
                //         $subject = "Bem-vindo ao " . $project['name'];
                //         $content = array("layout" => "finalize-registration", "content" => array("office" => $office['name'], "firstname" => $responsible, "link" => $verification_link));
                //         sendMail($responsible, $email_envios, $subject, $content);

                //     }
                // }

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Empresa atualizada com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Empresa atualizada com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao atualizar a empresa: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar a empresa', 'error' => $e->getMessage()]);
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