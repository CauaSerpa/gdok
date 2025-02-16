<?php
    session_start();
    include('./../../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update") {
        if (isset($_SESSION['user_id'])) {
            $id = $_POST['office_id'];
            $channel = 'whatsapp';
            $is_active = ($_POST['notify_whatsapp'] == 'on') ? true : false;
            $contact = $_POST['office_whatsapp'];
            $send_type = $_POST['whatsapp_frequency'];
            $start_days_before = isset($_POST['before_expiration']) ? $_POST['frequency_before_expiration'] : null;
            $after_due_days = isset($_POST['after_expiration']) ? $_POST['days_after_expiration'] : null;

            try {
                if (!$conn) {
                    throw new Exception("Conexão inválida com o banco de dados.");
                }

                // Iniciar transação
                $conn->beginTransaction();

                // Verificar se já existe um registro para este office_id
                $stmt = $conn->prepare("SELECT COUNT(*) FROM tb_office_notification_settings WHERE office_id = ? AND channel = 'whatsapp'");
                $stmt->execute([$id]);
                $exists = $stmt->fetchColumn();

                if ($exists) {
                    // Atualizar registro existente
                    $stmt = $conn->prepare("UPDATE tb_office_notification_settings SET 
                        is_active = ?, 
                        contact = ?, 
                        send_type = ?, 
                        start_days_before = ?, 
                        after_due_days = ?
                        WHERE office_id = ? AND channel = 'whatsapp'");
                    $stmt->execute([$is_active, $contact, $send_type, $start_days_before, $after_due_days, $id]);
                } else {
                    // Inserir novo registro
                    $stmt = $conn->prepare("INSERT INTO tb_office_notification_settings (office_id, channel, is_active, contact, send_type, start_days_before, after_due_days) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $channel, $is_active, $contact, $send_type, $start_days_before, $after_due_days]);
                }

                // Commit na transação
                $conn->commit();

                echo json_encode(['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Parametrizações do E-mail do Escritório atualizado com sucesso.']);
                $_SESSION['msg'] = ['status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Parametrizações do E-mail do Escritório atualizado com sucesso.'];
            } catch (Exception $e) {
                // Rollback em caso de erro
                $conn->rollBack();

                // Registrar erro em um log
                error_log("Erro ao salvar as parametrizações do e-mail do escritório: " . $e->getMessage());

                // Mensagem genérica para o usuário
                echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar as parametrizações do e-mail escritório', 'error' => $e->getMessage()]);
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