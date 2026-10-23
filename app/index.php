<?php
session_start();
$logout = isset($_GET['logout']);
if ($logout) { session_destroy(); header('Location: /'); exit; }
$authed = !empty($_SESSION['user']);
if ($authed) { header('Location: /dashboard.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    // account store read per-request (maintenance window check needs it anyway)
    $raw = @file_get_contents('/var/www/private/credentials.ini');
    $users = $raw !== false ? (parse_ini_string($raw, true) ?: []) : [];
    $stored = isset($users[$u]['password']) ? trim($users[$u]['password']) : null;
    if ($stored !== null && $p === $stored) {
        session_regenerate_id(true);
        $_SESSION['user'] = $u;
        header('Location: /dashboard.php'); exit;
    }
    $err = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html><head><title>PhpVault - Secure Document Vault</title>
<style>
body{font-family:Arial,sans-serif;background:#1c2333;color:#e6e6e6;margin:0}
.wrap{max-width:420px;margin:80px auto;background:#273043;padding:32px;border-radius:8px}
input{width:100%;padding:8px;margin:6px 0 14px;box-sizing:border-box;border:1px solid #444;background:#1c2333;color:#eee}
button{background:#e8590c;color:#fff;border:0;padding:10px 18px;cursor:pointer;width:100%}
.err{color:#ff6b6b}
h1{font-size:22px} .foot{font-size:11px;color:#777;margin-top:18px}
</style></head><body><div class="wrap">
<h1>&#128274; PhpVault</h1>
<p>Internal document vault for project documents.</p>
<?php if ($err) echo '<p class="err">'.$err.'</p>'; ?>
<form method="post">
<label>Username</label><input name="username" autocomplete="off">
<label>Password</label><input name="password" type="password" autocomplete="off">
<button>Sign in</button>
</form>
<p class="foot">demo / demo123 (demo account, reset nightly)</p>
</div></body></html>
