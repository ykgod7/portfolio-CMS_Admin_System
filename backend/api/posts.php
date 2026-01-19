<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/request.php';
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  json_error('UNAUTHORIZED', '로그인이 필요합니다.', 401);
}

$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'GET') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $q = trim($_GET['q'] ?? '');
  $page = max(1, (int)($_GET['page'] ?? 1));
  $size = min(50, (int)($_GET['size'] ?? 20));
  $offset = ($page - 1) * $size;
  $include_deleted = (int)$_GET['include_deleted'] ?? 0;

  $whereParts  = [];
  $params = [];

  if ($include_deleted !== 1) {
    $whereParts[] = 'p.deleted_at IS NULL';
  }

  if ($q !== '') {
    $whereParts[] = '(p.title LIKE :q OR u.name LIKE :q)';
    $params[':q'] = '%' . $q . '%';
  }

  $where = '';
  if (!empty($whereParts)) {
    $where = 'WHERE ' . implode(' AND ', $whereParts);
  }

  try {
    // 상세게시글 가져오기
    if ($id > 0) {
      $stmt = $pdo->prepare(
      'SELECT p.id, p.author_id, u.name AS author_name, p.title, p.content, p.created_at
      FROM posts p
      JOIN users u ON u.id = p.author_id
      WHERE p.id = ? AND p.deleted_at IS NULL
      LIMIT 1'
      );

      $stmt->execute([$id]);
      $post = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$post) {
        json_error('NOT_FOUND', '게시글이 존재하지 않습니다', 404);
      }

      json_ok($post);
      exit;
    }
    
    // 게시글 목록 가져오기
    $stmt = $pdo->prepare(
      "SELECT p.id, p.author_id, u.name AS author_name, p.title, p.created_at
      FROM posts p
      JOIN users u ON u.id = p.author_id
      $where
      ORDER BY p.created_at DESC, p.id DESC
      LIMIT :limit OFFSET :offset"  
    );
    
    foreach ($params as $k => $v) {
      $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $size, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 게시글 전체 개수 가져오기
    $countStmt = $pdo->prepare(
      "SELECT COUNT(*)
      FROM posts p
      JOIN users u ON u.id = p.author_id
      $where"
    );
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    json_ok([
      'items' => $posts,
      'total' => $total,
      'page'  => $page,
      'size'  => $size,
      'q'     => $q
    ]);
  } catch (Throwable $e) {
    json_error('DB_ERROR', 'DB 연결을 확인하세요', 500);
  }
} elseif ($method === 'POST') {
  // 신규 게시글 생성
  $body = read_json();

  if (!is_array($body)) {
    json_error('BAD_REQUEST', '요청 형식이 올바르지 않습니다.', 400);
  }

  $title = trim($body['title'] ?? '');
  $content = trim($body['content'] ?? '');

  if (mb_strlen($title) < 2 || mb_strlen($title) > 120) {
    json_error('BAD_REQUEST', '제목은 2~120자여야 합니다.', 400);
  }

  if (mb_strlen($content) < 5) {
    json_error('BAD_REQUEST', '내용은 5자 이상이어야 합니다.', 400);
  }

  try {
    $author_id = (int)$_SESSION['user_id'];
  
    $stmt = $pdo->prepare(
      "INSERT INTO posts (author_id, title, content, created_at)
      VALUES (:author_id, :title, :content, NOW())"
    );
  
    $stmt->execute([
      ':author_id' => $author_id,
      ':title' => $title,
      ':content' => $content,
    ]);
  
    json_ok(['id' => $newId], 201); 
  } catch (Throwable $e) {
    error_log($e->getMessage());
    json_error('DB_ERROR', '게시글 생성 실패', 500);
  }
} elseif ($method === 'DELETE') {
  // id 가져오기
  $id = (int)($_GET['id'] ?? 0);
  if ($id <= 0) json_error('BAD_REQUEST', 'id가 필요합니다', 400);

  try {
    // 게시글 조회
    $stmt = $pdo->prepare(
      "SELECT id, author_id, deleted_at
      FROM posts
      WHERE id = :id
      LIMIT 1"
    );
    $stmt->execute([
      ':id' => $id
    ]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
  
    if (!$post || $post['deleted_at']) {
      json_error('NOT_FOUND', '게시글이 존재하지 않습니다', 404);
    }

    // 작성자와 현재 유저의 id가 일치한지
    $me = (int)$_SESSION['user_id'];
    error_log($_SESSION['role']);

    if ($_SESSION['role'] !== 'admin') {
      if ((int)$post['author_id'] !== $me) json_error('FORBIDDEN', '삭제 권한이 없습니다', 403);
    }
  
    $updateStmt = $pdo->prepare(
      "UPDATE posts
      SET deleted_at = NOW()
      WHERE id = :id AND deleted_at IS NULL"
    );
    $updateStmt->execute([
      ':id' => $id
    ]);

    if ($updateStmt->rowCount() === 0) {
      json_error('NOT_FOUND', '이미 삭제되었거나 존재하지 않습니다', 404);
      exit;
    }
  
    json_ok(['id' => $id]);
  } catch (Throwable $e) {
    error_log($e->getMessage());
    json_error('DB_ERROR', '게시글 삭제 실패', 500);
  }
} else {

}