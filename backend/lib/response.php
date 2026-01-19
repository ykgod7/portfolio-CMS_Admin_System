<?php
function json_ok($data = null, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
  exit;
}

function json_error(string $code, string $message, int $status): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['success' => false, 'error' => ['code' => $code, 'message' => $message]], JSON_UNESCAPED_UNICODE);
  exit;
}
