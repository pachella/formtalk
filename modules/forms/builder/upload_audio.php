<?php
// IMPORTANTE: Não deixar NENHUM whitespace antes desta linha
// Output buffering DEVE ser a primeira coisa
ob_start();

// Desabilitar display de erros PHP
error_reporting(0);
ini_set('display_errors', 0);

// Iniciar sessão
session_start();

// Limpar qualquer output anterior
ob_clean();

// Definir header JSON IMEDIATAMENTE
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Verificar autenticação
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Não autorizado']));
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Método não permitido']));
}

try {
    // Verificar se o arquivo foi enviado
    if (!isset($_FILES['audio'])) {
        throw new Exception('Nenhum arquivo foi enviado');
    }

    // Verificar erros de upload
    if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande',
            UPLOAD_ERR_PARTIAL => 'Upload incompleto',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada',
            UPLOAD_ERR_CANT_WRITE => 'Erro ao salvar arquivo',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
        ];
        throw new Exception($errorMessages[$_FILES['audio']['error']] ?? 'Erro desconhecido');
    }

    $file = $_FILES['audio'];

    // Validar extensão
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];

    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Formato não suportado. Use MP3, WAV, OGG ou M4A');
    }

    // Validar tamanho (50MB)
    if ($file['size'] > 50 * 1024 * 1024) {
        throw new Exception('Arquivo muito grande. Máximo: 50MB');
    }

    // Validar MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a', 'audio/mp3'];
    if (!in_array($mimeType, $allowedMimes)) {
        throw new Exception('Tipo de arquivo inválido');
    }

    // Criar diretório
    $uploadDir = __DIR__ . '/../../../uploads/audios/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Erro ao criar diretório');
        }
    }

    // Gerar nome único
    $uniqueId = uniqid('audio_', true);
    $fileName = $uniqueId . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Erro ao salvar arquivo');
    }

    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'url' => '/uploads/audios/' . $fileName,
        'filename' => $file['name']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

exit();
