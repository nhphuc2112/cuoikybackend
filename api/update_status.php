<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Todo.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Khong co quyen truy cap']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['todo_id']) || !isset($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Thieu thong tin']);
        exit();
    }
    
    $todo = new Todo($conn);
    $result = $todo->updateStatus(
        $data['todo_id'],
        $_SESSION['user_id'],
        $data['status']
    );
    
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Sai phuong thuc, vui long su dung POST']);
}
?> 