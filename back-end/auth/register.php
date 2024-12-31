<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    function generateToken($length = 50) {
        return bin2hex(random_bytes($length));
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "register") {

        // Coleta de dados 
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $document = $_POST['document'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $agree = isset($_POST['agree']) ? $_POST['agree'] : null;
        $active_token = generateToken();

        // Validação se o campo 'agree' está marcado
        if (!$agree) {
            echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'Você deve concordar com os termos para continuar.']);
            exit;
        }

        try {
            // Iniciar transação
            if (!$conn) {
                throw new Exception("Conexão inválida com o banco de dados.");
            }

            // Verificar se o email já está registrado
            $checkEmailStmt = $conn->prepare("SELECT COUNT(*) FROM tb_users WHERE email = ?");
            $checkEmailStmt->execute([$email]);
            $emailExists = $checkEmailStmt->fetchColumn();

            if ($emailExists > 0) {
                echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'O email informado já está registrado.']);
                exit;
            }

            // Iniciar transação
            $conn->beginTransaction();

            // Inserir dados do usuário
            $stmt = $conn->prepare("INSERT INTO tb_users (firstname, lastname, email, phone, document, password, active_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$firstname, $lastname, $email, $phone, $document, $password, $active_token]);
            $user_id = $conn->lastInsertId();

            // Enviar e-mail de verificação
            $verification_link = INCLUDE_PATH_AUTH . "confirmar-email/" . $active_token;
            $subject = "Bem-vindo ao " . $project['name'];
            $content = array("layout" => "verify-email", "content" => array("firstname" => $firstname, "link" => $verification_link));
            sendMail($firstname, $email, $subject, $content);

            // Armazena o informacoes em uma session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;

            // Commit na transação
            $conn->commit();

            // Retorna um status de sucesso
            echo json_encode(['status' => 'success']);

            // Defina a mensagem de sucesso na sessão
            $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Registro realizado com sucesso! Por favor, verifique seu e-mail.');
            $_SESSION['msg'] = $message;
        } catch (Exception $e) {
            // Rollback em caso de erro
            $conn->rollBack();

            // Registrar erro em um log
            error_log("Erro no registro do usuário: " . $e->getMessage());

            // Mensagem genérica para o usuário
            echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'Ocorreu um erro ao processar seu registro. Tente novamente mais tarde.', 'error' => $e->getMessage()]);
        }

        $stmt = null;
        $conn = null;
        exit;
    } else {
        echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'Método de requisição inválido.']);
        exit;
    }
?>