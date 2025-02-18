<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    function saveDocument($document_id, $document) {
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
                $extension = 'p12';
                break;
            default:
                throw new Exception("Formato de arquivo não suportado: {$mime_type}");
        }

        // Converte o nome do documento para minúsculas, substitui espaços por underscores e remove caracteres especiais
        $original_name = pathinfo($document['name'], PATHINFO_FILENAME); // Obtém o nome sem a extensão
        $sanitized_name = strtolower($original_name); // Converte para minúsculo
        $sanitized_name = str_replace(' ', '_', $sanitized_name); // Substitui espaços por _ e remove caracteres não permitidos

        // Verifica se a pasta 'files/documents' existe, caso contrário, cria
        $dir = __DIR__ . "/../../../files/documents/{$document_id}/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // Cria o nome do arquivo com data atual
        $timestamp = date('Y-m-d');
        $file_name = "{$sanitized_name}_{$timestamp}.{$extension}";

        // Caminho completo do arquivo
        $file_path = $dir . "/" . $file_name;
        $file_path_save = INCLUDE_PATH_DASHBOARD . "files/documents/{$document_id}/" . $file_name;

        // Move o arquivo enviado para o diretório
        if (move_uploaded_file($document['tmp_name'], $file_path)) {
            return $file_path_save;
        } else {
            throw new Exception("Falha ao salvar o arquivo: {$document['name']}");
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "no-renewal") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $document_id = $_POST['document_id'];

            try {
                // Verifica a conexão com o banco
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Atualizar os campos com o novo arquivo
                $stmt = $conn->prepare("UPDATE tb_documents SET status = 2 WHERE id = ? AND user_id = ?");
                $stmt->execute([$document_id, $user_id]);

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success', 'message' => 'Renovação informada com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Renovação informada com sucesso.');
                $_SESSION['msg'] = $message;

                exit;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao informar a renovação do documento: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao informar a renovação do documento', 'error' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
            exit;
        }
    } else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "renew-with-new") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $document_id = $_POST['document_id'];
            $document = $_FILES['document']; // Alterado para usar o $_FILES
            $expiration_date = $_POST['expiration_date'];

            try {
                // Verifica a conexão com o banco
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                $stmt = $conn->prepare("SELECT * FROM tb_documents WHERE id = ? AND user_id = ? LIMIT 1");
                $stmt->execute([$document_id, $user_id]);
                $document = $stmt->fetch(PDO::FETCH_ASSOC);

                // Iniciar transação
                $conn->beginTransaction();

                // Atualizar os campos com o novo arquivo
                $stmt = $conn->prepare("UPDATE tb_documents SET status = 2 WHERE id = ? AND user_id = ?");
                $stmt->execute([$document_id, $user_id]);

                // Inserir dados do tipo de documento com o caminho do arquivo salvo
                $stmt = $conn->prepare("
                    INSERT INTO tb_documents 
                    (user_id, company_id, document_type_id, name, expiration_date, advance_notification, personalized_advance_notification, observation) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id, 
                    $document['company_id'], 
                    $document['document_type_id'], 
                    $document['name'], 
                    $expiration_date, 
                    $document['advance_notification'], 
                    $document['personalized_advance_notification'], 
                    $document['observation'] 
                ]);

                // Recuperar o ID da última categoria inserida
                $document_id = $conn->lastInsertId();

                // Verifica se um arquivo foi enviado antes de salvar
                if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                    $document = $_FILES['document'];

                    // Chama a função para salvar o documento
                    $file_path = saveDocument($document_id, $document);
                    if (!$file_path) {
                        throw new Exception("Erro ao salvar o documento.");
                    }

                    // Atualizar os campos com o novo arquivo
                    $stmt = $conn->prepare("UPDATE tb_documents SET document = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([
                        $file_path,
                        $document_id,
                        $user_id
                    ]);
                }

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success', 'message' => 'Novo documento registrado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Novo documento registrado com sucesso.');
                $_SESSION['msg'] = $message;

            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao registrar o novo documento: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar o novo documento', 'error' => $e->getMessage()]);
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