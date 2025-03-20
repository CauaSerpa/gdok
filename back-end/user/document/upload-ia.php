<?php
session_start();
include('./../../../config.php');

// Ramificação para salvar o documento (ação "confirm")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'confirm') {
    // Recupera os dados enviados via POST
    $empresa                = $_POST['empresaOption'];
    $tipoDocumento          = $_POST['tipoDocumentoOption'];
    $endereco               = $_POST['endereco'];
    $orgao                  = $_POST['orgao'];
    $nomeAlvara             = $_POST['nomeAlvara'];
    $vencimento             = $_POST['vencimento'];   // no formato ISO (yyyy-mm-dd)
    $deferimento            = $_POST['deferimento'];  // no formato ISO (yyyy-mm-dd)
    $uploadedFileName       = $_POST['uploadedFileName'];
    $advance_notification   = 7;

    if (!empty($tipoDocumento)) {
        // Consulta para buscar tipos de documentos cadastradas
        $stmt = $conn->prepare("SELECT id, name, advance_notification, personalized_advance_notification FROM tb_document_types WHERE id = ? LIMIT 1");
        $stmt->execute([$tipoDocumento]);
        $document_type = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($document_type) {
            $advance_notification = ($advance_notification == 'personalized') ? $personalized_advance_notification : (!empty($advance_notification) ? $advance_notification : 7);
        }
    }

    // Insere os dados na tabela tb_documents (ajuste os nomes dos campos conforme sua tabela)
    $stmt = $conn->prepare("INSERT INTO tb_documents (user_id, company_id, document_type_id, name, advance_notification, expiration_date) VALUES (?, ?, ?, ?, ?, ?)");
    // Inicialmente, insere o campo file_path como vazio; ele será atualizado após mover o arquivo
    $stmt->execute([$_SESSION['user_id'], $empresa, $tipoDocumento, $nomeAlvara, $advance_notification, $vencimento]);
    $documentId = $conn->lastInsertId();

    // Cria a pasta destino: /files/documents/{documentId}/
    $newDir = __DIR__ . '/../../../files/documents/' . $documentId . '/';
    if (!is_dir($newDir)) {
        mkdir($newDir, 0777, true);
    }
    $sourceFile = __DIR__ . '/uploads/' . $uploadedFileName;
    $destFile   = $newDir . $uploadedFileName;
    if (file_exists($sourceFile)) {
        if (!rename($sourceFile, $destFile)) {
            echo json_encode(['error' => 'Erro ao mover o arquivo.']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Arquivo não encontrado.']);
        exit;
    }
    // Atualiza o campo file_path com o caminho relativo (ajuste conforme sua estrutura)
    $relativePath = INCLUDE_PATH_DASHBOARD . "files/documents/{$documentId}/" . $uploadedFileName;
    $stmtUpdate = $conn->prepare("UPDATE tb_documents SET document = ? WHERE id = ?");
    $stmtUpdate->execute([$relativePath, $documentId]);

    echo json_encode(['status' => 'success', 'documentId' => $documentId]);

    $message = array('status' => 'success', 'alert' => 'primary', 'title' => 'Sucesso', 'message' => 'Documento salvo com sucesso.');
    $_SESSION['msg'] = $message;
    exit;
}

// Ramo para processamento do upload e extração do documento via IA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {

    // Função para extrair texto do arquivo usando a API OCR.space.
    function extract_text_via_ocr($file_path) {
        // Obtém a chave da API do .env
        $ocr_api_key = $_ENV['OCR_API_KEY'];
        $url = 'https://api.ocr.space/parse/image';

        $post_fields = [
            'apikey' => $ocr_api_key,
            'language' => 'por',
            'isOverlayRequired' => 'false',
            'file' => new CURLFile($file_path)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Erro no cURL: ' . curl_error($ch);
        }
        curl_close($ch);

        $result_json = json_decode($response, true);
        if (isset($result_json['ParsedResults'][0]['ParsedText'])) {
            return $result_json['ParsedResults'][0]['ParsedText'];
        } else {
            return 'Erro na extração de texto via OCR.';
        }
    }

    // Função que extrai as informações do documento usando a API do ChatGPT.
    function extract_document_info($file_path) {
        global $conn; // Conexão com o banco de dados, definida no config.php
        // Obtém a chave da API do OpenAI a partir do .env
        $api_key = $_ENV['OPENAI_API_KEY'];
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        // Obtém a extensão do arquivo
        $file_info = pathinfo($file_path);
        $extension = strtolower($file_info['extension']);
        
        // Para PDF, usa o pdfparser; para imagens, usa a API OCR para extrair o texto
        if ($extension === 'pdf') {
            // Certifique-se de ter instalado a biblioteca smalot/pdfparser via Composer
            require_once './../../../vendor/autoload.php';
            $parser = new \Smalot\PdfParser\Parser();
            try {
                $pdf = $parser->parseFile($file_path);
                $document_text = $pdf->getText();
            } catch (Exception $e) {
                echo "Erro ao ler o PDF: " . $e->getMessage();
                exit;
            }
        } elseif (in_array($extension, ['png', 'jpeg', 'jpg'])) {
            $document_text = extract_text_via_ocr($file_path);
        } else {
            echo "Formato de arquivo não suportado.";
            exit;
        }

        $document_text = mb_convert_encoding($document_text, 'UTF-8', 'auto');

        // Prompt atualizado: o campo "endereco" deve ser retornado como string simples (todas as informações concatenadas)
        $prompt = "Extraia apenas as informações relevantes do documento abaixo e retorne somente os dados solicitados, sem explicações adicionais.
                Forneça a resposta em formato JSON com as seguintes chaves:
                - \"paraQuem\": para quem o alvará foi emitido,
                - \"endereco\": o endereço para o qual o alvará foi emitido, retornado como texto simples (string) com todas as informações concatenadas,
                - \"orgao\": o órgão que emitiu o alvará,
                - \"nomeAlvara\": o nome do alvará,
                - \"dataVencimento\": a data de vencimento ou validade do alvará,
                - \"dataDeferimento\": a data de deferimento.
                Atenção: Sempre retorne as datas no padrão brasileiro (dd/mm/aaaa).
                Texto do documento:
                " . $document_text;

        $data = [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um assistente que extrai informações de documentos.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($json_data === false) {
            die('Erro na conversão do JSON: ' . json_last_error_msg());
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Erro no cURL: ' . curl_error($ch);
        }
        curl_close($ch);

        $response_data = json_decode($response, true);
        if (isset($response_data['error'])) {
            echo 'Erro na API: ' . $response_data['error']['message'];
            exit;
        }
        
        $response_str = $response_data['choices'][0]['message']['content'] ?? 'Erro na extração';

        // Tenta decodificar a resposta como JSON
        $extractedData = json_decode($response_str, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Se não for JSON válido, retorna a resposta original
            echo $response_str;
            return;
        }
        
        // Verifica se a chave 'paraQuem' existe para obter o nome da empresa
        if (isset($extractedData['paraQuem'])) {
            $companyName = trim($extractedData['paraQuem']);
            // Consulta na tabela tb_companies para verificar se a empresa já está cadastrada
            $stmt = $conn->prepare("SELECT id FROM tb_companies WHERE name = ? LIMIT 1");
            $stmt->execute([$companyName]);
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $extractedData['empresaId'] = $row['id'];
                $extractedData['empresaExist'] = 1;
            } else {
                $extractedData['empresaExist'] = 0;
            }
        }
        
        echo json_encode($extractedData, JSON_UNESCAPED_UNICODE);
    }

    // Cria o diretório de upload se não existir
    $upload_dir = './../../../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_path = $upload_dir . basename($_FILES['document']['name']);
    
    if (move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
        extract_document_info($file_path);
    } else {
        echo 'Erro ao fazer upload do arquivo';
    }
    exit;
}
?>