<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-document-type") {
        if (isset($_SESSION['user_id'])) {
            $document_type_id = $_POST['document_type_id'];
            $user_id = $_SESSION['user_id'];
            $name = $_POST['name'];
            $category = $_POST['category'];
            $priority = $_POST['priority'];
            $advance_notification = $_POST['advance_notification'];
            $personalized_advance_notification = ($_POST['advance_notification'] === 'personalized') ? $_POST['personalized_advance_notification'] : null;

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    UPDATE tb_document_types SET
                    name = ?, category_id = ?, priority = ?, advance_notification = ?, personalized_advance_notification = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$name, $category, $priority, $advance_notification, $personalized_advance_notification, $document_type_id, $_SESSION['user_id']]);

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Tipo de documento atualizado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Tipo de documento atualizado com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao atualizar o tipo de documento: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o tipo de documento', 'error' => $e->getMessage()]);
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