<?php
    session_start();
    include('./../../../config.php'); // Inclua seu arquivo de configuração do banco

    if(isset($_POST['document_id'])){
        $documentId = $_POST['document_id'];
        $now = date('Y-m-d H:i:s');

        // Atualiza o documento marcando como visualizado e registrando o momento
        $stmt = $conn->prepare("UPDATE tb_sending_documents SET is_read = 1, read_in = ? WHERE id = ?");
        $stmt->execute([$now, $documentId]);

        if($stmt->rowCount() > 0){
            echo json_encode([
                'status' => 'success',
                'message' => 'Documento marcado como visualizado.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Não foi possível marcar o documento como visualizado ou ele já está marcado.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Documento não informado.'
        ]);
    }