<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../lib/response.php';

$_SESSION = [];

$cookieName = session_name(); // 기본은 PHPSESSID

$params = session_get_cookie_params(); // 세션 쿠키 파라미터 가져오기(경로/도메인 등)

// 만료로 쿠키 삭제
setcookie($cookieName, '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);

session_destroy();

json_ok(true);
