<?php
/*
 * Shared checks for user accounts.
 */

/** Returns an error message, or null when the username is valid and free. */
function username_error($connect, $username, $exceptUserId = 0) {
    if($username === '') {
        return 'Username is required.';
    }
    if(!preg_match('/^[\p{L}\p{N} ._@-]{3,50}$/u', $username)) {
        return 'Username must be 3–50 characters: letters, numbers, spaces, dots, @, dashes or underscores.';
    }
    $stmt = $connect->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_id != ?");
    $stmt->bind_param('si', $username, $exceptUserId);
    $stmt->execute();
    $taken = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $taken ? 'That username is already taken.' : null;
}

function password_error($password) {
    if(strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    return null;
}

/** True when the logged-in user is the shared demo login (see MEL_LOCKED_DEMO_USER). */
function is_locked_demo_user($connect) {
    if(MEL_LOCKED_DEMO_USER === '') {
        return false;
    }
    $userId = current_user_id();
    $stmt = $connect->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row && $row['username'] === MEL_LOCKED_DEMO_USER;
}
