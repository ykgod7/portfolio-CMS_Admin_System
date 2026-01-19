<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/response.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
  try {
    $stmt = $pdo->prepare(
      'SELECT id, email, name, role
       FROM users
       WHERE id = ?
       LIMIT 1'
    );
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
      $_SESSION = [];
      json_error('UNAUTHORIZED', '로그인이 필요합니다.', 401);
    }

    json_ok($user);

  } catch (Throwable $e) {
    $_SESSION = [];
    json_error('SERVER_ERROR', '서버 오류가 발생했습니다.', 500);
  }
} else {
  json_error('UNAUTHORIZED', '로그인이 필요합니다.', 401);
}
