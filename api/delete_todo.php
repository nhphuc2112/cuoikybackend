<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Todo.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['todo_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing todo ID']);
        exit();
    }
    
    $todo = new Todo($conn);
    $result = $todo->delete($data['todo_id'], $_SESSION['user_id']);
    
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 