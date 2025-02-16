<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update") {
        if (isset($_SESSION['user_id'])) {
            $cep = $_POST['cep'];
            $address = $_POST['address'];
            $number = !isset($_POST['noNumber']) ? $_POST['number'] : 0;
            $province = $_POST['province'];
            $complement = $_POST['complement'];
            $city = $_POST['city'];
            $state = $_POST['state'];
            $id = $_POST['office_address_id'];

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                $stmt = $conn->prepare("
                    UPDATE tb_office_addresses SET
                    cep = ?, address = ?, number = ?, province = ? , complement = ? , city = ?, state = ?
                    WHERE id = ?
                ");
                $stmt->execute([$cep, $address, $number, $province, $complement, $city, $state, $id]);

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Endereço do Escritório atualizado com sucesso.']);

                $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Endereço do Escritório atualizado com sucesso.');
                $_SESSION['msg'] = $message;
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao atualizar o endereço do escritório: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o endereço do escritório', 'error' => $e->getMessage()]);
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