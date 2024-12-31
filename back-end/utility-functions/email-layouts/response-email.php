<?php $message = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    background-image: linear-gradient(to right, #434343 0%, black 100%) !important;
                }
                img.logo {
                    max-width: 200px; /* Ajuste o tamanho do logotipo conforme necessário */
                    height: 40px; /* Ajuste o tamanho do logotipo conforme necessário */
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
                .email-body .code {
                    display: block;
                    background-color: #f4f4f4;
                    padding: 10px;
                    text-align: center;
                    font-size: 18px;
                    color: #333333;
                    border-radius: 4px;
                    margin: 20px 0;
                    font-weight: bold;
                }
                blockquote {
                    border-left: 3px solid #28a745;
                    margin: 10px 0;
                    padding-left: 14px;
                    font-style: italic;
                    color: #555;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    font-size: 16px;
                    color: #fff;
                    background-color: #007BFF;
                    text-decoration: none;
                    border-radius: 4px;
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
            <div class="email-container">
                <div class="email-header">
                    <img src="https://rendacap.com.br/app/images/logo.png" alt="Logo ' . $project_name . '" class="logo" />
                </div>
                <div class="email-body">
                    <h2>Olá ' . $content["content"]["firstname"] . ',</h2>
                    <p>Os administradores enviaram uma resposta para a sua dúvida. Abaixo estão os detalhes:</p>
                    <h4>Sua dúvida:</h4>
                    <blockquote>
                        ' . nl2br(htmlspecialchars($content['content']['message'])) . '
                    </blockquote>
                    <p><b>Enviada em:</b> ' . $content["content"]["sent_in"] . '</p>
                    <h4>Resposta dos administradores:</h4>
                    <blockquote>
                        ' . nl2br(htmlspecialchars($content['content']['response'])) . '
                    </blockquote>
                    <p>Esperamos que essa resposta tenha solucionado a sua dúvida.<br>Caso ainda tenha perguntas, fique à vontade para responder a este e-mail com mais detalhes. Por favor, tente ser o mais claro e objetivo possível para que possamos ajudá-lo da melhor forma.</p>
                </div>
                <div class="email-footer">
                    <p>Este é um e-mail automático.</p>
                    <p>Direitos autorais &copy; ' . $project_name . date("Y") . '</p>
                </div>
            </div>
        </body>
        </html>
    ';
?>