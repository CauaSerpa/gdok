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

            // Verifica se o tipo de documento está em uso na tabela tb_documents
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count, name FROM tb_documents WHERE document_type_id = ?");
            $checkStmt->execute([$id]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                echo json_encode(['status' => 'error']);
                $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'O tipo de documento "' . $result['name'] . '" está em uso. Não pode ser excluído.');
                $_SESSION['msg'] = $message;
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM tb_document_types WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount()) {
                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Tipo de documento deletado com sucesso.');
                $_SESSION['msg'] = $message;
            } else {
                // Rollback em caso de falha
                $conn->rollBack();

                echo json_encode(['status' => 'error']);

                $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Tipo de documento não encontrado.');
                $_SESSION['msg'] = $message;
            }
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();

            // Log do erro
            error_log("Erro ao deletar tipo de documento: " . $e->getMessage());

            // Retorno de erro
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            $_SESSION['msg'] = [
                'status' => 'error',
                'alert' => 'danger',
                'title' => 'Erro',
                'message' => 'Erro ao deletar tipo de documento: ' . $e->getMessage()
            ];
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido ou ID não fornecido.']);
    }