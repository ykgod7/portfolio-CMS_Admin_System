<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/request.php';
require_once __DIR__ . '/../../lib/authorize.php';
require_once __DIR__ . '/../../config/db.php';

require_admin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  try {
    $stmt = $pdo->prepare(
      'SELECT id, email, name, role
      FROM users
      ORDER BY id DESC
      LIMIT 100'
      );
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    json_ok($users);
  } catch (Throwable $e) {
    json_error('DB_ERROR', 'DB 연결을 확인하세요', 500);
  }
} elseif ($method === 'PATCH') {
  $body = read_json();
  if (!is_array($body)) {
    json_error('BAD_REQUEST', '요청 형식이 올바르지 않습니다.', 400);
    exit;
  }

  $id = (int)($body['id'] ?? 0);
  $role = trim((string)($body['role'] ?? ''));

  if ($id <= 0) {
    json_error('BAD_REQUEST', 'id가 필요합니다.', 400);
    exit;
  }

  $allowedRoles = ['user', 'admin'];
  if (!in_array($role, $allowedRoles, true)) {
    json_error('BAD_REQUEST', 'role 값이 올바르지 않습니다.', 400);
    exit;
  }

  // (권장) 자기 자신의 admin 권한을 내려버리는 것 방지
  $me = (int)($_SESSION['user_id'] ?? 0);
  if ($id === $me && $role !== 'admin') {
    json_error('BAD_REQUEST', '본인 계정의 admin 권한은 해제할 수 없습니다.', 400);
    exit;
  }

  try {
    // 대상 유저 존재 확인
    $check = $pdo->prepare("SELECT id, role FROM users WHERE id = :id LIMIT 1");
    $check->execute([':id' => $id]);
    $target = $check->fetch(PDO::FETCH_ASSOC);

    if (!$target) {
      json_error('NOT_FOUND', '유저가 존재하지 않습니다.', 404);
      exit;
    }

    // 업데이트
    $upd = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
    $upd->execute([':role' => $role, ':id' => $id]);

    json_ok([
      'id' => $id,
      'role' => $role
    ]);
    exit;
  } catch (Throwable $e) {
    error_log($e->getMessage());
    json_error('DB_ERROR', 'role 변경 실패', 500);
    exit;
  }
} else {
  json_error('METHOD_NOT_ALLOWED', '허용되지 않은 메서드입니다.', 405);
  exit;
}

