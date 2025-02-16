<?php
    session_start();
    include('./../../../../config.php');

    header('Content-Type: application/json');

    function saveProfileImage($user_id, $image) {
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

        // Sanitize o nome da imagem
        $original_name = pathinfo($image['name'], PATHINFO_FILENAME);
        $sanitized_name = strtolower(str_replace(' ', '_', $original_name)); // Normaliza o nome
        $timestamp = date('Y-m-d_H-i-s'); // Tira timestamp para o nome único
        $file_name = "{$sanitized_name}_{$timestamp}.{$extension}";

        // Define o diretório para salvar a imagem
        $dir = __DIR__ . "/../../../../files/user/profile_image/{$user_id}/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true); // Cria o diretório se não existir
        }

        // Caminho completo da imagem
        $file_path = $dir . $file_name;
        $file_path_save = INCLUDE_PATH_DASHBOARD . "files/user/profile_image/{$user_id}/" . $file_name;

        // Move o arquivo para o diretório
        if (move_uploaded_file($image['tmp_name'], $file_path)) {
            return $file_path_save;
        } else {
            throw new Exception("Erro ao salvar a imagem: {$image['name']}");
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "upload-profile-image") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $image = $_FILES['profile_image'];

            try {
                // Verifica se a imagem foi enviada sem erros
                if (isset($image) && $image['error'] === UPLOAD_ERR_OK) {
                    // Chama a função para salvar a imagem de perfil
                    $image_path = saveProfileImage($user_id, $image);

                    // Atualiza o caminho da imagem no banco de dados
                    $stmt = $conn->prepare("UPDATE tb_users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$image_path, $user_id]);        

                    echo json_encode(['status' => 'success', 'message' => 'Imagem de perfil atualizada com sucesso!', 'image_path' => $image_path]);
                } else {
                    throw new Exception("Erro ao enviar a imagem.");
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
    }