<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
        $id = $_GET['id'];

        // Conecte ao banco e execute a exclusão
        try {
            if (!$conn) {
                throw new Exception("Conexão inválida com o banco de dados.");
            }

            // Inicia uma transação
            $conn->beginTransaction();

            // Verifica se o depto está em uso na tabela tb_sending_documents
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tb_sending_categories WHERE department_id = ?");
            $checkStmt->execute([$id]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                echo json_encode(['status' => 'error']);
                $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Este depto de envio está em uso em uma categoria de envio. Não pode ser excluído.');
                $_SESSION['msg'] = $message;
                exit;
            }

            // Executa a exclusão do depto de envio
            $stmt = $conn->prepare("DELETE FROM tb_sending_departments WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount()) {
                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Depto de envio deletado com sucesso.');
                $_SESSION['msg'] = $message;
            } else {
                // Rollback em caso de falha
                $conn->rollBack();
                echo json_encode(['status' => 'error']);

                $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Depto de envio não encontrado.');
                $_SESSION['msg'] = $message;
            }
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();

            // Log do erro
            error_log("Erro ao deletar o depto de envio: " . $e->getMessage());

            // Retorno de erro
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            $_SESSION['msg'] = [
                'status' => 'error',
                'alert' => 'danger',
                'title' => 'Erro',
                'message' => 'Erro ao deletar o depto de envio: ' . $e->getMessage()
            ];
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido ou ID não fornecido.']);
    }