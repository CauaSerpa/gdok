<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-category") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];

            try {
                // Iniciar transação
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Inserir dados da categoria
                $stmt = $conn->prepare("INSERT INTO tb_document_type_categories (user_id, name, description) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $name, $description]);

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success', 'message' => 'Categoria registrada com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Categoria registrada com sucesso.');
                $_SESSION['msg'] = $message;

            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao registrar a categoria: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar a categoria', 'error' => $e->getMessage()]);
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