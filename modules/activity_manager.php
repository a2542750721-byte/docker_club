<?php
// 活动管理模块 - 处理活动的增删改查

require_once __DIR__ . '/../includes/functions.php';

// 检查用户权限（是否为管理员）
function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}

// 获取活动列表
function getActivities($limit = null, $offset = 0) {
    global $pdo;
    
    $sql = "SELECT * FROM activities ORDER BY date_created DESC";
    if ($limit) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

// 获取单个活动
function getActivity($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// 添加活动
function addActivity($title, $description, $date, $location, $category) {
    global $pdo;
    
    if (!isAdmin()) {
        return ['success' => false, 'message' => '权限不足'];
    }
    
    $sql = "INSERT INTO activities (title, description, date, location, category, date_created) 
            VALUES (:title, :description, :date, :location, :category, NOW())";
    $stmt = $pdo->prepare($sql);
    
    try {
        $result = $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':date' => $date,
            ':location' => $location,
            ':category' => $category
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => '活动添加成功', 'id' => $pdo->lastInsertId()];
        } else {
            return ['success' => false, 'message' => '活动添加失败'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => '数据库错误: ' . $e->getMessage()];
    }
}

// 更新活动
function updateActivity($id, $title, $description, $date, $location, $category) {
    global $pdo;
    
    if (!isAdmin()) {
        return ['success' => false, 'message' => '权限不足'];
    }
    
    $sql = "UPDATE activities SET title = :title, description = :description, 
                   date = :date, location = :location, category = :category 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    try {
        $result = $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':description' => $description,
            ':date' => $date,
            ':location' => $location,
            ':category' => $category
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => '活动更新成功'];
        } else {
            return ['success' => false, 'message' => '活动更新失败'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => '数据库错误: ' . $e->getMessage()];
    }
}

// 删除活动
function deleteActivity($id) {
    global $pdo;
    
    if (!isAdmin()) {
        return ['success' => false, 'message' => '权限不足'];
    }
    
    $sql = "DELETE FROM activities WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    try {
        $result = $stmt->execute([':id' => $id]);
        
        if ($result) {
            return ['success' => true, 'message' => '活动删除成功'];
        } else {
            return ['success' => false, 'message' => '活动删除失败'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => '数据库错误: ' . $e->getMessage()];
    }
}

// 处理活动管理请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activity_action'])) {
    $action = $_POST['activity_action'];
    
    if ($action === 'add' && isAdmin()) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $location = $_POST['location'];
        $category = $_POST['category'];
        
        $result = addActivity($title, $description, $date, $location, $category);
        echo json_encode($result);
    } elseif ($action === 'update' && isAdmin()) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $location = $_POST['location'];
        $category = $_POST['category'];
        
        $result = updateActivity($id, $title, $description, $date, $location, $category);
        echo json_encode($result);
    } elseif ($action === 'delete' && isAdmin()) {
        $id = $_POST['id'];
        
        $result = deleteActivity($id);
        echo json_encode($result);
    }
}
?>