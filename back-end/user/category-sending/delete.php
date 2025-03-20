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

            // // Verifica se a categoria está em uso na tabela tb_document_types
            // $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tb_document_types WHERE category_id = ?");
            // $checkStmt->execute([$id]);
            // $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            // if ($result['count'] > 0) {
            //     echo json_encode(['status' => 'error']);
            //     $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Esta categoria de envio está em uso. Não pode ser excluída.');
            //     $_SESSION['msg'] = $message;
            //     exit;
            // }

            // Executa a exclusão da categoria de envio
            $stmt = $conn->prepare("DELETE FROM tb_sending_categories WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount()) {
                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Categoria de envio deletada com sucesso.');
                $_SESSION['msg'] = $message;
            } else {
                // Rollback em caso de falha
                $conn->rollBack();
                echo json_encode(['status' => 'error']);

                $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Categoria de envio não encontrada.');
                $_SESSION['msg'] = $message;
            }
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();

            // Log do erro
            error_log("Erro ao deletar a categoria de envio: " . $e->getMessage());

            // Retorno de erro
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            $_SESSION['msg'] = [
                'status' => 'error',
                'alert' => 'danger',
                'title' => 'Erro',
                'message' => 'Erro ao deletar a categoria de envio: ' . $e->getMessage()
            ];
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido ou ID não fornecido.']);
    }