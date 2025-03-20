<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    function saveNewDocument($document_id, $document, $conn) {
        // Remover o arquivo antigo, caso exista
        $stmt = $conn->prepare("SELECT document FROM tb_documents WHERE id = ? AND user_id = ?");
        $stmt->execute([$document_id, $_SESSION['user_id']]);
        $old_document = $stmt->fetchColumn();

        if ($old_document) {
            $folderPath = __DIR__ . "/../../../files/documents/{$document_id}";
            if (!is_dir($folderPath)) {
                return;
            }
            $files = array_diff(scandir($folderPath), ['.', '..']);
            foreach ($files as $file) {
                $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filePath)) {
                    deleteFolder($filePath);
                } else {
                    unlink($filePath);
                }
            }
            rmdir($folderPath);
        }

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
        $sanitized_name = preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $sanitized_name)); // Substitui espaços por _ e remove caracteres não permitidos

        // Verifica se a pasta 'files/documents' existe, caso contrário, cria
        $dir = __DIR__ . "/../../../files/documents/{$document_id}";
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

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-document") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $document_id = $_POST['document_id'];
            $company = $_POST['company'];
            $document_type = $_POST['document_type'];
            $name = $_POST['name'];
            $expiration_date = $_POST['expiration_date'];
            $advance_notification = $_POST['advance_notification'];
            $personalized_advance_notification = $_POST['personalized_advance_notification'];
            $observation = $_POST['observation'];
            $document = $_FILES['document'] ?? null;

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Verificar se o arquivo foi enviado
                if ($document && $document['error'] === 0) {
                    // Chama a função para salvar o documento
                    $file_path = saveNewDocument($document_id, $document, $conn);
                    if (!$file_path) {
                        throw new Exception("Erro ao salvar o documento.");
                    }

                    // Atualizar os campos com o novo arquivo
                    $stmt = $conn->prepare("
                        UPDATE tb_documents SET
                        user_id = ?, company_id = ?, document_type_id = ?, document = ?, name = ?, expiration_date = ?, advance_notification = ?, personalized_advance_notification = ?, observation = ?
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([
                        $user_id,
                        $company,
                        $document_type,
                        $file_path,
                        $name,
                        $expiration_date,
                        $advance_notification,
                        $personalized_advance_notification,
                        $observation,
                        $document_id,
                        $user_id
                    ]);
                } else {
                    // Caso não haja novo arquivo, apenas atualizar os outros campos
                    $stmt = $conn->prepare("
                        UPDATE tb_documents SET
                        user_id = ?, company_id = ?, document_type_id = ?, name = ?, expiration_date = ?, advance_notification = ?, personalized_advance_notification = ?, observation = ?
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([
                        $user_id,
                        $company,
                        $document_type,
                        $name,
                        $expiration_date,
                        $advance_notification,
                        $personalized_advance_notification,
                        $observation,
                        $document_id,
                        $user_id
                    ]);
                }

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Documento atualizado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Documento atualizado com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao atualizar o documento: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o documento', 'error' => $e->getMessage()]);
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