<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: /?logout=1'); exit; }
$user = $_SESSION['user'];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['doc'])) {
    $f = $_FILES['doc'];
    if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 8 * 1024 * 1024) {
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($f['name']));
        move_uploaded_file($f['tmp_name'], '/var/www/uploads/' . $safe);
        $msg = 'Uploaded: ' . htmlspecialchars($safe);
    } else {
        $msg = 'Upload failed (max 8MB).';
    }
}
$files = array_values(array_diff(scandir('/var/www/uploads'), ['.', '..']));
?>
<!DOCTYPE html>
<html><head><title>PhpVault - Dashboard</title>
<style>
body{font-family:Arial,sans-serif;background:#1c2333;color:#e6e6e6;margin:0}
.wrap{max-width:640px;margin:50px auto;background:#273043;padding:28px;border-radius:8px}
li{margin:4px 0} .msg{color:#51cf66} a{color:#74c0fc}
input[type=file]{margin:8px 0} button{background:#e8590c;color:#fff;border:0;padding:8px 16px;cursor:pointer}
table{width:100%;border-collapse:collapse} td{padding:4px 8px;border-bottom:1px solid #3a4560}
</style></head>
<body><div class="wrap">
<h1>Dashboard</h1>
<p>Signed in as <b><?= htmlspecialchars($user) ?></b> · <?= $user === 'backup' ? '<a href="/backup.php">backup audit</a> · ' : '' ?><?= $user === 'backup' || $user === 'demo' ? '<a href="/about.php">runtime notes</a> · ' : '' ?><a href="/?logout=1">logout</a></p>
<?php if ($msg) echo '<p class="msg">'.htmlspecialchars($msg).'</p>'; ?>
<form method="post" enctype="multipart/form-data">
<input type="file" name="doc">
<button>Upload document</button>
<p style="font-size:11px;color:#888">Server-side upload progress tracking is enabled for large documents.</p>
</form>
<h3>Documents</h3><table>
<?php foreach ($files as $f): ?>
<tr><td><a href="/uploads/<?= rawurlencode($f) ?>"><?= htmlspecialchars($f) ?></a></td><td><?= filesize('/var/www/uploads/'.$f) ?> bytes</td></tr>
<?php endforeach; if (!$files): ?><tr><td>No documents yet.</td></tr><?php endif; ?>
</table>
<p style="font-size:11px;color:#777;margin-top:24px">PhpVault 2.4.1 · hardened build · all requests sanitized</p>
</div></body></html>
