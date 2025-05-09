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
    $todo = new Todo($conn);
    
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        exit();
    }
    
    $result = $todo->create(
        $_SESSION['user_id'],
        $title,
        $description,
        $priority,
        $due_date
    );
    
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 