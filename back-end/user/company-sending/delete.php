<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
        $id = $_GET['id'];

        // Conecte ao banco e execute a exclusão
        try {
            $stmt = $conn->prepare("DELETE FROM tb_sending_companies WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount()) {
                echo json_encode(['status' => 'success']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Empresa de envio deletada com sucesso.');
                $_SESSION['msg'] = $message;
            } else {
                echo json_encode(['status' => 'error']);

                $message = array('status' => 'error', 'alert' => 'danger', 'title' => 'Error', 'message' => 'Empresa de envio não encontrado.');
                $_SESSION['msg'] = $message;
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }