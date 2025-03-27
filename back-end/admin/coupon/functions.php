<?php
    function getCouponByToken($category_sending_id, $conn) {
        try {
            if (!$conn) {
                throw new Exception("Conexão inválida com o banco de dados.");
            }

            $stmt = $conn->prepare("SELECT * FROM tb_coupons WHERE id = ? LIMIT 1");
            $stmt->execute([$category_sending_id]);

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            // Exibir mensagem de erro (somente em ambiente de desenvolvimento)
            die("Erro no banco de dados: " . $e->getMessage());
        }
    }