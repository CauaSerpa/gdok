<?php
    session_start();
    include('./../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-profile") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $document = $_POST['document'];
            $phone = $_POST['phone'];

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    UPDATE tb_users SET
                    firstname = ?, lastname = ?, document = ?, phone = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$firstname, $lastname, $document, $phone, $_SESSION['user_id']]);

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Perfil atualizado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Perfil atualizado com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao atualizar o Perfil: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o perfil', 'error' => $e->getMessage()]);
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