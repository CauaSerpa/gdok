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
    define('INCLUDE_PATH_PORTAL', $_ENV['URL'] . "portal/");



    // Definir o nome do projeto
	$project = [
        'name' => $_ENV['PROJECT_NAME'],
        'version' => $_ENV['PROJECT_VERSION'] ?? '1.0'
    ];

    // Definir o fuso horário para o Brasil
	$default_timezone = $_ENV['DEFAULT_TIMEZONE'];
    date_default_timezone_set($default_timezone);

    // Tamanho maximo de arquivo
	$max_file_size = $_ENV['MAX_FILE_SIZE'];

    // Evolution
	$config['evolution_url'] = $_ENV['EVOLUTION_URL'];
	$config['evolution_instance'] = $_ENV['EVOLUTION_INSTANCE'];
	$config['evolution_apikey'] = $_ENV['EVOLUTION_APIKEY'];


    // Incluir codigo de funcionalidades
    include('back-end/utility-functions/mail.php');
    include('back-end/utility-functions/whatsapp.php');


    // Permissao do usuario
    function getUserPermission($user_id, $conn) {
        $sql = "SELECT role FROM tb_users WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);

        // Obter os resultados
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $timeAgo = time() - $timestamp;

        $seconds = $timeAgo;
        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);
        $weeks = round($seconds / 604800);
        $months = round($seconds / 2629440);
        $years = round($seconds / 31553280);

        if ($seconds <= 60) {
            return "agora mesmo";
        } elseif ($minutes <= 60) {
            return $minutes == 1 ? "há um minuto" : "há $minutes minutos";
        } elseif ($hours <= 24) {
            return $hours == 1 ? "há uma hora" : "há $hours horas";
        } elseif ($days <= 7) {
            return $days == 1 ? "há um dia" : "há $days dias";
        } elseif ($weeks <= 4) {
            return $weeks == 1 ? "há uma semana" : "há $weeks semanas";
        } elseif ($months <= 12) {
            return $months == 1 ? "há um mês" : "há $months meses";
        } else {
            return $years == 1 ? "há um ano" : "há $years anos";
        }
    }

    function getFileSize($path) {
        // Substituir espaços por %20
        $path = str_replace(' ', '%20', $path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            // URL remota -> usa cURL para pegar o tamanho do arquivo
            $ch = curl_init($path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            curl_exec($ch);
            
            $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
    
            if ($httpCode !== 200 || $fileSize < 0) {
                return 'Tamanho não disponível';
            }
        } else {
            // Arquivo local -> usa `filesize()`
            if (!file_exists($path)) {
                return 'Arquivo não encontrado';
            }
            $fileSize = filesize($path);
        }
    
        return formatFileSize($fileSize);
    }
    
    function formatFileSize($size) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
    
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
    
        return round($size, 0) . ' ' . $units[$i];
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