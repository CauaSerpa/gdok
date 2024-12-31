<?php
    // Caso prefira o .env apenas descomente o codigo e comente o "include('parameters.php');" acima
	// Carrega as variáveis de ambiente do arquivo .env

    // Caminho para o diretório pai
    $parentDir = __DIR__;

	require $parentDir . '/vendor/autoload.php';
	$dotenv = Dotenv\Dotenv::createImmutable($parentDir);
	$dotenv->load();

	// Acessa as variáveis de ambiente
	$dbHost = $_ENV['DB_HOST'];
	$dbUser = $_ENV['DB_USER'];
	$dbPass = $_ENV['DB_PASS'];
	$dbName = $_ENV['DB_NAME'];
	$port = $_ENV['DB_PORT'];

    try{
        //Conexão com a porta
        $conn = new PDO("mysql:host=$dbHost;port=$port;dbname=" . $dbName, $dbUser, $dbPass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Conexão sem a porta
        //$conn = new PDO("mysql:host=$host;dbname=" . $dbname, $user, $pass);
        //echo "Conexão com banco de dados realizado com sucesso!";
    } catch (PDOException $e) {
        // Tratamento de erros
        echo 'Erro de conexão com o banco de dados: ' . $e->getMessage();
    }

    // Definir url principal
    // define('INCLUDE_PATH', $_ENV['URL']);
    define('INCLUDE_PATH_DASHBOARD', $_ENV['URL']);
    define('INCLUDE_PATH_AUTH', $_ENV['URL'] . "auth/");



    // Definir o nome do projeto
	$project['name'] = $_ENV['PROJECT_NAME'];

    // Definir o fuso horário para o Brasil
	$default_timezone = $_ENV['DEFAULT_TIMEZONE'];
    date_default_timezone_set($default_timezone);

    // Tamanho maximo de arquivo
	$max_file_size = $_ENV['MAX_FILE_SIZE'];



    // Incluir codigo de funcionalidades
    include('back-end/utility-functions/mail.php');



    // Mensagem de bom dia, boa tarde e boa noite
    function getGreeting() {
        $hour = date('H'); // Obtém a hora atual no formato de 24 horas (0-23)

        if ($hour >= 5 && $hour < 12) {
            return "Bom dia";
        } elseif ($hour >= 12 && $hour < 18) {
            return "Boa tarde";
        } else {
            return "Boa noite";
        }
    }


    // // Lógica de permissões
    // function hasPermission($userId, $permissionName, $conn) {
    //     $stmt = $conn->prepare("
    //         SELECT COUNT(*) 
    //         FROM tb_user_roles ur
    //         INNER JOIN tb_role_permissions rp ON ur.role_id = rp.role_id
    //         INNER JOIN tb_permissions p ON rp.permission_id = p.id
    //         WHERE ur.user_id = ? AND p.permission_name = ?
    //     ");
    //     $stmt->execute([$userId, $permissionName]);
    
    //     return $stmt->fetchColumn() > 0;
    // }

    // // Mapa das permissões
    // $permissionsMap = [
    //     'manage_users' => 'admin',
    //     'edit_content' => 'editor',
    //     'use_content' => 'user'
    // ];
?>