<?php
// Iniciar output buffering para capturar qualquer saída inesperada
ob_start();

// Evitar que warnings e notices sejam exibidos
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

// Tentar incluir db.php com tratamento de erro
try {
    require_once(__DIR__ . "/../../../core/db.php");
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro ao conectar ao banco de dados']);
    exit();
}

// Limpar qualquer saída anterior e definir header JSON
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticação
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

try {
    // Verificar se o arquivo foi enviado
    if (!isset($_FILES['audio'])) {
        throw new Exception('Nenhum arquivo foi enviado');
    }

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
        $errorMsg = $errorMessages[$_FILES['audio']['error']] ?? 'Erro desconhecido no upload';
        throw new Exception($errorMsg);
    }

    $file = $_FILES['audio'];

    // Validar tipo de arquivo
    $allowedMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a', 'audio/mp3'];
    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mimeType, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
        throw new Exception('Formato de áudio não suportado. Use MP3, WAV, OGG ou M4A');
    }

    // Validar tamanho (50MB)
    $maxSize = 50 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception('Arquivo muito grande. Máximo: 50MB');
    }

    // Criar diretório de uploads se não existir
    $uploadDir = __DIR__ . '/../../../uploads/audios/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Erro ao criar diretório de upload');
        }
    }

    // Gerar nome único para o arquivo
    $uniqueId = uniqid() . '_' . time();
    $fileName = $uniqueId . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Erro ao salvar o arquivo no servidor');
    }

    // Retornar URL relativa
    $fileUrl = '/uploads/audios/' . $fileName;

    echo json_encode([
        'success' => true,
        'url' => $fileUrl,
        'filename' => $fileName
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?>
