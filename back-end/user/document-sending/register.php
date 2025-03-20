<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    function saveDocumentSending($document_sending_id, $document) {
        // Verifica o MIME type do arquivo enviado
        $mime_type = mime_content_type($document['tmp_name']);

        // Obtém a extensão do arquivo com base no MIME type
        $extension = '';
        switch ($mime_type) {
            case 'application/pdf':
                $extension = 'pdf';
                break;
            case 'application/msword':
                $extension = 'doc';
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $extension = 'docx';
                break;
            case 'application/vnd.ms-excel':
                $extension = 'xls';
                break;
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $extension = 'xlsx';
                break;
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'application/x-pkcs12': 
            case 'application/octet-stream': 
                $extension = 'pfx';
                break;
            default:
                throw new Exception("Formato de arquivo não suportado: {$mime_type}");
        }

        // Converte o nome do documento para minúsculas, substitui espaços por underscores e remove caracteres especiais
        $original_name = pathinfo($document['name'], PATHINFO_FILENAME); // Obtém o nome sem a extensão
        $sanitized_name = strtolower($original_name); // Converte para minúsculo
        $sanitized_name = str_replace(' ', '_', $sanitized_name); // Substitui espaços por _ e remove caracteres não permitidos

        // Verifica se a pasta 'files/documents-sending' existe, caso contrário, cria
        $dir = __DIR__ . "/../../../files/documents-sending/{$document_sending_id}/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // Cria o nome do arquivo com data atual
        $timestamp = date('Y-m-d');
        $file_name = "{$sanitized_name}_{$timestamp}.{$extension}";

        // Caminho completo do arquivo
        $file_path = $dir . "/" . $file_name;
        $file_path_save = INCLUDE_PATH_DASHBOARD . "files/documents-sending/{$document_sending_id}/" . $file_name;

        // Move o arquivo enviado para o diretório
        if (move_uploaded_file($document['tmp_name'], $file_path)) {
            return $file_path_save;
        } else {
            throw new Exception("Falha ao salvar o arquivo: {$document['name']}");
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-document-sending") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $company = $_POST['company'];
            $category = $_POST['category'];
            $department = $_POST['department'];
            $document = $_FILES['document']; // Alterado para usar o $_FILES
            $name = $_POST['name'];

            $reference = $_POST['reference'];

            // Converte de m/Y para Y-m
            $date = DateTime::createFromFormat("m/Y", $reference);
            $reference = $date->format("Y-m");

            $expiration_date = $_POST['expiration_date'];

            $price = trim($_POST['price']);
            $price = str_replace('.', '', $price);
            $price = str_replace(',', '.', $price);

            $observation = isset($_POST['observation']) ? $_POST['observation'] : null;

            $now = date('Y-m-d H:i:s');

            try {
                // Verifica a conexão com o banco
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Inserir dados do tipo de documento com o caminho do arquivo salvo
                $stmt = $conn->prepare("
                    INSERT INTO tb_sending_documents 
                    (user_id, company_id, category_id, department_id, name, reference, expiration_date, price, observation, upload_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id, 
                    $company, 
                    $category, 
                    $department, 
                    $name, 
                    $reference, 
                    $expiration_date, 
                    $price, 
                    $observation,
                    $now
                ]);

                // Recuperar o ID da última categoria inserida
                $document_sending_id = $conn->lastInsertId();

                // Verifica se um arquivo foi enviado antes de salvar
                if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                    $document = $_FILES['document'];

                    // Chama a função para salvar o documento
                    $file_path = saveDocumentSending($document_sending_id, $document);
                    if (!$file_path) {
                        throw new Exception("Erro ao salvar o documento.");
                    }

                    // Atualizar os campos com o novo arquivo
                    $stmt = $conn->prepare("UPDATE tb_sending_documents SET document = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([
                        $file_path,
                        $document_sending_id,
                        $user_id
                    ]);
                }

                // Consulta para buscar o email de envio cadastrado para a empresa
                $stmt = $conn->prepare("
                    SELECT o.*
                    FROM tb_office_users ou
                    JOIN tb_offices o ON ou.office_id = o.id
                    WHERE ou.user_id = ?
                ");
                $stmt->execute([$user_id]);
                $office = $stmt->fetch(PDO::FETCH_ASSOC);

                // Consulta para buscar o email de envio cadastrado para a empresa
                $stmt = $conn->prepare("
                    SELECT c.*, u.firstname AS user_name, u.email AS user_email, u.phone AS user_phone
                    FROM tb_companies c
                    JOIN tb_company_users cu ON c.id = cu.company_id
                    JOIN tb_users u ON cu.user_id = u.id
                    WHERE c.id = ?
                ");
                $stmt->execute([$company]);
                $company = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($company) {
                    $channels = json_decode($company['channels']);

                    $expiration_date = date("d/m/Y", strtotime($_POST['expiration_date']));

                    $stmt = $conn->prepare("SELECT (name) FROM tb_sending_departments WHERE id = ?");
                    $stmt->execute([$department]);
                    $department = $stmt->fetch(PDO::FETCH_ASSOC);

                    $stmt = $conn->prepare("SELECT id, name FROM tb_sending_categories WHERE id = ?");
                    $stmt->execute([$category]);
                    $category = $stmt->fetch(PDO::FETCH_ASSOC);


                    $document = array(
                        "name"              => $name,
                        "expiration_date"   => $expiration_date,
                        "reference"         => $_POST['reference'],
                        "company"           => $company['name'],
                        "department"        => $department['name'],
                        "category"          => $category['name'],
                        "price"             => "R$ " . $_POST['price']
                    );

                    $document_link = INCLUDE_PATH_PORTAL . "c/" . $category['id'];

                    if ($channels) {
                        if (in_array("email", $channels)) {
                            $subject = "Documento registrado para sua empresa - " . $project['name'];
                            $content = array("layout" => "sending-notification-document", "content" => array("office" => $office['name'], "firstname" => $company['user_name'], "document" => $document, "link" => $document_link));
                            sendMail($company['user_name'], $company['user_email'], $subject, $content);
                        }
    
                        // if (in_array("whatsapp", $channels)) {
                        //     $phone = '+55' . preg_replace('/[()\-\s]/', '', $company['user_phone']);
    
                        //     $message = "Olá, {$company['user_name']}! 👋\n\n" .
                        //         "O escritório *{$office['name']}* associou um novo documento à sua empresa.\n\n" .
                        //         "Para visualizá-lo, clique no link abaixo:\n\n" .
                        //         "$document_link\n\n" .
                        //         "Obrigado por usar o *{$project['name']}*! 😊";
    
                        //     sendWhatsAppMessage($phone, $message);
                        // }
                    }
                }

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success', 'message' => 'Documento de envio registrado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Documento de envio registrado com sucesso.');
                $_SESSION['msg'] = $message;

            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao registrar o documento de envio: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar o documento de envio', 'error' => $e->getMessage()]);
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