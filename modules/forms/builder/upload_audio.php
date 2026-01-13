<?php
session_start();
require_once(__DIR__ . "/../../../core/db.php");

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
    if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload do arquivo');
    }

    $file = $_FILES['audio'];

    // Validar tipo de arquivo
    $allowedMimes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a'];
    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mimeType, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
        throw new Exception('Formato de áudio não suportado');
    }

    // Validar tamanho (50MB)
    $maxSize = 50 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception('Arquivo muito grande. Máximo: 50MB');
    }

    // Criar diretório de uploads se não existir
    $uploadDir = __DIR__ . '/../../../uploads/audios/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Gerar nome único para o arquivo
    $uniqueId = uniqid() . '_' . time();
    $fileName = $uniqueId . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Erro ao salvar o arquivo');
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
?>
