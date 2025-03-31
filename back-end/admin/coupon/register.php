<?php
    session_start();
    include('./../../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "register-coupon") {
        if (isset($_SESSION['user_id'])) {
            // Captura os dados do formulário
            $user_id           = $_SESSION['user_id'];
            $name              = $_POST['name'];
            $validity_start    = $_POST['validity_start'];
            $validity_end      = $_POST['validity_end'];
            $discount_type     = $_POST['discount_type'];
            $code              = $_POST['code'];
            $accessibleModules = isset($_POST['accessibleModules']) ? $_POST['accessibleModules'] : [];

            // Converte o array de módulos para JSON para armazenamento
            $accessibleModulesJson = json_encode($accessibleModules);

            // Define o valor do desconto com base no tipo selecionado
            if ($discount_type === 'fixed') {

                $price = trim($_POST['price']);
                $price = str_replace('.', '', $price);
                $discount_value = str_replace(',', '.', $price);

            } else {

                $percent = trim($_POST['percent']);
                $discount_value = str_replace(',', '.', $percent);

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

                // Verifica se o código do cupom é único
                $stmt = $conn->prepare("SELECT id FROM tb_coupons WHERE code = ?");
                $stmt->execute([$code]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'O código do cupom já existe. Por favor, utilize outro.'
                    ]);
                    exit;
                }

                // Inicia a transação
                $conn->beginTransaction();

                // Insere os dados do cupom na tabela tb_coupons
                $stmt = $conn->prepare("
                    INSERT INTO tb_coupons 
                    (name, validity_start, validity_end, discount_type, discount_value, code, accessible_modules, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$name, $validity_start, $validity_end, $discount_type, $discount_value, $code, $accessibleModulesJson,]);

                // Commit na transação
                $conn->commit();

                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Cupom registrado com sucesso.'
                ]);

                $message = array(
                    'status'  => 'success',
                    'alert'   => 'primary',
                    'title'   => 'Sucesso',
                    'message' => 'Cupom registrado com sucesso.'
                );
                $_SESSION['msg'] = $message;

            } catch (Exception $e) {
                // Em caso de erro, realiza rollback
                $conn->rollBack();
                error_log("Erro ao registrar o cupom: " . $e->getMessage());
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Erro ao registrar o cupom',
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