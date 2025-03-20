<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    function saveOfficeLogo($office_id, $image) {
        global $conn;

        // Verifica o MIME type da imagem
        $mime_type = mime_content_type($image['tmp_name']);

        // Definindo a extensão com base no MIME type
        $extension = '';
        switch ($mime_type) {
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
            default:
                throw new Exception("Formato de imagem não suportado: {$mime_type}");
        }

        // Buscar logo antiga no banco
        $stmt = $conn->prepare("SELECT logo FROM tb_offices WHERE id = ?");
        $stmt->execute([$office_id]);
        $old_logo = $stmt->fetchColumn();

        // Apagar a logo antiga, se existir
        if ($old_logo && file_exists(__DIR__ . '/../../../' . str_replace(INCLUDE_PATH_DASHBOARD, '', $old_logo))) {
            unlink(__DIR__ . '/../../../' . str_replace(INCLUDE_PATH_DASHBOARD, '', $old_logo));
        }

        // Sanitize o nome da imagem
        $original_name = pathinfo($image['name'], PATHINFO_FILENAME);
        $sanitized_name = strtolower(str_replace(' ', '_', $original_name));
        $timestamp = date('Y-m-d_H-i-s'); // Tira timestamp para o nome único
        $file_name = "{$original_name}.{$extension}";

        // Define o diretório para salvar a imagem
        $dir = __DIR__ . "/../../../files/offices/logo/{$office_id}/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true); // Cria o diretório se não existir
        }

        // Caminho completo da imagem
        $file_path = $dir . $file_name;
        $file_path_save = INCLUDE_PATH_DASHBOARD . "files/offices/logo/{$office_id}/" . $file_name;

        // Move o arquivo para o diretório
        if (move_uploaded_file($image['tmp_name'], $file_path)) {
            return $file_path_save;
        } else {
            throw new Exception("Erro ao salvar a imagem: {$image['name']}");
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "upload-office-logo") {
        if (isset($_POST['office_id'])) {
            $office_id = $_POST['office_id'];
            $image = $_FILES['logo'];

            try {
                // Verifica se a imagem foi enviada sem erros
                if (isset($image) && $image['error'] === UPLOAD_ERR_OK) {
                    // Chama a função para salvar a logo do escritório
                    $logo_path = saveOfficeLogo($office_id, $image);

                    // Atualiza o caminho da imagem no banco de dados
                    $stmt = $conn->prepare("UPDATE tb_offices SET logo = ? WHERE id = ?");
                    $stmt->execute([$logo_path, $office_id]);        

                    echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Logo do escritório atualizada com sucesso!']);

                    $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Logo do escritório atualizada com sucesso!');
                    $_SESSION['msg'] = $message;
                } else {
                    throw new Exception("Erro ao enviar a imagem.");
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Escritório não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }