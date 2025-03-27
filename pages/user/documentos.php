<style>
    .w-200 {
        width: 200px !important;
    }

    #documentsTable_paginate .pagination {
        display: none;
    }
    .dataTables_empty {
        text-align: center;
    }
</style>

<?php
    // Consulta para buscar empresas cadastradas
    $stmt = $conn->prepare("SELECT id, name FROM tb_companies WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar tipos de documentos cadastradas
    $stmt = $conn->prepare("SELECT id, name, advance_notification, personalized_advance_notification FROM tb_document_types WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $document_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Offcanvas de Filtro -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="filterForm">
            <div class="mb-3">
                <label for="companyFilter" class="form-label">Empresa</label>
                <select id="companyFilter" class="form-select">
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= htmlspecialchars($company['id']); ?>">
                            <?= htmlspecialchars($company['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="documentTypeFilter" class="form-label">Tipo de Documento</label>
                <select id="documentTypeFilter" class="form-select">
                    <option value="">Selecione um tipo de documento</option>
                    <?php foreach ($document_types as $document_type): ?>
                        <option value="<?= htmlspecialchars($document_type['id']); ?>" 
                                data-advance-notification="<?= htmlspecialchars($document_type['advance_notification']); ?>"
                                data-personalized-advance-notification="<?= htmlspecialchars($document_type['personalized_advance_notification']); ?>">
                            <?= htmlspecialchars($document_type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="expirationDateFilter" class="form-label">Vencimento</label>
                <div class="date-fields row">
                    <div class="col-6">
                        <input type="date" id="startDateFilter" class="form-control">
                        <small>De</small>
                    </div>
                    <div class="col-6">
                        <input type="date" id="endDateFilter" class="form-control">
                        <small>Até</small>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="statusFilter" class="form-label">Filtrar por status</label>
                <select id="statusFilter" class="form-select">
                    <option value="all_parametrized">Todos (até 7 dias de vencido)</option>
                    <option value="all" selected>Todos</option>
                    <option value="in_day">Em dia</option>
                    <option value="next">A vencer</option>
                    <option value="today">Vence hoje</option>
                    <option value="overdue">Vencido</optiozn>
                </select>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer d-flex align-items-center justify-content-between">
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas" aria-label="Close">Voltar</button>
        <div>
            <button id="clearFiltersButton" type="button" class="btn btn-link d-none">Limpar Filtros</button>
            <button id="applyFiltersButton" type="button" class="btn btn-primary">Aplicar Filtros</button>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir o documento<span id="documentName"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
                <button type="button" id="confirmDelete" class="btn btn-danger">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Visualização do Documento -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- modal extra-grande para melhor visualização -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Visualizar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-4">
                        <!-- Empresa -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-0">Empresa:</label>
                            <p class="form-control-plaintext fs-16 p-0" id="empresa">Nome da Empresa</p>
                        </div>
                        <!-- Data de Vencimento -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-0">Data de Vencimento:</label>
                            <p class="form-control-plaintext fs-16 p-0" id="dataVencimento">DD/MM/AAAA</p>
                        </div>
                        <!-- Nome do Documento -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-0">Nome do Documento:</label>
                            <p class="form-control-plaintext fs-16 p-0" id="nomeDocumento">Nome do Documento</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <!-- Tipo de Documento -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-0">Tipo de Documento:</label>
                            <p class="form-control-plaintext fs-16 p-0" id="tipoDocumento">Tipo do Documento</p>
                        </div>
                        <!-- Notificação Antecipada -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-0">Notificação Antecipada:</label>
                            <p class="form-control-plaintext fs-16 p-0" id="notificacaoAntecipada">Detalhes da Notificação</p>
                        </div>
                        <!-- Notificação Antecipada -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-0">Status:</label>
                            <div id="status"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <!-- Arquivo -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2">Arquivo:</label>
                            <div id="arquivo">
                                <a href="#" class="btn btn-primary btn-sm" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Baixar Documento">
                                    <i class="mdi mdi-download fs-16 align-middle"></i>
                                </a>
                                <a href="#" class="btn btn-link px-2" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Pré-visualização">NomeArquivo.pdf</a>
                            </div>
                        </div>
                        <!-- Observação -->
                        <div>
                            <label class="form-label fw-semibold mb-0">Observação:</label>
                            <blockquote class="blockquote fs-16 p-0 mb-0" id="observacao">Observações adicionais aqui</blockquote>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para informar renovação -->
<div class="modal fade" id="renewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="informRenewal" class="d-none">
                    <h6 class="fs-15">Como deseja continuar?</h6>
                    <div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="option_renew" id="option_renew_1" value="no_renewal">
                            <label class="form-check-label" for="option_renew_1">
                                Documento não será renovado
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="option_renew" id="option_renew_2" value="renew_later">
                            <label class="form-check-label" for="option_renew_2">
                                Documento a ser renovado
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="option_renew" id="option_renew_3" value="renew_with_new">
                            <label class="form-check-label" for="option_renew_3">
                                Documento renovado, informar novo vencimento
                            </label>
                        </div>
                    </div>
                </form>

                <form id="renewForm">
                    <!-- Novo Documento -->
                    <div class="mb-3">
                        <label for="document" class="form-label">Novo Documento</label>
                        <input class="form-control" name="document" type="file" id="document" accept=".jpg,.png,.pdf,.doc,.docx,.xls,.xlsx,.pfx,.p12">
                    </div>

                    <!-- Nova Data de Validade -->
                    <div class="row">
                        <div class="col-md-4">
                            <div>
                                <label for="expiration_date" class="form-label">Nova Data de Validade</label>
                                <input class="form-control" name="expiration_date" type="date" id="expiration_date">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex aling-items-center justify-content-between">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <div>
                    <button class="btn btn-primary" id="btnNextStep" type="submit">Continuar</button>
                    <button class="btn btn-primary d-none" id="btnSubmit" type="submit">Salvar</button>
                    <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status">Carregando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulseAnimation {
        0% {
            transform: translate(-50%, -50%) scale(1);
        }
        50% {
            transform: translate(-50%, -50%) scale(1.5);
        }
        100% {
            transform: translate(-50%, -50%) scale(1);
        }
    }
    .pulse-icon {
        animation: pulseAnimation 2s infinite ease-in-out;
    }
</style>

<!-- Modal de Upload de Documento -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="uploadModalLabel">Upload de Documento com IA</h5>
        <!-- Botão de fechar customizado -->
        <button type="button" class="btn-close custom-close" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="uploadForm">
          <div class="mb-3">
            <label for="documentFile" class="form-label">Escolha o arquivo</label>
            <input type="file" class="form-control" id="documentFile" name="documentFile" accept=".pdf, .jpg, .jpeg, .png" required>
          </div>
          <div class="alert alert-info" role="alert">
            É possível fazer upload de documentos em PDF, JPG, PNG ou JPEG. As imagens serão limitadas a 1MB e o PDF não terá limite.
          </div>
          <button type="submit" class="btn btn-primary mb-0">Enviar</button>
        </form>
        <!-- Área para exibir o status do upload -->
        <div id="uploadStatus" class="d-none">
          <div class="text-center">
            <div class="position-relative">
              <div class="spinner-border m-2" style="width: 4rem; height: 4rem;" role="status">
                <span class="visually-hidden">Carregando...</span>
              </div>
              <i class="mdi mdi-creation fs-20 align-middle pulse-icon" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);"></i>
            </div>
            <p>Carregando... Por favor, não feche a página.</p>
          </div>
        </div>
        <!-- Área onde os dados extraídos serão exibidos -->
        <div id="result"></div>
      </div>
    </div>
  </div>
</div>

<!-- Trecho do código dentro do success do AJAX -->
<script>
  // Função que valida se a string está no formato brasileiro dd/mm/yyyy
  function isValidAmericanDate(dateStr) {
    var regex = /^(0?[1-9]|[12]\d|3[01])\/(0?[1-9]|1[0-2])\/\d{4}$/;
    return regex.test(dateStr);
  }

  // Converte data de dd/mm/yyyy para o formato ISO (yyyy-mm-dd)
  function convertAmericanToISO(dateStr) {
    var parts = dateStr.split('/');
    if (parts.length !== 3) return '';
    var day = parts[0].padStart(2, '0');
    var month = parts[1].padStart(2, '0');
    var year = parts[2];
    return `${year}-${month}-${day}`;
  }

  $(document).ready(function(){
    var uploadedFileName = ''; // variável global para armazenar o nome do arquivo

    $('#uploadForm').on('submit', function(e) {
      e.preventDefault();
      
      var fileInput = $('#documentFile')[0];
      if (fileInput.files.length === 0) {
        alert('Selecione um arquivo.');
        return;
      }
      var file = fileInput.files[0];
      uploadedFileName = file.name; // armazena o nome do arquivo
      var extension = file.name.split('.').pop().toLowerCase();
      
      if (extension !== 'pdf' && file.size > 1024 * 1024) {
        alert('Imagens devem ter tamanho máximo de 1MB.');
        return;
      }
      
      var formData = new FormData();
      formData.append('document', file);
      formData.append('documentType', $('#documentType').val());
      
      $('#uploadForm').addClass('d-none');
      $('#uploadStatus').removeClass('d-none');
      
      $.ajax({
        url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/upload-ia.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
              var percentComplete = Math.round((evt.loaded / evt.total) * 100);
              $('#uploadStatus p').text('Carregando... ' + percentComplete + '% concluído. Por favor, não feche a página.');
            }
          }, false);
          return xhr;
        },
        success: function(response) {
          $('#uploadStatus').addClass('d-none');
  
          var jsonResponse;
          try {
            jsonResponse = JSON.parse(response);
          } catch (e) {
            console.error("Erro ao converter resposta em JSON:", e);
            $('#result').html('Erro ao processar a resposta.');
            return;
          }
          
          var verificationHTML = `<h5>Verifique os dados extraídos:</h5>`;
  
          if (!jsonResponse.empresaExist) {
            verificationHTML += `
              <div class="mb-3">
                <label class="form-label" id="empresaLabel">Empresa não encontrada: ${jsonResponse.paraQuem || ''}</label>
                <select class="form-select" id="empresaOption" name="empresaOption">
                  <option value="">Selecione uma empresa</option>
                  <?php foreach ($companies as $company): ?>
                    <option value="<?= htmlspecialchars($company['id']); ?>">
                      <?= htmlspecialchars($company['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-secondary mt-2" id="openCompanyModal">Cadastrar Empresa</button>
              </div>
            `;
          } else {
            verificationHTML += `
              <div class="mb-3">
                <label class="form-label" id="empresaLabel">Empresa</label>
                <select class="form-select" id="empresaOption" name="empresaOption">
                  <option value="">Selecione uma empresa</option>
                  <?php foreach ($companies as $company): ?>
                    <option value="<?= htmlspecialchars($company['id']); ?>">
                      <?= htmlspecialchars($company['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            `;
          }
  
          verificationHTML += `
            <div class="mb-3">
              <label for="nomeAlvara" class="form-label">Qual nome do alvará</label>
              <input type="text" id="nomeAlvara" class="form-control" value="${jsonResponse.nomeAlvara || ''}" aria-describedby="nomeAlvara-error">
            </div>
          `;
  
          verificationHTML += `
              <div class="mb-3">
                <label class="form-label">Tipo do documento</label>
                <select class="form-select" id="tipoDocumentoOption" name="tipoDocumentoOption" required>
                  <option value="">Selecione um tipo de documento</option>
                  <?php foreach ($document_types as $document_type): ?>
                    <option value="<?= htmlspecialchars($document_type['id']); ?>">
                      <?= htmlspecialchars($document_type['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
          `;
  
          verificationHTML += `
            <div class="mb-3">
              <label for="endereco" class="form-label">Para qual endereço o alvará foi emitido</label>
              <input type="text" id="endereco" class="form-control" value="${jsonResponse.endereco || ''}" aria-describedby="endereco-error">
            </div>
            <div class="mb-3">
              <label for="orgao" class="form-label">Qual foi o órgão que emitiu o alvará</label>
              <input type="text" id="orgao" class="form-control" value="${jsonResponse.orgao || ''}" aria-describedby="orgao-error">
            </div>
          `;
  
          var vencimentoHTML = '';
          if (jsonResponse.dataVencimento && isValidAmericanDate(jsonResponse.dataVencimento)) {
            var isoVencimento = convertAmericanToISO(jsonResponse.dataVencimento);
            vencimentoHTML = `<input type="date" id="vencimento" class="form-control" value="${isoVencimento}" aria-describedby="vencimento-error">`;
          } else {
            vencimentoHTML = `
                <input type="date" id="vencimento" class="form-control" value="" aria-describedby="vencimento-error">
                <em id="vencimento-error" class="error invalid-feedback">Por favor, informe a data de vencimento.</em>
            `;
          }
  
          var deferimentoHTML = '';
          if (jsonResponse.dataDeferimento && isValidAmericanDate(jsonResponse.dataDeferimento)) {
            var isoDeferimento = convertAmericanToISO(jsonResponse.dataDeferimento);
            deferimentoHTML = `<input type="date" id="deferimento" class="form-control" value="${isoDeferimento}" aria-describedby="deferimento-error">`;
          } else {
            deferimentoHTML = `
                <input type="date" id="deferimento" class="form-control" value="" aria-describedby="deferimento-error">
                <em id="deferimento-error" class="error invalid-feedback">Por favor, informe a data de deferimento.</em>
            `;
          }
  
          verificationHTML += `
            <div class="mb-3">
              <label for="vencimento" class="form-label">Data de vencimento/validade</label>
              ${vencimentoHTML}
            </div>
            <div class="mb-3">
              <label for="deferimento" class="form-label">Data de deferimento</label>
              ${deferimentoHTML}
            </div>
            <div class="mb-3">
              <label class="form-label">Documento Enviado</label>
              <div id="documentPreview">
                <a href="uploads/${uploadedFileName}" target="_blank" id="fileLink">Visualizar Documento</a>
              </div>
              <input type="hidden" id="uploadedFileName" value="${uploadedFileName}">
            </div>
            <button type="button" class="btn btn-primary" id="confirmData">Confirmar</button>
            <button type="button" class="btn btn-secondary" id="repeatProcess">Repetir o Processo</button>
          `;
  
          $('#result').html(verificationHTML);
          if (jsonResponse.empresaExist) {
            $('#empresaOption').val(jsonResponse.empresaId);
          }
  
          // Evento para repetir o processo
          $('#repeatProcess').on('click', function(){
            $('#result').empty();
            $('#uploadForm')[0].reset();
            $('#uploadForm').removeClass('d-none');
          });
  
          // Validação dos campos no clique do "Confirmar"
          $('#confirmData').on('click', function(){
            var isValid = true;
  
            // Validação do campo "empresaOption" (se necessário)
            if ($('#empresaOption').val().trim() === "") {
              $('#empresaOption').removeClass('is-valid').addClass('is-invalid');
              if (!$('#empresaOption-error').length) {
                $('#empresaOption').after('<em id="empresaOption-error" class="error invalid-feedback">Por favor, selecione uma empresa.</em>');
              }
              isValid = false;
            } else {
              $('#empresaOption').removeClass('is-invalid').addClass('is-valid');
              $('#empresaOption-error').remove();
            }
  
            // Validação do campo "tipoDocumentoOption"
            if ($('#tipoDocumentoOption').val().trim() === "") {
              $('#tipoDocumentoOption').removeClass('is-valid').addClass('is-invalid');
              if (!$('#tipoDocumentoOption-error').length) {
                $('#tipoDocumentoOption').after('<em id="tipoDocumentoOption-error" class="error invalid-feedback">Por favor, selecione um tipo de documento.</em>');
              }
              isValid = false;
            } else {
              $('#tipoDocumentoOption').removeClass('is-invalid').addClass('is-valid');
              $('#tipoDocumentoOption-error').remove();
            }
  
            // Validação do campo "endereco"
            if ($('#endereco').val().trim() === "") {
              $('#endereco').removeClass('is-valid').addClass('is-invalid');
              if (!$('#endereco-error').length) {
                $('#endereco').after('<em id="endereco-error" class="error invalid-feedback">Por favor, preencha o endereço.</em>');
              }
              isValid = false;
            } else {
              $('#endereco').removeClass('is-invalid').addClass('is-valid');
              $('#endereco-error').remove();
            }
  
            // Validação do campo "orgao"
            if ($('#orgao').val().trim() === "") {
              $('#orgao').removeClass('is-valid').addClass('is-invalid');
              if (!$('#orgao-error').length) {
                $('#orgao').after('<em id="orgao-error" class="error invalid-feedback">Por favor, preencha o órgão.</em>');
              }
              isValid = false;
            } else {
              $('#orgao').removeClass('is-invalid').addClass('is-valid');
              $('#orgao-error').remove();
            }
  
            // Validação do campo "nomeAlvara"
            $('#nomeAlvara').removeClass('is-invalid').addClass('is-valid');
            $('#nomeAlvara-error').remove();
  
            // Validação do campo "vencimento"
            if ($('#vencimento').val().trim() === "") {
              $('#vencimento').removeClass('is-valid').addClass('is-invalid');
              if (!$('#vencimento-error').length) {
                $('#vencimento').after('<em id="vencimento-error" class="error invalid-feedback">Por favor, selecione a data de vencimento.</em>');
              }
              isValid = false;
            } else {
              $('#vencimento').removeClass('is-invalid').addClass('is-valid');
              $('#vencimento-error').remove();
            }
  
            // Validação do campo "deferimento"
            if ($('#deferimento').val().trim() === "") {
              $('#deferimento').removeClass('is-valid').addClass('is-invalid');
              if (!$('#deferimento-error').length) {
                $('#deferimento').after('<em id="deferimento-error" class="error invalid-feedback">Por favor, selecione a data de deferimento.</em>');
              }
              isValid = false;
            } else {
              $('#deferimento').removeClass('is-invalid').addClass('is-valid');
              $('#deferimento-error').remove();
            }
  
            // Se algum campo estiver inválido, interrompe o envio
            if (!isValid) {
              return;
            }
  
            // Caso os campos estejam válidos, envia os dados para salvar o documento
            var empresaOption   = $('#empresaOption').val();
            var tipoDocumento   = $('#tipoDocumentoOption').val();
            var endereco        = $('#endereco').val();
            var orgao           = $('#orgao').val();
            var nomeAlvara      = $('#nomeAlvara').val();
            var vencimento      = $('#vencimento').val();
            var deferimento     = $('#deferimento').val();
            var uploadedFileName = $('#uploadedFileName').val();
  
            var confirmData = {
              action: 'confirm',
              empresaOption: empresaOption,
              tipoDocumentoOption: tipoDocumento,
              endereco: endereco,
              orgao: orgao,
              nomeAlvara: nomeAlvara,
              vencimento: vencimento,
              deferimento: deferimento,
              uploadedFileName: uploadedFileName
            };
  
            $.ajax({
              url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/upload-ia.php',
              type: 'POST',
              data: confirmData,
              success: function(response) {
                try {
                  var jsonResponse = JSON.parse(response);
                  if(jsonResponse.status === 'success') {
                    location.reload();
                  } else if(jsonResponse.error) {
                    alert('Erro: ' + jsonResponse.error);
                  }
                } catch(e) {
                  alert('Erro ao processar a resposta do servidor.');
                }
              },
              error: function() {
                alert('Erro ao salvar o documento.');
              }
            });
          });
  
          // Abre o modal de cadastro de empresa, se necessário
          $('#openCompanyModal').on('click', function(){
            $('#companyForm #name').val(jsonResponse.paraQuem || '');
            var companyModal = new bootstrap.Modal(document.getElementById('companyModal'), { backdrop: 'static' });
            companyModal.show();
          });
        },
        error: function() {
          $('#uploadStatus').addClass('d-none');
          $('#result').html('Erro ao enviar o arquivo.');
        }
      });
    });
  
    $('.custom-close').on('click', function(e){
      if ($('#result').html().trim() !== "") {
        $('#uploadModal').modal('hide');
        $('#result').empty();
        $('#uploadForm')[0].reset();
        $('#uploadForm').removeClass('d-none');
      } else {
        var modalEl = document.getElementById('uploadModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
          modal.hide();
        }
      }
    });
  });
</script>

<?php if (isset($token) && $token === 'ia'): ?>
<script>
  $(document).ready(function(){
    // Abre o modal assim que o documento estiver pronto
    $('#uploadModal').modal('show');
  });
</script>
<?php endif; ?>

<!-- Modal para Cadastro de Empresa (fica aberto sobre o modal de upload) -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="companyForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companyModalLabel">Cadastrar Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Nome da Empresa -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nome da Empresa*</label>
                    <input class="form-control" name="name" type="text" id="name" maxlength="120" placeholder="Digite o Nome da Empresa" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <!-- CPF/CNPJ -->
                        <div class="mb-3">
                            <label for="document" class="form-label">CPF/CNPJ*</label>
                            <input class="form-control" name="document" type="text" id="document" placeholder="Digite o CPF ou CNPJ" onkeyup="handleCpfCnpj(event)" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Responsável -->
                        <div class="mb-3">
                            <label for="responsible" class="form-label">Responsável*</label>
                            <input class="form-control" name="responsible" type="text" id="responsible" maxlength="100" placeholder="Digite o Nome do Responsável" required>
                        </div>
                    </div>
                </div>
                <!-- Telefone e E-mail -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefone*</label>
                    <input class="form-control" name="phone" type="tel" id="phone" maxlength="15" placeholder="Digite o Telefone" onkeyup="handlePhone(event)" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail*</label>
                    <input class="form-control" name="email" type="email" id="email" maxlength="120" placeholder="Digite o E-mail" required>
                </div>
                <!-- Estado (UF) e Cidade -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-grid">
                            <label for="uf" class="form-label">Estado (UF)*</label>
                            <select class="form-select" name="uf" id="uf" required>
                                <option value="" selected disabled>Selecione um estado</option>
                                <!-- Opções carregadas dinamicamente -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid">
                            <label for="cidade" class="form-label">Cidade*</label>
                            <select class="form-select" name="cidade" id="cidade" required>
                                <option value="" selected disabled>Selecione uma cidade</option>
                                <!-- Opções carregadas dinamicamente -->
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <!-- Note que este modal é apenas para cadastro, sem interferir no modal de upload -->
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <div>
                    <button class="btn btn-primary" id="btnSubmit" type="submit">Cadastrar</button>
                    <button class="btn btn-primary loader-btn d-none" id="btnLoader" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status">Carregando...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    // Verifica o texto inicial ao carregar a página
    verificarTextoUF();
    verificarTextoCidade();

    // Verifica novamente sempre que há uma alteração no Select2
    $('#companyForm #uf').on('change', function () {
        verificarTextoUF();
    });

    // Verifica novamente sempre que há uma alteração no Select2
    $('#companyForm #cidade').on('change', function () {
        verificarTextoCidade();
    });

    function verificarTextoUF() {
        const container = $('#select2-uf-container');
        const textoAtual = container.text().trim(); // Remove espaços em branco extras

        if (textoAtual.toLowerCase() !== '' && textoAtual.toLowerCase() !== 'Selecione um estado') {
            $('#companyForm #uf').addClass("is-valid").removeClass("is-invalid");
        }
    }

    function verificarTextoCidade() {
        const container = $('#select2-cidade-container');
        const textoAtual = container.text().trim(); // Remove espaços em branco extras

        if (textoAtual.toLowerCase() !== '' && textoAtual.toLowerCase() !== 'Selecione uma cidade') {
            $('#companyForm #cidade').addClass("is-valid").removeClass("is-invalid");
        }
    }
});
</script>

