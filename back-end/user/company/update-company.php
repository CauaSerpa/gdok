<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

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

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    UPDATE tb_companies SET
                    name = ?, phone = ?, notify_phone = ?, email = ?, notify_email = ?, responsible = ?, document = ?, uf = ?, cidade = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$name, $phone, $notify_phone, $email, $notify_email, $responsible, $document, $uf, $cidade, $company_id, $_SESSION['user_id']]);

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