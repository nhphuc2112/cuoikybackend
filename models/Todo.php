<?php
require_once __DIR__ . '/../config/database.php';

class Todo {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($user_id, $title, $description, $priority, $due_date) {
        try {
            $stmt = $this->conn->prepare("CALL CreateTodo(?, ?, ?, ?, ?)");
            return $stmt->execute([$user_id, $title, $description, $priority, $due_date]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getTodos($user_id, $status = null, $priority = null, $search = null, $due_date = null, $page = 1, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("CALL GetTodos(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $status, $priority, $search, $due_date, $page, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function updateStatus($todo_id, $user_id, $status) {
        try {
            $stmt = $this->conn->prepare("CALL UpdateTodoStatus(?, ?, ?)");
            return $stmt->execute([$todo_id, $user_id, $status]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function delete($todo_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM todos WHERE id = ? AND user_id = ?");
            return $stmt->execute([$todo_id, $user_id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getTotalTodos($user_id, $status = null, $priority = null, $search = null, $due_date = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM todos WHERE user_id = ?";
            $params = [$user_id];

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            if ($priority) {
                $sql .= " AND priority = ?";
                $params[] = $priority;
            }
            if ($search) {
                $sql .= " AND (title LIKE ? OR description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if ($due_date) {
                $sql .= " AND due_date = ?";
                $params[] = $due_date;
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch(PDOException $e) {
            return 0;
        }
    }
}
?> 