<?php $message = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verificação de Segurança</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    background-color: #ffffff;
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                    background-image: linear-gradient(to right,#287F71, #06162E) !important;
                }
                img.logo {
                    max-width: 150px;
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .email-body {
                    padding: 20px;
                    line-height: 1.6;
                    color: #333333;
                }
                .email-body h2 {
                    font-size: 20px;
                    margin-bottom: 10px;
                }
                .email-body p {
                    margin: 10px 0;
                }
                .button {
                    display: inline-block;
                    padding: .47rem .95rem;
                    font-size: 16px;
                    color: #fff;
                    background-color: #287F71;
                    text-decoration: none;
                    border-radius: 4px;
                }
                .email-footer {
                    text-align: center;
                    color: #777777;
                    font-size: 12px;
                    margin-top: 20px;
                }

                .small {
                    font-size: 0.875em;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <img src='" . INCLUDE_PATH_DASHBOARD . "assets/images/logo-light.png' class='logo'>
                </div>
                <div class='email-body'>
                    <h2>Olá " . htmlspecialchars($content['content']['firstname'], ENT_QUOTES, 'UTF-8') . ",</h2>
                    <p>O escritório " . htmlspecialchars($content['content']['office'], ENT_QUOTES, 'UTF-8') . " adicionou um documento associado à sua empresa.</p>

                    <h4>Detalhes do Documento</h4>
                    <p class='small'><strong>Nome do Documento:</strong> " . htmlspecialchars($content['content']['document']['name'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='small'><strong>Departamento:</strong> " . htmlspecialchars($content['content']['document']['department'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='small'><strong>Categoria:</strong> " . htmlspecialchars($content['content']['document']['category'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='small'><strong>Empresa:</strong> " . htmlspecialchars($content['content']['document']['company'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='small'><strong>Referência:</strong> " . htmlspecialchars($content['content']['document']['reference'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='small'><strong>Data de Vencimento:</strong> " . htmlspecialchars($content['content']['document']['expiration_date'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p class='small'><strong>Referência:</strong> " . htmlspecialchars($content['content']['document']['price'], ENT_QUOTES, 'UTF-8') . "</p>

                    <p>Para visualizar esse documento, clique no botão abaixo:</p>
                    <a href='" . htmlspecialchars($content['content']['link'], ENT_QUOTES, 'UTF-8') . "' class='button'>Visualizar Documento</a>
                    <p>Obrigado por usar o " . htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') . "!</p>
                </div>
                <div class='email-footer'>
                    <p>Este é um e-mail automático. Por favor, não responda.</p>
                    <p>Direitos autorais &copy; " . htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') . " " . date('Y') . "</p>
                </div>
            </div>
        </body>
        </html>
    ";
?>