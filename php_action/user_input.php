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
