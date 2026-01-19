<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/request.php';
require_once __DIR__ . '/../../config/db.php';

$body = read_json();
$email = trim($body['email'] ?? '');
$password = $body['password'] ?? '';


if ($email === '' || $password === '') {
  json_error('VALIDATION_ERROR', 'email/password는 필수입니다.', 422);
}

try {
  // 1) email로 사용자 조회
  $stmt = $pdo->prepare(
    'SELECT id, email, name, role, password_hash
     FROM users
     WHERE email = ?
     LIMIT 1'
  );
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  // 2) 인증 실패 처리 (보안상 동일 메시지)
  if (!$user || !isset($user['password_hash']) || !password_verify($password, (string)$user['password_hash'])) {
    json_error('INVALID_CREDENTIALS', '이메일 또는 비밀번호가 올바르지 않습니다.', 401);
  }

  // 권장: 세션 고정 공격 완화
  session_regenerate_id(true);

  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['role'] = (string)$user['role'];

  // 3) 응답
  // 프론트가 로그인 후 fetchMe()를 호출하므로 여기서는 success만 줘도 충분
  json_ok([
    'id' => (int)$user['id'],
    'email' => (string)$user['email'],
    'name' => (string)$user['name'],
    'role' => (string)$user['role'],
  ]);

} catch (PDOException $e) {
  // DB 오류
  json_error('DB_ERROR', '로그인 처리 중 DB 오류가 발생했습니다.', 500);
} catch (Throwable $e) {
  // 기타 오류
  json_error('SERVER_ERROR', '로그인 처리 중 서버 오류가 발생했습니다.', 500);
}