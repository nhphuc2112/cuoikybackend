<?php
session_start();
require_once 'config/database.php';
require_once 'auth/auth.php';
require_once 'models/Todo.php';

$auth = new Auth($conn);
$todo = new Todo($conn);

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$priority = isset($_GET['priority']) ? $_GET['priority'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$due_date = isset($_GET['due_date']) ? $_GET['due_date'] : '';

$todos = $todo->getTodos($user_id, $status, $priority, $search, $due_date, $page, $limit);
$total_todos = $todo->getTotalTodos($user_id, $status, $priority, $search, $due_date);
$total_pages = ceil($total_todos / $limit);

// Build query string for pagination
$query_params = [];
if ($status !== '') $query_params['status'] = $status;
if ($priority !== '') $query_params['priority'] = $priority;
if ($search !== '') $query_params['search'] = $search;
if ($due_date !== '') $query_params['due_date'] = $due_date;

$query_string = http_build_query($query_params);
$pagination_url = '?' . $query_string . ($query_string ? '&' : '') . 'page=';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Todolist</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-light">Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="nav-link" href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search todos..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status" class="form-select" style="width: auto;">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chưa xong</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    </select>
                    <select name="priority" class="form-select" style="width: auto;">
                        <option value="">Quan trọng</option>
                        <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Bình thường</option>
                        <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Tương đối quan trọng</option>
                        <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>Quan trọng</option>
                    </select>
                    <input type="date" name="due_date" class="form-control" style="width: auto;" value="<?php echo htmlspecialchars($due_date); ?>">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTodoModal">
                    <i class="fas fa-plus"></i> Thêm công việc
                </button>
            </div>
        </div>

        <div class="row">
            <?php foreach ($todos as $item): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?php echo $item['priority'] === 'high' ? 'danger' : ($item['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($item['priority']); ?>
                            </span>
                            <span class="badge bg-<?php echo $item['status'] === 'completed' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </div>
                        <?php if ($item['due_date']): ?>
                        <div class="mt-2">
                            <small class="text-muted">Ngày hết hạn: <?php echo date('Y-m-d', strtotime($item['due_date'])); ?></small>
                        </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-primary" onclick="updateStatus(<?php echo $item['id']; ?>, '<?php echo $item['status'] === 'completed' ? 'pending' : 'completed'; ?>')">
                                <?php echo $item['status'] === 'completed' ? 'Mark Pending' : 'Mark Completed'; ?>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTodo(<?php echo $item['id']; ?>)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $pagination_url . $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Add Todo Modal -->
    <div class="modal fade" id="addTodoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm công việc mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTodoForm">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả công việc</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mức độ quan trọng</label>
                            <select class="form-select" name="priority" required>
                                <option value="low">Ổn</option>
                                <option value="medium">Tương đối quan trọng</option>
                                <option value="high">Quan trọng</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ngày hết hạn</label>
                            <input type="date" class="form-control" name="due_date">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Thoát</button>
                    <button type="button" class="btn btn-primary" onclick="addTodo()">Thêm công việc</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addTodo() {
            const form = document.getElementById('addTodoForm');
            const formData = new FormData(form);
            
            fetch('api/add_todo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error adding todo');
                }
            });
        }

        function updateStatus(todoId, newStatus) {
            fetch('api/update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    todo_id: todoId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status');
                }
            });
        }

        function deleteTodo(todoId) {
            if (confirm('Are you sure you want to delete this todo?')) {
                fetch('api/delete_todo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        todo_id: todoId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting todo');
                    }
                });
            }
        }
    </script>
</body>
</html> 