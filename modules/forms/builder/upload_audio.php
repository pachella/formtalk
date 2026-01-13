<?php
ob_start();
session_start();
ob_end_clean();

header('Content-Type: application/json');
header('Cache-Control: no-cache');

if (!isset($_SESSION["user_id"])) {
    die(json_encode(['success' => false, 'error' => 'Não autorizado']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => 'Método inválido']));
}

if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== 0) {
    die(json_encode(['success' => false, 'error' => 'Nenhum arquivo enviado']));
}

$file = $_FILES['audio'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
    die(json_encode(['success' => false, 'error' => 'Formato inválido']));
}

if ($file['size'] > 52428800) { // 50MB
    die(json_encode(['success' => false, 'error' => 'Arquivo muito grande']));
}

$dir = __DIR__ . '/../../../uploads/audios/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$name = uniqid('aud_') . '.' . $ext;
$path = $dir . $name;

if (!move_uploaded_file($file['tmp_name'], $path)) {
    die(json_encode(['success' => false, 'error' => 'Erro ao salvar']));
}

die(json_encode([
    'success' => true,
    'url' => '/uploads/audios/' . $name,
    'filename' => $file['name']
]));
