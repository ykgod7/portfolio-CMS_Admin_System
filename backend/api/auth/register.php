<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/request.php';
require_once __DIR__ . '/../../config/db.php';


$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
  json_error('METHOD_NOT_ALLOWED', 'POST만 허용됩니다.', 405);
  exit;
}

$body = read_json();
if (!is_array($body)) {
  json_error('BAD_REQUEST', '요청 형식이 올바르지 않습니다.', 400);
  exit;
}

// 1) 입력 받기
$name = trim($body['name'] ?? '');
$email = trim($body['email'] ?? '');
$password = (string)($body['password'] ?? '');

// 2) 서버 검증
if (mb_strlen($name) < 2 || mb_strlen($name) > 30) {
  json_error('BAD_REQUEST', '이름은 2~30자여야 합니다.', 400);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_error('BAD_REQUEST', '이메일 형식이 올바르지 않습니다.', 400);
  exit;
}

if (mb_strlen($password) < 4) {
  json_error('BAD_REQUEST', '비밀번호는 4자 이상이어야 합니다.', 400);
  exit;
}

try {
  $emailStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
  $emailStmt->execute([
    ':email' => $email
  ]);
  $exists = $emailStmt->fetchColumn();
  if ($exists) {
    json_error('DUPLICATE', '이미 등록된 이메일입니다.', 409);
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);

  $addStmt = $pdo->prepare(
    "INSERT INTO users (name, email, password_hash, role, created_at) 
    VALUES (:name, :email, :hash, :role, NOW())");
  $addStmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':hash' => $hash,
    ':role' => 'user',
  ]);

  $newId = (int)$pdo->lastInsertId();

  $_SESSION['user_id'] = $newId;
  $_SESSION['role'] = 'user';

  json_ok(['id' => $newId, 'name' => $name, 'email' => $email, 'role' => 'user']);

} catch (Throwable $e) {
  error_log($e->getMessage());
  json_error('DB_ERROR', '회원가입 실패', 500);
  exit;
}