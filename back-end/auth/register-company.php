<?php
    session_start();
    include('./../../config.php');

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "register") {

        // Coleta de dados 
        $name = $_POST['name'];
        $document = $_POST['document'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $cep = $_POST['cep'];
        $address = $_POST['address'];
        $number = !isset($_POST['noNumber']) ? $_POST['number'] : 0;
        $province = $_POST['province'];
        $complement = $_POST['complement'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $agree = isset($_POST['agree']) ? $_POST['agree'] : null;

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

            // Consulta o usuário pelo e-mail
            $stmt = $conn->prepare("SELECT id, firstname FROM tb_users WHERE id = ?");
            $stmt->execute([$_SESSION['finalize_registration_user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'Faça login para continuar o cadastro da sua empresa.']);
                exit();
            }

            // Verificar se o document já está registrado
            $checkDocumentStmt = $conn->prepare("SELECT COUNT(*) FROM tb_offices WHERE document = ?");
            $checkDocumentStmt->execute([$document]);
            $documentExists = $checkDocumentStmt->fetchColumn();

            if ($documentExists > 0) {
                echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'O Documento informado já está registrado.']);
                exit;
            }

            // Verificar se o email já está registrado
            $checkEmailStmt = $conn->prepare("SELECT COUNT(*) FROM tb_offices WHERE email = ?");
            $checkEmailStmt->execute([$email]);
            $emailExists = $checkEmailStmt->fetchColumn();

            if ($emailExists > 0) {
                echo json_encode(['status' => 'error', 'alert' => 'danger', 'message' => 'O E-mail informado já está registrado.']);
                exit;
            }

            // Iniciar transação
            $conn->beginTransaction();

            // Inserir dados da empresa
            $stmt = $conn->prepare("INSERT INTO tb_offices (name, document, phone, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $document, $phone, $email]);
            $office_id = $conn->lastInsertId();

            // Inserir endereco da empresa
            $stmt = $conn->prepare("INSERT INTO tb_office_addresses (office_id, cep, address, number, province, complement, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$office_id, $cep, $address, $number, $province, $complement, $city, $state]);

            // Associar o usuário ao escritório como 'owner'
            $stmt = $conn->prepare("INSERT INTO tb_office_users (office_id, user_id, role) VALUES (?, ?, ?)");
            $stmt->execute([$office_id, $user['id'], 'owner']);

            // Enviar e-mail de boas vindas
            $subject = "Seu Escritório $name foi registrado com sucesso em " . $project['name'];
            $content = array("layout" => "registered-office", "content" => array("firstname" => $user['firstname'], "name" => $name));
            sendMail($name, $email, $subject, $content);

            // Armazena o informacoes em uma session
            $_SESSION['office_id'] = $office_id;
            $_SESSION['user_id'] = $_SESSION['finalize_registration_user_id'];
            $_SESSION['email'] = $_SESSION['finalize_registration_email'];

            unset($_SESSION['finalize_registration_user_id']);
            unset($_SESSION['finalize_registration_email']);

            // Commit na transação
            $conn->commit();

            // Retorna um status de sucesso
            echo json_encode(['status' => 'success']);

            // Defina a mensagem de sucesso na sessão
            $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Escritório registrado com sucesso! Você já pode utilizar o ' . $project['name'] . '.');
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