<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    function saveLogo($company_sending_id, $logo) {
        // Verifica o MIME type do arquivo enviado
        $mime_type = mime_content_type($logo['tmp_name']);

        // Obtém a extensão do arquivo com base no MIME type
        $extension = '';
        switch ($mime_type) {
            case 'application/pdf':
                $extension = 'pdf';
                break;
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            default:
                throw new Exception("Formato de arquivo não suportado: {$mime_type}");
        }

        // Converte o nome da logo para minúsculas, substitui espaços por underscores e remove caracteres especiais
        $original_name = pathinfo($logo['name'], PATHINFO_FILENAME); // Obtém o nome sem a extensão
        $sanitized_name = strtolower($original_name); // Converte para minúsculo
        $sanitized_name = str_replace(' ', '_', $sanitized_name); // Substitui espaços por _ e remove caracteres não permitidos

        // Verifica se a pasta 'files/companies-logos' existe, caso contrário, cria
        $dir = __DIR__ . "/../../../files/companies-logos/{$company_sending_id}/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // Cria o nome do arquivo com data atual
        $timestamp = date('Y-m-d');
        $file_name = "{$sanitized_name}_{$timestamp}.{$extension}";

        // Caminho completo do arquivo
        $file_path = $dir . "/" . $file_name;
        $file_path_save = INCLUDE_PATH_DASHBOARD . "files/companies-logos/{$company_sending_id}/" . $file_name;

        // Move o arquivo enviado para o diretório
        if (move_uploaded_file($logo['tmp_name'], $file_path)) {
            return $file_path_save;
        } else {
            throw new Exception("Falha ao salvar o arquivo: {$logo['name']}");
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-company-sending") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $name = $_POST['name'];
            $logo = $_FILES['logo']; // Alterado para usar o $_FILES
            $login = $_POST['login'];
            $phone = $_POST['phone'];
            $notify_phone = isset($_POST['notify_phone']) ? 1 : 0;
            $email = $_POST['email'];
            $notify_email = isset($_POST['notify_email']) ? 1 : 0;
            $responsible = $_POST['responsible'];
            $active_token = generateToken();

            try {
                // Iniciar transação
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Inserir dados da empresa
                $stmt = $conn->prepare("INSERT INTO tb_sending_companies (user_id, name, phone, notify_phone, email, notify_email, responsible) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $phone, $notify_phone, $email, $notify_email, $responsible]);
                $company_sending_id = $conn->lastInsertId();

                // Verifica se um arquivo foi enviado antes de salvar
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $logo = $_FILES['logo'];

                    // Chama a função para salvar a logo
                    $file_path = saveLogo($company_sending_id, $logo);
                    if (!$file_path) {
                        throw new Exception("Erro ao salvar a logo.");
                    }

                    // Atualizar os campos com o novo arquivo
                    $stmt = $conn->prepare("UPDATE tb_sending_companies SET logo = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([
                        $file_path,
                        $company_sending_id,
                        $user_id
                    ]);
                }

                // Inserir dados do usuário
                $stmt = $conn->prepare("INSERT INTO tb_users (firstname, email, active_token) VALUES (?, ?, ?)");
                $stmt->execute([$responsible, $login, $active_token]);
                $user_id = $conn->lastInsertId();

                // Associar o usuário ao escritório como 'owner'
                $stmt = $conn->prepare("INSERT INTO tb_sending_company_users (company_id, user_id, role) VALUES (?, ?, ?)");
                $stmt->execute([$company_sending_id, $user_id, 'owner']);

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success', 'message' => 'Empresa registrada com sucesso.']);

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