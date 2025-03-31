<?php $message = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmação de Alteração de E-mail</title>
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
                    background-image: linear-gradient(to right, #287F71, #06162E);
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
                    padding: 12px 24px;
                    font-size: 16px;
                    color: #ffffff;
                    background-color: #287F71;
                    text-decoration: none;
                    border-radius: 4px;
                    font-weight: bold;
                }
                .button:hover {
                    background-color: #225E55;
                }
                .email-footer {
                    text-align: center;
                    color: #777777;
                    font-size: 12px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <img src='" . INCLUDE_PATH_DASHBOARD . "assets/images/logo-light.png' class='logo' alt='Logo'>
                </div>
                <div class='email-body'>
                    <h2>Olá, " . htmlspecialchars($content['content']['firstname'], ENT_QUOTES, 'UTF-8') . "!</h2>
                    <p>Informamos que a senha da sua conta em <strong>" . htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') . "</strong> foi redefinida por um dos administradores.</p>
                    <p>Para acessar sua conta, utilize as credenciais abaixo:</p>
                    <p><strong>E-mail:</strong> " . htmlspecialchars($content['content']['email'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p><strong>Senha:</strong> " . htmlspecialchars($content['content']['password'], ENT_QUOTES, 'UTF-8') . "</p>
                    <p>Se você precisa de suporte, entre em contato com nossa equipe imediatamente.</p>
                </div>
                <div class='email-footer'>
                    <p>Este é um e-mail automático. Não responda a esta mensagem.</p>
                    <p>&copy; " . htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') . " " . date('Y') . " - Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>
    ";
?>