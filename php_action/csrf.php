<?php
/*
 * csrf.php — Reusable CSRF token helper
 * /apps/admin/php_action/csrf.php
 *
 * Usage:
 *   In a form:    <?php echo csrf_field(); ?>
 *   In a handler: csrf_verify();   // exits with 403 on failure
 *
 * Tokens are stored in $_SESSION['csrf_token'] and are regenerated
 * per session (not per request) to avoid breaking multi-tab workflows.
 * For higher security you can switch to per-form tokens.
 */

if(!function_exists('csrf_token')) {

    function csrf_token(): string
    {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if(empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Outputs a hidden <input> tag with the CSRF token.
     * Call inside any <form> that submits via POST.
     */
    function csrf_field(): string
    {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Validates the token submitted with a POST request.
     * Exits with HTTP 403 on failure — never returns false silently.
     */
    function csrf_verify(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';
        $expected  = csrf_token();

        // hash_equals() prevents timing attacks
        if(!hash_equals($expected, $submitted)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'messages' => 'Invalid security token. Please refresh and try again.']);
            exit();
        }
    }
}
