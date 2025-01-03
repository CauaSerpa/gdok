<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-company") {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $responsible = $_POST['responsible'];
            $document = $_POST['document'];
            $uf = $_POST['uf'];
            $cidade = $_POST['cidade'];

            try {
                // Iniciar transação
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Inserir dados da empresa
                $stmt = $conn->prepare("INSERT INTO tb_companies (user_id, name, phone, email, responsible, document, uf, cidade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $phone, $email, $responsible, $document, $uf, $cidade]);

                // Commit na transação
                $conn->commit();

                // Retorna um status de sucesso
                echo json_encode(['status' => 'success', 'message' => 'Empresa registrada com sucesso.']);

            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao registrar a empresa: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar a empresa', 'error' => $e->getMessage()]);
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