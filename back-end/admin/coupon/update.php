<?php
session_start();
include('./../../../config.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "update-coupon") {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Verifica se o ID do cupom foi informado
        $couponId = $_POST['coupon_id'] ?? null;
        if (!$couponId) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID do cupom não informado.'
            ]);
            exit;
        }
        
        // Captura os dados do formulário
        $name          = $_POST['name'];
        $validity      = $_POST['validity'];
        $discount_type = $_POST['discount_type'];
        $code          = $_POST['code'];
        
        // Define o valor do desconto com base no tipo selecionado
        if ($discount_type === 'fixed') {
            $price = trim($_POST['price']);
            // Remove os pontos e substitui a vírgula pelo ponto decimal
            $price = str_replace('.', '', $price);
            $discount_value = str_replace(',', '.', $price);
        } else {
            $discount_value = $_POST['percent'];
        }
        
        try {
            if (!$conn) {
                throw new Exception("Conexão inválida com o banco de dados.");
            }

            // Verifica se e um administrador
            $permission = getUserPermission($user_id, $conn);
            if ($permission['role'] !== 0) {
                echo json_encode(['status' => 'error', 'message' => 'Usuário não tem permissão.']);
                exit;
            }
            
            // Verifica se o código do cupom é único (excluindo o cupom atual)
            $stmt = $conn->prepare("SELECT id FROM tb_coupons WHERE code = ? AND id != ?");
            $stmt->execute([$code, $couponId]);
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'O código do cupom já existe. Por favor, utilize outro.'
                ]);
                exit;
            }
            
            // Inicia a transação
            $conn->beginTransaction();
            
            // Atualiza os dados do cupom na tabela tb_coupons
            $stmt = $conn->prepare("
                UPDATE tb_coupons SET
                    name = ?,
                    validity = ?,
                    discount_type = ?,
                    discount_value = ?,
                    code = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $validity, $discount_type, $discount_value, $code, $couponId]);
            
            // Commit na transação
            $conn->commit();
            
            echo json_encode([
                'status'  => 'success',
                'message' => 'Cupom atualizado com sucesso.'
            ]);
            
            $message = array(
                'status'  => 'success',
                'alert'   => 'primary',
                'title'   => 'Sucesso',
                'message' => 'Cupom atualizado com sucesso.'
            );
            $_SESSION['msg'] = $message;
        } catch (Exception $e) {
            // Em caso de erro, realiza rollback
            $conn->rollBack();
            error_log("Erro ao atualizar o cupom: " . $e->getMessage());
            echo json_encode([
                'status'  => 'error',
                'message' => 'Erro ao atualizar o cupom.',
                'error'   => $e->getMessage()
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Usuário não autenticado.'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Método de requisição inválido.'
    ]);
    exit;
}