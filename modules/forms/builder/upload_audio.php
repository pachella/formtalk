<?php
ob_start();
session_start();
ob_end_clean();

// Headers para bypass de TODOS os caches (incluindo Cloudflare)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: none');
// Cloudflare bypass
header('CDN-Cache-Control: no-cache');
header('CF-Cache-Status: BYPASS');

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Não autorizado']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Método inválido']));
}

if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Nenhum arquivo enviado']));
}

$file = $_FILES['audio'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Formato inválido']));
}

if ($file['size'] > 52428800) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Arquivo muito grande']));
}

$dir = __DIR__ . '/../../../uploads/audios/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$name = uniqid('aud_') . '.' . $ext;
$path = $dir . $name;

if (!move_uploaded_file($file['tmp_name'], $path)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Erro ao salvar']));
}

http_response_code(200);
die(json_encode([
    'success' => true,
    'url' => '/uploads/audios/' . $name,
    'filename' => $file['name']
]));
