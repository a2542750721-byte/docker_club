<?php
// 资源管理模块 - 处理资源上传和下载

require_once __DIR__ . '/../includes/functions.php';

// 检查用户权限（是否为管理员）
function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}

// 获取资源列表
function getResources($limit = null, $offset = 0, $category = null) {
    global $pdo;
    
    $sql = "SELECT * FROM resources";
    $params = [];
    
    if ($category && $category !== 'all') {
        $sql .= " WHERE category = :category";
        $params[':category'] = $category;
    }
    
    $sql .= " ORDER BY date_created DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

// 获取单个资源
function getResource($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// 添加资源
function addResource($title, $description, $category, $file_path, $file_size, $file_type) {
    global $pdo;
    
    if (!isAdmin()) {
        return ['success' => false, 'message' => '权限不足'];
    }
    
    $sql = "INSERT INTO resources (title, description, category, file_path, file_size, file_type, date_created) 
            VALUES (:title, :description, :category, :file_path, :file_size, :file_type, NOW())";
    $stmt = $pdo->prepare($sql);
    
    try {
        $result = $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':category' => $category,
            ':file_path' => $file_path,
            ':file_size' => $file_size,
            ':file_type' => $file_type
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => '资源添加成功', 'id' => $pdo->lastInsertId()];
        } else {
            return ['success' => false, 'message' => '资源添加失败'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => '数据库错误: ' . $e->getMessage()];
    }
}

// 更新资源
function updateResource($id, $title, $description, $category) {
    global $pdo;
    
    if (!isAdmin()) {
        return ['success' => false, 'message' => '权限不足'];
    }
    
    $sql = "UPDATE resources SET title = :title, description = :description, category = :category 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    try {
        $result = $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':description' => $description,
            ':category' => $category
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => '资源更新成功'];
        } else {
            return ['success' => false, 'message' => '资源更新失败'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => '数据库错误: ' . $e->getMessage()];
    }
}

// 删除资源
function deleteResource($id) {
    global $pdo;
    
    if (!isAdmin()) {
        return ['success' => false, 'message' => '权限不足'];
    }
    
    // 获取文件路径以便删除实际文件
    $resource = getResource($id);
    if ($resource && file_exists($resource['file_path'])) {
        unlink($resource['file_path']);
    }
    
    $sql = "DELETE FROM resources WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    try {
        $result = $stmt->execute([':id' => $id]);
        
        if ($result) {
            return ['success' => true, 'message' => '资源删除成功'];
        } else {
            return ['success' => false, 'message' => '资源删除失败'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => '数据库错误: ' . $e->getMessage()];
    }
}

// 处理文件上传
function uploadResource($file, $category) {
    global $pdo;
    
    if (!isAdmin()) {
        return ['success' => false, 'message' => '权限不足'];
    }
    
    $upload_dir = __DIR__ . '/../assets/uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = basename($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    
    // 验证文件类型
    $allowed_types = ['application/pdf', 'application/zip', 'application/x-rar-compressed', 
                      'application/x-tar', 'application/x-7z-compressed', 'text/plain', 
                      'text/html', 'text/css', 'application/javascript', 'image/jpeg', 
                      'image/png', 'image/gif'];
    
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => '不支持的文件类型'];
    }
    
    // 生成唯一文件名
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $unique_filename;
    
    if (move_uploaded_file($file_tmp, $file_path)) {
        return addResource($file_name, "上传的资源", $category, $file_path, $file_size, $file_type);
    } else {
        return ['success' => false, 'message' => '文件上传失败'];
    }
}

// 处理资源管理请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_action'])) {
    $action = $_POST['resource_action'];
    
    if ($action === 'add' && isAdmin()) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        
        $result = addResource($title, $description, $category, '', 0, '');
        echo json_encode($result);
    } elseif ($action === 'update' && isAdmin()) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        
        $result = updateResource($id, $title, $description, $category);
        echo json_encode($result);
    } elseif ($action === 'delete' && isAdmin()) {
        $id = $_POST['id'];
        
        $result = deleteResource($id);
        echo json_encode($result);
    } elseif ($action === 'upload' && isAdmin()) {
        if (isset($_FILES['resource_file'])) {
            $category = $_POST['category'];
            $result = uploadResource($_FILES['resource_file'], $category);
            echo json_encode($result);
        }
    }
}

// 处理资源下载
if (isset($_GET['download']) && is_numeric($_GET['download'])) {
    $resource_id = (int)$_GET['download'];
    $resource = getResource($resource_id);
    
    if ($resource) {
        $file_path = $resource['file_path'];
        if (file_exists($file_path)) {
            // 增加下载计数
            $stmt = $pdo->prepare("UPDATE resources SET download_count = download_count + 1 WHERE id = ?");
            $stmt->execute([$resource_id]);
            
            // 提供文件下载
            header('Content-Type: ' . $resource['file_type']);
            header('Content-Disposition: attachment; filename="' . basename($resource['title']) . '"');
            header('Content-Length: ' . $resource['file_size']);
            readfile($file_path);
            exit;
        } else {
            http_response_code(404);
            echo "文件不存在";
        }
    } else {
        http_response_code(404);
        echo "资源不存在";
    }
}
?>