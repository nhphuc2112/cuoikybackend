-- Create database
CREATE DATABASE IF NOT EXISTS todolist;
USE todolist;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create todos table
CREATE TABLE IF NOT EXISTS todos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Stored Procedure for creating a new todo
DELIMITER //
CREATE PROCEDURE CreateTodo(
    IN p_user_id INT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_priority ENUM('low', 'medium', 'high'),
    IN p_due_date DATE
)
BEGIN
    INSERT INTO todos (user_id, title, description, priority, due_date)
    VALUES (p_user_id, p_title, p_description, p_priority, p_due_date);
END //
DELIMITER ;

-- Stored Procedure for getting todos with pagination and filters
DELIMITER //
CREATE PROCEDURE GetTodos(
    IN p_user_id INT,
    IN p_status VARCHAR(20),
    IN p_priority VARCHAR(20),
    IN p_search VARCHAR(255),
    IN p_due_date DATE,
    IN p_page INT,
    IN p_limit INT
)
BEGIN
    DECLARE offset INT;
    SET offset = (p_page - 1) * p_limit;
    
    SELECT * FROM todos 
    WHERE user_id = p_user_id
    AND (p_status = '' OR p_status IS NULL OR status = p_status)
    AND (p_priority = '' OR p_priority IS NULL OR priority = p_priority)
    AND (p_search = '' OR p_search IS NULL OR title LIKE CONCAT('%', p_search, '%') OR description LIKE CONCAT('%', p_search, '%'))
    AND (p_due_date IS NULL OR p_due_date = '' OR due_date = p_due_date)
    ORDER BY created_at DESC
    LIMIT p_limit OFFSET offset;
END //
DELIMITER ;

-- Stored Procedure for updating todo status
DELIMITER //
CREATE PROCEDURE UpdateTodoStatus(
    IN p_todo_id INT,
    IN p_user_id INT,
    IN p_status VARCHAR(20)
)
BEGIN
    UPDATE todos 
    SET status = p_status
    WHERE id = p_todo_id AND user_id = p_user_id;
END //
DELIMITER ; 