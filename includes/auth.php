




<?php
session_start();

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    $user = getCurrentUser();
    if (!in_array($user['role'], (array)$roles)) {
        header('Location: dashboard.php');
        exit;
    }
}

function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function register($data) {
    $db = getDB();
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (email, password, first_name, last_name, phone, role, city, address, postal_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $data['email'],
        $hashedPassword,
        $data['first_name'],
        $data['last_name'],
        $data['phone'] ?? null,
        $data['role'],
        $data['city'] ?? null,
        $data['address'] ?? null,
        $data['postal_code'] ?? null
    ]);
}

function getUnreadMessagesCount($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM messages m
        JOIN conversations c ON m.conversation_id = c.id
        WHERE (c.user1_id = ? OR c.user2_id = ?) 
        AND m.sender_id != ? 
        AND m.is_read = 0
    ");
    $stmt->execute([$userId, $userId, $userId]);
    return $stmt->fetchColumn();
}
?>