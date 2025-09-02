<?php
session_start();
session_unset();
session_destroy();
// Remove the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// Optionally clear localStorage for UI state
// This can be done with a small JS snippet after redirect if needed
header('Location: login.php');
exit();
?>
</rewritten_file> 