<script>
    const handlePhone = (event) => {
        let input = event.target;

        let numericValue = input.value.replace(/\D/g, '');
        input.value = input.value.replace(/\D/g, '').slice(0, 11);

        input.value = phoneMask(input.value);
    };

    const phoneMask = (value) => {
        if (!value) return "";
        value = value.replace(/\D/g, ''); // Remove caracteres não numéricos
        value = value.replace(/(\d{2})(\d)/, "($1) $2"); // Formata o DDD
        value = value.replace(/(\d)(\d{4})$/, "$1-$2"); // Formata o número
        return value;
    };

    function handleCpfCnpj(event) {
        var input = event.target;
        var value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

        if (value.length <= 11) {
            $(input).mask('000.000.000-00#####');
        } else {
            $(input).mask('00.000.000/0000-00');
        }
    }

    $(document).ready(function() {
        $('#companyForm #uf').select2({
            dropdownParent: $("#companyForm"),
            placeholder: 'Selecione um estado',
        });

        $('#companyForm #cidade').select2({
            dropdownParent: $("#companyForm"),
            placeholder: 'Selecione uma cidade',
        });

        // Carregar estados (UF) da API IBGE
        $.getJSON('https://servicodados.ibge.gov.br/api/v1/localidades/estados', function (data) {
            var estados = data.sort((a, b) => a.nome.localeCompare(b.nome));
            estados.forEach(function (estado) {
                $('#uf').append(new Option(estado.nome, estado.sigla));
            });
        });

        // Carregar cidades com base na UF selecionada
        $('#companyForm #uf').on('change', function () {
            var uf = $(this).val();
            $('#companyForm #cidade').empty().append(new Option('Selecione a Cidade', '', true, true));
            $.getJSON(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`, function (data) {
                data.sort((a, b) => a.nome.localeCompare(b.nome)).forEach(function (cidade) {
                    $('#companyForm #cidade').append(new Option(cidade.nome, cidade.nome));
                });
            });
        });

        // Adicionar validação personalizada para o e-mail
        $.validator.addMethod("documentExists", function(value, element) {
            let isValid = false;
            if (value) {
                $.ajax({
                    url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company/forms-validations/document-exists.php", // URL do script PHP que verifica o e-mail no banco de dados
                    type: "POST",
                    data: { action: 'document-exists', document: value },
                    async: false, // Sincronizar para garantir a validação antes de prosseguir
                    success: function(response) {
                        isValid = response.status === "available"; // Verifica se o e-mail está disponível
                    },
                    error: function() {
                        isValid = false;
                    }
                });
            }
            return isValid;
        }, "Este CPF/CNPJ já está cadastrado.");

        // Função para validar CPF ou CNPJ
        $.validator.addMethod("cpfcnpj", function(value, element) {
            value = value.replace(/\D/g, ''); // Remove tudo o que não for número
            if (value.length === 11) {
                return validarCPF(value);
            } else if (value.length === 14) {
                return validarCNPJ(value);
            }
            return false;
        }, "Por favor, insira um CPF ou CNPJ válido");

        // Adicionar validação personalizada para o e-mail
        $.validator.addMethod("emailExists", function(value, element) {
            let isValid = false;
            if (value) {
                $.ajax({
                    url: "<?= INCLUDE_PATH_DASHBOARD; ?>back-end/forms-validations/email-exists.php", // URL do script PHP que verifica o e-mail no banco de dados
                    type: "POST",
                    data: { action: 'email-exists', email: value },
                    async: false, // Sincronizar para garantir a validação antes de prosseguir
                    success: function(response) {
                        isValid = response.status === "available"; // Verifica se o e-mail está disponível
                    },
                    error: function() {
                        isValid = false;
                    }
                });
            }
            return isValid;
        }, "Este e-mail já está cadastrado.");

        function validarCPF(cpf) {
            let soma = 0;
            let resto;
            if (cpf === "00000000000" || cpf === "11111111111" || cpf === "22222222222" || cpf === "33333333333" || cpf === "44444444444" || cpf === "55555555555" || cpf === "66666666666" || cpf === "77777777777" || cpf === "88888888888" || cpf === "99999999999") {
                return false; // CPF inválido
            }
            // Valida CPF
            for (let i = 1; i <= 9; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf.substring(9, 10))) return false;

            soma = 0;
            for (let i = 1; i <= 10; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            return resto === parseInt(cpf.substring(10, 11));
        }

        function validarCNPJ(cnpj) {
            // Remove qualquer caractere não numérico
            cnpj = cnpj.replace(/\D/g, '');

            // Verifica se o CNPJ tem 14 dígitos
            if (cnpj.length !== 14) {
                return false;
            }

            // CNPJ's inválidos conhecidos
            const cnpjsInvalidos = [
                "00000000000000", "11111111111111", "22222222222222", "33333333333333", 
                "44444444444444", "55555555555555", "66666666666666", "77777777777777", 
                "88888888888888", "99999999999999"
            ];

            if (cnpjsInvalidos.includes(cnpj)) {
                return false; // CNPJ inválido
            }

            // Valida primeiro dígito verificador
            let soma = 0;
            let peso = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            for (let i = 0; i < 12; i++) {
                soma += parseInt(cnpj.charAt(i)) * peso[i];
            }
            let resto = soma % 11;
            if (resto < 2) {
                resto = 0;
            } else {
                resto = 11 - resto;
            }
            if (resto !== parseInt(cnpj.charAt(12))) {
                return false;
            }

            // Valida segundo dígito verificador
            soma = 0;
            peso = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            for (let i = 0; i < 13; i++) {
                soma += parseInt(cnpj.charAt(i)) * peso[i];
            }
            resto = soma % 11;
            if (resto < 2) {
                resto = 0;
            } else {
                resto = 11 - resto;
            }
            return resto === parseInt(cnpj.charAt(13));
        }

        // Validação do Formulário
        $("#companyForm").validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2,
                },
                phone: {
                    required: true,
                    minlength: 14,
                },
                email: {
                    required: true,
                    email: true,
                },
                responsible: {
                    required: true,
                    minlength: 2,
                },
                document: {
                    required: true,
                    minlength: 14,
                    cpfcnpj: true,
                    documentExists: true,
                },
                uf: "required",
                cidade: "required",
            },
            messages: {
                name: {
                    required: "Por favor, insira seu nome",
                    minlength: "Seu nome deve ter pelo menos 2 caracteres",
                },
                phone: {
                    required: "Por favor, insira seu telefone",
                    minlength: "Seu telefone deve ter pelo menos 14 caracteres",
                },
                email: {
                    required: "Por favor, insira um e-mail",
                    email: "Por favor, insira um e-mail válido",
                },
                responsible: {
                    required: "Por favor, insira um Responsável",
                    minlength: "O nome do responsável deve ter pelo menos 2 caracteres",
                },
                document: {
                    required: "Por favor, insira seu CPF/CNPJ",
                    minlength: "Seu documento deve ter pelo menos 14 caracteres",
                    cpfcnpj: "Por favor, insira um CPF ou CNPJ válido",
                    documentExists: "Este CPF/CNPJ já está cadastrado.",
                },
                uf: "Por favor, selecione um Estado (UF)",
                cidade: "Por favor, selecione uma cidade",
            },
            errorElement: "em",
            errorPlacement: function (error, element) {
                console.log(element.prop("type"));
                error.addClass("invalid-feedback");
                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.next("label"));
                } else if (element.prop("type") === "select-one") {
                    error.insertAfter(element.next("span.select2"));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).addClass("is-valid").removeClass("is-invalid");
            },
            submitHandler: function(form) {
                // Impede o envio padrão do formulário
                event.preventDefault(); 

                // Define os botões como variáveis
                var btnSubmit = $("#companyForm #btnSubmit");
                var btnLoader = $("#companyForm #btnLoader");

                // Desabilitar botão submit e habilitar loader
                btnSubmit.prop("disabled", true).addClass("d-none");
                btnLoader.removeClass("d-none");

                // Cria um objeto FormData a partir do formulário
                var formData = new FormData(form);

                // Adiciona um novo campo
                formData.append("action", "register-company-modal");

                // Realiza o AJAX para enviar os dados
                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/company/register.php', // Substitua pelo URL do seu endpoint
                    type: 'POST',
                    data: formData,
                    processData: false, // Impede que o jQuery processe os dados
                    contentType: false, // Impede que o jQuery defina o Content-Type
                    success: function (response) {
                        if (response.status == "success") {
                            // Adiciona a nova empresa ao select do modal de upload e define-a como selecionada
                            $('#empresaOption').append(new Option(response.company.name, response.company.id, true, true));
                            $('#openCompanyModal').remove();
                            $('#empresaLabel').text('Para quem esse alvará foi emitido');

                            $('#companyModal').modal('hide');

                            // Caso contrário, exibe a mensagem de erro
                            $("#uploadForm .alert").remove(); // Remove qualquer mensagem de erro anterior
                            $("#uploadForm").before('<div class="alert alert-primary alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        } else {
                            // console.error("Erro no AJAX:", status, error);

                            // Caso contrário, exibe a mensagem de erro
                            $("#companyForm .alert").remove(); // Remove qualquer mensagem de erro anterior
                            $("#companyForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        }
                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro no AJAX:", status, error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#companyForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    }
                });
            }
        });
    });
</script>

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Documento</h4>
    </div>

    <div class="text-end">
        <!-- Botão que abre o modal -->
        <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#uploadModal" title="Cadastrar Documento com IA">
            <i class="mdi mdi-creation fs-16 align-middle"></i> Upload por IA
        </button>
        <a href="<?= INCLUDE_PATH_DASHBOARD; ?>cadastrar-documento" class="btn btn-dark">
            <i class="mdi mdi-plus fs-16 align-middle"></i>
            Upload de Documento
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 text-black">Documentos cadastrados</h5>
                    <div class="ms-auto d-flex">
                        <div class="dropdown me-2">
                            <button class="btn btn-primary btn-sm dropdown-toggle fs-14" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Exportar
                                <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button id="exportCSV" class="dropdown-item">Exportar CSV</button></li>
                                <li><button id="exportXLS" class="dropdown-item">Exportar XLS</button></li>
                                <li><button id="exportPDF" class="dropdown-item">Exportar PDF</button></li>
                                <li><button id="exportDocx" class="dropdown-item">Exportar DOCX</button></li>
                                <li><button id="exportPrint" class="dropdown-item">Imprimir</button></li>
                            </ul>
                        </div>
                        <div class="me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Filtrar por status">
                            <label class="visually-hidden" for="statusFast" name="statusFast">Filtrar por status</label>
                            <select class="form-select form-select-sm fs-14" id="statusFast" name="statusFast">
                                <option value="all_parametrized" selected>Todos (até 7 dias de vencido)</option>
                                <option value="all">Todos</option>
                                <option value="in_day">Em dia</option>
                                <option value="next">A vencer</option>
                                <option value="today">Vence hoje</option>
                                <option value="overdue">Vencido</optiozn>
                            </select>
                        </div>
                        <button class="btn btn-sm bg-light border dropdown-toggle fw-medium text-black" type="button" data-bs-toggle="offcanvas" href="#filterOffcanvas" role="button" aria-controls="filterOffcanvas">
                            <i class="mdi mdi-filter-outline me-1 fs-14"></i>Filtrar Documentos</i>
                        </button>
                    </div>
                </div>
                <div class="active-filters mt-3 d-none">
                    <h6 class="text-uppercase fs-13">Filtros</h6>

                    <div id="filterList" class="d-flex flex-wrap gap-2"></div>

                </div>
            </div>

            <div class="card-body p-0">
                <table id="documentsTable" class="table table-traffic mb-0">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Tipo de Documento</th>
                            <th>Arquivo</th>
                            <th>Vencimento do Documento</th>
                            <th>Notificação Antecipada</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="card-footer py-0 border-top">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <div class="text-block text-center text-sm-start">
                            <span id="info" class="fw-medium">1 de 3</span>
                        </div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <div class="pagination gap-2 justify-content-center py-3 ps-0 pe-3">
                            <ul id="pagination" class="pagination mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link me-2 rounded-2" href="javascript:void(0);" id="prevPage">Anterior</a>
                                </li>
                                <!-- Dynamically generated page numbers will be inserted here -->
                                <li class="page-item liNextPage">
                                    <a class="page-link text-primary rounded-2" href="javascript:void(0);" id="nextPage">Próxima</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Adicionar máscaras e validação -->
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>

<!-- Modal para visualizacao -->
<script>
$(document).ready(function(){
    // Verifica os parâmetros da URL
    const urlParams = new URLSearchParams(window.location.search);
    const openModal = urlParams.get('openModal');
    const docId = urlParams.get('docId');

    if(openModal === "1" && docId) {
        // Realiza a requisição AJAX para obter os dados do documento
        $.ajax({
            url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/get.php`,
            type: 'POST',
            dataType: 'json',
            data: { id: docId },
            success: function(response) {
                if(response.status === "success") {
                    $('#empresa').text(response.data.company);
                    $('#dataVencimento').text(response.data.expiration_date);
                    $('#nomeDocumento').text(response.data.name);
                    
                    if (response.data.document && response.data.document.path) {
                        var arquivoHtml = '';
                        arquivoHtml += '<a href="'+ response.data.document.path +'" class="btn btn-primary btn-sm" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Baixar Documento">';
                        arquivoHtml += '<i class="mdi mdi-download fs-16 align-middle"></i></a>';
                        arquivoHtml += ' <a href="'+ response.data.document.path +'" class="btn btn-link px-2" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Pré-visualização">'+ response.data.document.file +'</a>';
                        $('#arquivo').html(arquivoHtml);
                    } else {
                        $('#arquivo').html('-');
                    }
                    
                    $('#observacao').text(response.data.observation);
                    $('#tipoDocumento').text(response.data.document_type);
                    $('#notificacaoAntecipada').text(response.data.advance_notification);
                    $('#status').html(response.data.status);
                    
                    // Abre o modal de visualização do documento
                    $('#documentModal').modal('show');
                } else {
                    console.error('Documento não encontrado ou erro na consulta.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição AJAX: ' + error);
            }
        });
    }
});
</script>

<script>
$(document).ready(function(){
    // Usando delegação de eventos para capturar cliques em elementos que podem ser inseridos dinamicamente
    $(document).on('click', '.btn-view', function(e) {
        e.preventDefault();

        // Pega o data-id do botão clicado
        var documentId = $(this).data('id');

        // Faz a requisição AJAX para o backend PHP
        $.ajax({
            url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/get.php`, // Altere para o caminho correto do seu arquivo PHP
            type: 'POST',
            dataType: 'json',
            data: { id: documentId },
            success: function(response) {
                if(response.status === "success") {
                    // Preenche os campos do modal com os dados retornados
                    $('#empresa').text(response.data.company);
                    $('#dataVencimento').text(response.data.expiration_date);
                    $('#nomeDocumento').text(response.data.name);

                    // Atualiza o conteúdo do campo "Arquivo"
                    if (response.data.document && response.data.document.path) {
                        var arquivoHtml = '';
                        arquivoHtml += '<a href="'+ response.data.document.path +'" class="btn btn-primary btn-sm" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Baixar Documento">';
                        arquivoHtml += '<i class="mdi mdi-download fs-16 align-middle"></i></a>';
                        arquivoHtml += ' <a href="'+ response.data.document.path +'" class="btn btn-link px-2" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Pré-visualização">'+ response.data.document.file +'</a>';
                        $('#arquivo').html(arquivoHtml);
                    } else {
                        $('#arquivo').html('-');
                    }
                    
                    $('#observacao').text(response.data.observation);
                    $('#tipoDocumento').text(response.data.document_type);
                    $('#notificacaoAntecipada').text(response.data.advance_notification);
                    $('#status').html(response.data.status);
                    
                    // Abre o modal após preencher os dados
                    $('#documentModal').modal('show');
                } else {
                    $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                    $("#documentsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Documento não encontrado ou ocorreu um erro na consulta.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição AJAX: ' + error);
                $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                $("#documentsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            }
        });
    });
});
</script>

<script>
    $(document).ready(function() {
        // Função para configurar a data mínima no end date
        $('#startDateFilter').on('change', function() {
            var startDate = $(this).val();

            // Verifica se a data do start date foi preenchida
            if (startDate) {
                // Formata a data para o formato YYYY-MM-DD
                var startDateObj = new Date(startDate);

                // Atualiza o minDate do end date
                var formattedEndDate = startDateObj.toISOString().split('T')[0]; // Formata para o formato adequado
                $('#endDateFilter').attr('min', formattedEndDate);

                var startDate = $(this).val();
                var endDate = $('#endDateFilter').val();

                // Verifica se o end date é menor que o start date
                if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                    // Remove o erro se os campos forem corrigidos
                    $('#dateError').remove();
                    $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início</div>')
                        .insertAfter('.date-fields').show(); // Adiciona a mensagem de erro após o campo de data de término
                    $('#endDateFilter').val(''); // Limpa o campo end date
                } else {
                    // Remove o erro se os campos forem corrigidos
                    $('#dateError').remove();
                }
            } else {
                // Remove o minDate do end date caso o start date seja apagado
                $('#endDateFilter').removeAttr('min');
            }
        });

        // Validação para impedir a seleção de uma data menor no end date
        $('#endDateFilter').on('blur', function() {
            var startDate = $('#startDateFilter').val();
            var endDate = $(this).val();

            // Verifica se o end date é menor que o start date
            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                // Remove o erro se os campos forem corrigidos
                $('#dateError').remove();
                $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início</div>')
                    .insertAfter('.date-fields').show(); // Adiciona a mensagem de erro após o campo de data de término
                $(this).val(''); // Limpa o campo end date
            } else {
                // Remove o erro se os campos forem corrigidos
                $('#dateError').remove();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#companyFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione uma ou mais empresas',
            allowClear: true,
        });

        $('#documentTypeFilter').select2({
            dropdownParent: $("#filterOffcanvas"),
            placeholder: 'Selecione um ou mais tipos de documentos',
            allowClear: true,
        });
    });
</script>

<script>
    $(document).ready(function () {
        let elementIdToDelete = null;

        // Quando clicar no botão de exclusão
        $(document).on('click', '.btn-delete', function () {
            elementIdToDelete = $(this).data('id'); // Obtém o ID do elemento a ser excluído
            const elementNameToDelete = $(this).data('name'); // Obtém o nome do elemento a ser excluído
            if (elementNameToDelete || elementNameToDelete.trim() !== "") {
                $('#documentName').text(' "'+elementNameToDelete+'"'); // Mostra o modal
            }
            $('#deleteModal').modal('show'); // Mostra o modal
        });

        // Quando confirmar a exclusão
        $('#confirmDelete').on('click', function () {
            console.log(elementIdToDelete);
            if (elementIdToDelete) {
                // Substitua esta URL pela rota de exclusão do seu servidor
                $.ajax({
                    url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/delete.php?id=${elementIdToDelete}`,
                    type: 'DELETE',
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro:', error);

                        // Caso haja erro na requisição, exibe uma mensagem de erro
                        $(".alert").remove(); // Remove qualquer mensagem de erro anterior
                        $("#documentsForm").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        let elementIdToRenew = null; 
        let docName = null; // Renomeado de "document" para "docName"

        // Quando clicar no botão de exclusão
        $(document).on('click', '.btn-renew', function () {
            $(".alert").remove();
            $('#informRenewal').removeClass('d-none'); // Mostra campos
            $('#renewForm').addClass('d-none'); // Ocultar opções
            $("#btnNextStep").removeClass("d-none");
            $("#btnSubmit").addClass("d-none");
            $('#renewForm')[0].reset();
            $('#informRenewal')[0].reset();

            elementIdToRenew = $(this).data('id'); // Obtém o ID do elemento a ser renovado

            // Faz a requisição AJAX para o backend PHP
            $.ajax({
                url: `<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/get.php`, // Altere para o caminho correto do seu arquivo PHP
                type: 'POST',
                dataType: 'json',
                data: { id: elementIdToRenew },
                success: function(response) {
                    if(response.status === "success") {
                        if (response.data.name !== '-' && response.data.name !== '') {
                            docName = response.data.name;
                        } else {
                            docName = response.data.document_type;
                        }
                        $('#renewModalLabel').text('Renovar ' + docName + ' de ' + response.data.company + ' com vencimento ' + response.data.expiration_date + ':');
                    } else {
                        $('#renewModalLabel').text('Renovar documento #' + elementIdToRenew);
                    }
                    $('#renewModal').modal('show'); // Mostra o modal
                },
                error: function(xhr, status, error) {
                    console.error('Erro:', error);
                    $('#renewModalLabel').text('Renovar documento #' + elementIdToRenew);
                    $('#renewModal').modal('show'); // Chama o modal mesmo em caso de erro
                }
            });
        });

        $('#expiration_date').on('change', function() {
            $('#expirationDateError').remove();
        });

        $("#btnSubmit").click(function () {
            var btnSubmit = $("#btnSubmit");
            var btnLoader = $("#btnLoader");

            btnSubmit.prop("disabled", true).addClass("d-none");
            btnLoader.removeClass("d-none");

            let expirationDate = $("#expiration_date").val(); // Obtém a nova data de validade

            // Verifica se o campo de validade está vazio
            if (!expirationDate) {
                // Remove o erro se os campos forem corrigidos
                $('#expirationDateError').remove();
                $('<div id="expirationDateError" class="invalid-feedback">Por favor, preencha a nova data de validade antes de continuar</div>')
                    .insertAfter('#expiration_date').show(); // Adiciona a mensagem de erro após o campo de data de término

                btnSubmit.prop("disabled", false).removeClass("d-none");
                btnLoader.addClass("d-none");
                return;
            }

            var form = $('#renewForm')[0];
            var formData = new FormData(form);

            formData.append('document_id', elementIdToRenew);
            formData.append('action', 'renew-with-new');

            // Enviar AJAX confirmando que o documento não será renovado
            $.ajax({
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/renew.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>documentos";
                    } else {
                        $(".alert").remove();
                        $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                    }
                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                },
                error: function (xhr, status, error) {
                    console.error("Erro no AJAX:", status, error);

                    $(".alert").remove();
                    $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                    btnSubmit.prop("disabled", false).removeClass("d-none");
                    btnLoader.addClass("d-none");
                }
            });
        });

        let selectedOption = "";

        // Monitora a seleção do usuário e altera a interface conforme a opção escolhida
        $("input[name='option_renew']").change(function () {
            selectedOption = $(this).val();
        });

        // Ação ao clicar no botão "Continuar"
        $("#btnNextStep").click(function () {
            if (selectedOption === "no_renewal") {
                var btnSubmit = $("#btnNextStep");
                var btnLoader = $("#btnLoader");

                btnSubmit.prop("disabled", true).addClass("d-none");
                btnLoader.removeClass("d-none");

                ajaxData = {
                    'document_id': elementIdToRenew,
                    'action': 'no-renewal'
                };

                // Enviar AJAX confirmando que o documento não será renovado
                $.ajax({
                    url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/renew.php',
                    type: 'POST',
                    data: ajaxData,
                    success: function (response) {
                        if (response.status === "success") {
                            window.location.href = "<?= INCLUDE_PATH_DASHBOARD; ?>documentos";
                        } else {
                            $(".alert").remove();
                            $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        }
                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro no AJAX:", status, error);

                        $(".alert").remove();
                        $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Ocorreu um erro, tente novamente mais tarde.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');

                        btnSubmit.prop("disabled", false).removeClass("d-none");
                        btnLoader.addClass("d-none");
                    }
                });
            } else if (selectedOption === "renew_with_new") {
                // Caso o usuário vá enviar um novo documento, valida os campos antes de enviar
                $('#informRenewal').addClass('d-none'); // Mostra campos
                $("#renewForm").removeClass("d-none");
                $("#btnNextStep").addClass("d-none");
                $("#btnSubmit").removeClass("d-none");
            } else if (selectedOption === "renew_later") {
                // Apenas fecha o modal
                $("#renewModal").modal("hide");
            } else {
                $(".alert").remove();
                $("#informRenewal").before('<div class="alert alert-danger alert-dismissible fade show w-100" role="alert">Por favor, selecione uma opção antes de continuar.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        var company = $('#companyFilter').val();
        var documentType = $('#documentTypeFilter').val();
        var startDate = $('#startDateFilter').val();
        var endDate = $('#endDateFilter').val();

        const table = $('#documentsTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            paging: true,
            ajax: {
                url: '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/list.php',
                type: 'GET',
                data: function (d) {
                    // Se nenhum outro filtro for aplicado, usar "all_parametrized"
                    if (!company && !documentType && !startDate && !endDate) {
                        d.statusFilter = 'all_parametrized';
                    } else {
                        d.statusFilter = $('#statusFilter').val();
                    }
                    // Sempre enviar os demais filtros também
                    d.companyFilter = company;
                    d.documentTypeFilter = documentType;
                    d.startDateFilter = startDate;
                    d.endDateFilter = endDate;
                },
                dataSrc: function (json) {
                    return json.data;
                },
            },
            columns: [
                { data: 'company', width: '25%' },
                { data: 'document_type', width: '20%' },
                { data: 'document', width: '10%' },
                { data: 'expiration_date', width: '12.5%' },
                {
                    data: 'advance_notification',
                    className: 'text-end',
                    width: '12.5%'
                },
                {
                    data: 'status',
                    className: 'text-nowrap text-reset text-center',
                    width: '5%'
                },
                {
                    data: 'actions',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    width: '15%'
                },
            ],
            buttons: [
                {
                    extend: 'csvHtml5',
                    title: 'GDok - Documentos',
                    text: 'Exportar CSV',
                    className: 'btn btn-sm btn-outline-primary',
                    filename: function() {
                        return 'documentos_' + new Date().toISOString().replace(/T/, '_').replace(/:/g, '-').split('.')[0];
                    },
                    exportOptions: {
                        columns: ':not(:last-child)', // Exclui a última coluna do export
                        modifier: {
                            search: 'applied', // Mantém filtros ativos na exportação
                            page: 'all' // Exporta todas as páginas da tabela
                        }
                    },
                    customize: function (csv) {
                        return 'GDok - Documentos\n' + csv; // Adiciona o título antes dos dados
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Exportar XLS',
                    className: 'btn btn-sm btn-outline-success',
                    filename: function() {
                        return 'documentos_' + new Date().toISOString().replace(/T/, '_').replace(/:/g, '-').split('.')[0];
                    },
                    exportOptions: {
                        columns: ':not(:last-child)', // Exclui a última coluna do export
                        modifier: {
                            search: 'applied', // Mantém filtros ativos na exportação
                            page: 'all' // Exporta todas as páginas da tabela
                        }
                    },
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Exportar PDF',
                    className: 'btn btn-sm btn-outline-danger',
                    filename: function() {
                        return 'documentos_' + new Date().toISOString().replace(/T/, '_').replace(/:/g, '-').split('.')[0];
                    },
                    exportOptions: {
                        columns: ':not(:last-child)', // Exclui a última coluna do export
                        modifier: {
                            search: 'applied', // Mantém filtros ativos na exportação
                            page: 'all' // Exporta todas as páginas da tabela
                        }
                    },
                    customize: function (doc) {
                        // Estiliza o cabeçalho do PDF
                        doc.styles.tableHeader.alignment = 'left'; // Alinha os títulos à esquerda
                        doc.styles.tableHeader.valign = 'bottom';  // Alinha verticalmente na parte inferior

                        // Alinha todas as células do corpo à esquerda
                        doc.content[1].table.body.forEach(function(row) {
                            row.forEach(function(cell) {
                                cell.alignment = 'left';
                            });
                        });

                        // Modifica todas as linhas abaixo da primeira na coluna "Empresa" (exemplo: coluna índice 0)
                        let columnIndex = 0; // Ajuste para o índice correto da coluna "Empresa"
                        for (let i = 1; i < doc.content[1].table.body.length; i++) {
                            doc.content[1].table.body[i][columnIndex].text = 'Teste ' + i;
                        }

                        // Modifica os textos na coluna "Arquivo" (exemplo: coluna índice 2)
                        let fileColumnIndex = 2; // Ajuste para a posição correta da coluna "Arquivo"
                        for (let i = 1; i < doc.content[1].table.body.length; i++) { 
                            let cellText = doc.content[1].table.body[i][fileColumnIndex].text;

                            // Se a célula contém "N/C" (com span HTML), altera para "N/C", senão altera para "C/D"
                            if (cellText.includes('N/C')) {
                                doc.content[1].table.body[i][fileColumnIndex].text = 'N/C';
                            } else {
                                doc.content[1].table.body[i][fileColumnIndex].text = 'C/D';
                            }
                        }

                        // Adiciona legenda ao final do documento
                        doc.content.push(
                            {
                                text: '\nLegenda:',
                                bold: true,
                                margin: [0, 20, 0, 5] // Espaçamento superior
                            },
                            {
                                text: 'N/C - Nada Consta\nC/D - Consta Documento',
                                fontSize: 10
                            }
                        );
                    },
                },
                {
                    extend: 'print',
                    text: 'Imprimir',
                    className: 'btn btn-sm btn-outline-secondary',
                    title: function() {
                        return 'Relatório de Exportação - ' + new Date().toLocaleString();
                    },
                    exportOptions: {
                        columns: ':not(:last-child)', // Exclui a última coluna do export
                        modifier: {
                            search: 'applied', // Mantém filtros ativos na exportação
                            page: 'all' // Exporta todas as páginas da tabela
                        }
                    },
                }
            ],
            columns: [
                { data: 'company', width: '25%' },
                { data: 'document_type', width: '20%' },
                { data: 'document', width: '10%' },
                { data: 'expiration_date', width: '12.5%' },
                {
                    data: 'advance_notification',
                    className: 'text-end',
                    width: '12.5%'
                },
                {
                    data: 'status',
                    className: 'text-nowrap text-center',
                    width: '5%'
                },
                {
                    data: 'actions',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    width: '15%'
                },
            ],
            language: {
                emptyTable: "Nenhum dado disponível na tabela",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 até 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros no total)",
                lengthMenu: "Mostrar _MENU_ registros por página",
                loadingRecords: "Carregando...",
                processing: "Processando...",
                search: "Buscar:",
                zeroRecords: "Nenhum registro encontrado",
            },
            pageLength: 10,
            lengthChange: false,
            info: false,
        });

        $('#exportCSV').click(function () {
            table.button('.buttons-csv').trigger();
        });

        $('#exportXLS').click(function () {
            table.button('.buttons-excel').trigger();
        });

        // $('#exportPDF').click(function () {
        //     table.button('.buttons-pdf').trigger();
        // });

        $('#exportPDF').click(function () {
            var companyFilter = $('#companyFilter').val();
            var documentTypeFilter = $('#documentTypeFilter').val();
            var startDateFilter = $('#startDateFilter').val();
            var endDateFilter = $('#endDateFilter').val();
            var statusFilter = $('#statusFilter').val();

            // Construir a URL com os parâmetros dos filtros
            var url = '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/export/pdf.php?export=pdf';

            if (companyFilter) {
                url += '&companyFilter=' + encodeURIComponent(companyFilter);
            }
            if (documentTypeFilter) {
                url += '&documentTypeFilter=' + encodeURIComponent(documentTypeFilter);
            }
            if (startDateFilter) {
                url += '&startDateFilter=' + encodeURIComponent(startDateFilter);
            }
            if (endDateFilter) {
                url += '&endDateFilter=' + encodeURIComponent(endDateFilter);
            }
            if (statusFilter) {
                url += '&statusFilter=' + encodeURIComponent(statusFilter);
            }

            // Redirecionar para a URL construída
            window.location.href = url;
        });

        $('#exportDocx').click(function () {
            var companyFilter = $('#companyFilter').val();
            var documentTypeFilter = $('#documentTypeFilter').val();
            var startDateFilter = $('#startDateFilter').val();
            var endDateFilter = $('#endDateFilter').val();
            var statusFilter = $('#statusFilter').val();

            // Construir a URL com os parâmetros dos filtros
            var url = '<?= INCLUDE_PATH_DASHBOARD; ?>back-end/user/document/export/docx.php?export=docx';

            if (companyFilter) {
                url += '&companyFilter=' + encodeURIComponent(companyFilter);
            }
            if (documentTypeFilter) {
                url += '&documentTypeFilter=' + encodeURIComponent(documentTypeFilter);
            }
            if (startDateFilter) {
                url += '&startDateFilter=' + encodeURIComponent(startDateFilter);
            }
            if (endDateFilter) {
                url += '&endDateFilter=' + encodeURIComponent(endDateFilter);
            }
            if (statusFilter) {
                url += '&statusFilter=' + encodeURIComponent(statusFilter);
            }

            // Redirecionar para a URL construída
            window.location.href = url;
        });

        $('#exportPrint').click(function () {
            table.button('.buttons-print').trigger();
        });

        // Limpar filtros ao clicar no botão "Limpar Filtros"
        $('#clearFiltersButton').on('click', function () {
            // Limpar os valores dos filtros
            $('#filterForm')[0].reset();

            // Remover filtros aplicados da listagem
            $('#filterList').empty();
            $('#companyFilter, #documentTypeFilter, #statusFilter').val(null).trigger('change');

            // Atualizar a tabela com os filtros limpos
            table.draw();

            // Ocultar o título "Filtros" se não houver filtros aplicados
            $('.active-filters').addClass('d-none');
            $('#clearFiltersButton').addClass('d-none');

            // Fechar o offcanvas
            $('#filterOffcanvas').offcanvas('hide');
        });

        // Capturar o evento de clique do botão "Aplicar Filtros"
        $('#applyFiltersButton').on('click', function () {
            // Função para formatar a data no padrão DD/MM/YYYY
            function formatDateToBR(date) {
                if (!date) return ''; // Verifica se a data é válida
                const [year, month, day] = date.split('-'); // Divide a data no formato ISO (YYYY-MM-DD)
                return `${day}/${month}/${year}`; // Retorna no formato DD/MM/YYYY
            }

            // Capturar os valores do formulário
            const companyFilterVal = $('#companyFilter').val();
            const companyFilterText = $('#companyFilter option:selected').text();
            const documentTypeFilterVal = $('#documentTypeFilter').val();
            const documentTypeFilterText = $('#documentTypeFilter option:selected').text();
            const startDateFilterVal = $('#startDateFilter').val();
            const endDateFilterVal = $('#endDateFilter').val();
            const statusFilterVal = $('#statusFilter').val();
            const statusFilterText = $('#statusFilter option:selected').text();

            // Verificação de validação dos campos obrigatórios
            if (startDateFilterVal && endDateFilterVal && startDateFilterVal > endDateFilterVal) {
                // Exibir o erro
                $('<div id="dateError" class="invalid-feedback">A data de término deve ser maior que a data de início.</div>')
                    .insertAfter('.date-fields').show(); // Adiciona a mensagem de erro após o campo de data de término

                return; // Impede que o filtro seja aplicado
            } else {
                // Remove o erro se os campos forem corrigidos
                $('#dateError').remove();
            }

            // Formatar as datas para o padrão brasileiro
            const startDateFormatted = formatDateToBR(startDateFilterVal);
            const endDateFormatted = formatDateToBR(endDateFilterVal);

            // Adicionar filtros aplicados na listagem
            $('#filterList').empty();

            if (companyFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${companyFilterText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (documentTypeFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${documentTypeFilterText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="documentType">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (startDateFilterVal || endDateFilterVal) {
                const dateRange = startDateFormatted && endDateFormatted
                    ? `${startDateFormatted} - ${endDateFormatted}`
                    : (startDateFormatted ? `De ${startDateFormatted}` : (endDateFormatted ? `Até ${endDateFormatted}` : null));

                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${dateRange}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="dateRange">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }
            if (statusFilterVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${statusFilterText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }

            // Verificar se existem filtros aplicados e mostrar ou ocultar o título "Filtros"
            if ($('#filterList').children().length > 0) {
                $('.active-filters').removeClass('d-none');
                $('#clearFiltersButton').removeClass('d-none');
            } else {
                $('.active-filters').addClass('d-none');
                $('#clearFiltersButton').addClass('d-none');
            }

            // Atualizar a tabela com os filtros
            table.draw();

            // Fechar o offcanvas
            $('#filterOffcanvas').offcanvas('hide');
        });

        // Capturar o evento de alteração do select "Filtrar por status"
        $('#statusFast').on('change', function () {
            const statusFastVal = $('#statusFast').val();
            const statusFastText = $('#statusFast option:selected').text();

            // Adicionar filtros aplicados na listagem
            $('#filterList').empty();

            // Atualizar o select statusFilter com o mesmo valor selecionado
            $('#statusFilter').val(statusFastVal).trigger('change');

            if (statusFastVal) {
                $('#filterList').append(`
                    <span class="bg-light border rounded dropdown-toggle fw-medium text-black d-flex align-items-center fs-12 py-1 px-2">
                        ${statusFastText}
                        <button type="button" class="remove-filter btn btn-sm btn-light lh-1 p-1 ms-1 d-flex align-items-center" data-filter="company">
                            <i class="mdi mdi-close fs-11 align-middle"></i>
                        </button>
                    </span>
                `);
            }

            // Verificar se existem filtros aplicados e mostrar ou ocultar o título "Filtros"
            if ($('#filterList').children().length > 0) {
                $('.active-filters').removeClass('d-none');
                $('#clearFiltersButton').removeClass('d-none');
            } else {
                $('.active-filters').addClass('d-none');
                $('#clearFiltersButton').addClass('d-none');
            }

            // Atualizar a tabela com os filtros
            table.draw();

            // Fechar o offcanvas
            $('#filterOffcanvas').offcanvas('hide');
        });

        // Remover filtros aplicados
        $('#filterList').on('click', '.remove-filter', function () {
            const filterType = $(this).data('filter');
            
            // Remover o filtro
            if (filterType === 'dateRange') {
                $('#startDateFilter').val('');
                $('#endDateFilter').val('');
                $('#endDateFilter').attr('min', '');
            } else {
                $(`#${filterType}Filter`).val(null).trigger('change');
            }

            // Remover o item do filtro da listagem
            $(this).closest('span').remove();

            // Atualizar a tabela com os filtros
            table.draw();

            // Verificar se existem filtros aplicados e mostrar ou ocultar o título "Filtros"
            if ($('#filterList').children().length === 0) {
                $('.active-filters').addClass('d-none');
                $('#clearFiltersButton').addClass('d-none');
            }

        });

        // Custom pagination control
        let currentPage = 1;
        let totalPages = 1;

        // Função para atualizar os controles de paginação
        function updatePagination() {
            const info = table.page.info();
            totalPages = info.pages;

            // Atualiza a informação de páginas
            $('#info').text(`${currentPage} de ${totalPages}`);

            // Atualiza os estados dos botões de "Anterior" e "Próxima"
            $('#prevPage').parent().toggleClass('disabled', currentPage === 1);
            $('#nextPage').parent().toggleClass('disabled', currentPage === totalPages);

            // Limpa números de páginas existentes
            $('#pagination').find('.page-number').remove();

            // Adiciona os números de páginas dinamicamente
            for (let i = 1; i <= totalPages; i++) {
                const isActive = (i === currentPage) ? 'active' : '';
                $('#pagination').find('.liNextPage').before(
                    `<li class="page-item ${isActive}">
                        <a class="page-link rounded-2 me-2 page-number" href="#" data-page="${i}">${i}</a>
                    </li>`
                );
            }
        }

        // Clique no número da página
        $('#pagination').on('click', '.page-number', function (e) {
            e.preventDefault();
            currentPage = parseInt($(this).data('page'));
            table.page(currentPage - 1).draw(false);
            updatePagination();
        });

        // Clique no botão "Anterior"
        $('#prevPage').on('click', function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                table.page('previous').draw(false);
                updatePagination();
            }
        });

        // Clique no botão "Próxima"
        $('#nextPage').on('click', function (e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                table.page('next').draw(false);
                updatePagination();
            }
        });

        // Atualiza a paginação após o carregamento da tabela
        table.on('draw', function () {
            updatePagination();
        });

        // Atualização inicial da paginação
        updatePagination();
    });
</script>