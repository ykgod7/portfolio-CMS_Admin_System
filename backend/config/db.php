<?php
// DB 설정 (로컬 개발 기준)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'simple_cms';
$DB_USER = 'root';
$DB_PASS = '0000';

// DSN
$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'success' => false,
    'error' => [
      'code' => 'DB_CONNECTION_FAILED',
      'message' => 'DB 연결 실패',
    ],
  ], JSON_UNESCAPED_UNICODE);
  exit;
}
