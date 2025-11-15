<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Check if user is admin
function isAdmin() {
    return hasRole('admin');
}

// Check if user is seller
function isSeller() {
    return hasRole('seller');
}

// Check if user is buyer
function isBuyer() {
    return hasRole('buyer');
}

// Check if user is agent
function isAgent() {
    return hasRole('agent');
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

// Redirect if not seller
function requireSeller() {
    requireLogin();
    if (!isSeller() && !isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

// Redirect if not agent
function requireAgent() {
    requireLogin();
    if (!isAgent() && !isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
