<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
        $id = $_GET['id'];

        try {
            if (!$conn) {
                throw new Exception("Conexão inválida com o banco de dados.");
            }

            // Inicia uma transação
            $conn->beginTransaction();

            // Obtém o caminho do documento antes de deletar do banco
            $stmt = $conn->prepare("SELECT id FROM tb_sending_documents WHERE id = ?");
            $stmt->execute([$id]);
            $document = $stmt->fetch();

            if ($document) {
                // Deleta a entrada no banco de dados
                $stmt = $conn->prepare("DELETE FROM tb_sending_documents WHERE id = ?");
                $stmt->execute([$id]);

                // Verifica se a exclusão foi bem-sucedida
                if ($stmt->rowCount()) {
                    // Caminho da pasta do documento
                    $directory = __DIR__ . "/../../../files/documents-sending/{$id}";

                    // Função para excluir a pasta e seus arquivos
                    function deleteFolder($folderPath) {
                        if (!is_dir($folderPath)) {
                            return;
                        }
                        $files = array_diff(scandir($folderPath), ['.', '..']);
                        foreach ($files as $file) {
                            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
                            if (is_dir($filePath)) {
                                deleteFolder($filePath);
                            } else {
                                unlink($filePath);
                            }
                        }
                        rmdir($folderPath);
                    }

                    // Deleta a pasta
                    deleteFolder($directory);

                    // Commit na transação
                    $conn->commit();

                    // Retorno de sucesso
                    echo json_encode(['status' => 'success', 'message' => 'Documento de envio e pasta deletados com sucesso.']);
                    $_SESSION['msg'] = [
                        'status' => 'success',
                        'alert' => 'primary',
                        'title' => 'Sucesso',
                        'message' => 'Documento de envio e pasta deletados com sucesso.'
                    ];
                } else {
                    // Rollback em caso de falha
                    $conn->rollBack();
                    throw new Exception("Falha ao deletar o documento de envio no banco de dados.");
                }
            } else {
                throw new Exception("Documento de envio não encontrado.");
            }
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();

            // Log do erro
            error_log("Erro ao deletar documento de envio: " . $e->getMessage());

            // Retorno de erro
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            $_SESSION['msg'] = [
                'status' => 'error',
                'alert' => 'danger',
                'title' => 'Erro',
                'message' => 'Erro ao deletar documento de envio: ' . $e->getMessage()
            ];
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido ou ID não fornecido.']);
    }