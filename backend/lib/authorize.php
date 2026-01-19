<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/response.php';

function require_admin() {
  $user_id = $_SESSION['user_id'] ?? null;
  if (!$user_id) {
    json_error('UNAUTHORIZED', '로그인이 필요합니다.', 401);
  }
  
  $user_role = $_SESSION['role'] ?? null;
  if ($user_role !== 'admin') {
    json_error('FORBIDDEN', '관리자 계정이 아닙니다.', 403);
  }
}