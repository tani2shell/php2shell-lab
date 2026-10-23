<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: /?logout=1'); exit; }
if ($_SESSION['user'] !== 'backup') { http_response_code(403); exit('Backup role only.'); }

$out = null;
$err = null;

// stage a link into the backup area (ops convenience: point a slot at a bundle location)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot'], $_POST['target'])) {
    $slot = preg_replace('/[^A-Za-z0-9._-]/', '_', substr($_POST['slot'], 0, 64));
    $target = $_POST['target'];
    if ($slot === '' || $target === '') {
        $err = 'slot and target required.';
    } else {
        $link = '/var/vault-backup/' . $slot;
        @unlink($link);
        // NOTE: target is taken as given; the audit tool is expected to police access.
        if (@symlink($target, $link)) $out = "staged: $link -> $target";
        else $err = 'stage failed.';
    }
}

// run the integrity auditor (suid helper) against a backup-area path
$audit = null;
if (isset($_GET['audit']) && $_GET['audit'] !== '') {
    $p = '/var/vault-backup/' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_GET['audit']));
    $o = []; $rc = -1;
    exec(escapeshellcmd('/usr/local/bin/vault-check') . ' ' . escapeshellarg($p), $o, $rc);
    $audit = ['rc' => $rc, 'out' => implode("\n", $o)];
}

$slots = array_values(array_diff(@scandir('/var/vault-backup') ?: [], ['.', '..']));
?>
<!DOCTYPE html>
<html><head><title>PhpVault - Backup Audit</title>
<style>
body{font-family:Arial,sans-serif;background:#1c2333;color:#e6e6e6;margin:0}
.wrap{max-width:680px;margin:50px auto;background:#273043;padding:28px;border-radius:8px}
a{color:#74c0fc} input{padding:6px;margin:4px 0;background:#1c2333;color:#eee;border:1px solid #444}
button{background:#e8590c;color:#fff;border:0;padding:8px 16px;cursor:pointer}
pre{background:#141a26;padding:12px;overflow:auto;border-radius:6px}
.err{color:#ff6b6b}
</style></head><body><div class="wrap">
<h1>Backup Audit</h1>
<p>Signed in as <b>backup</b> · <a href="/dashboard.php">dashboard</a> · <a href="/?logout=1">logout</a></p>
<?php if ($out) echo '<p>'.htmlspecialchars($out).'</p>'; if ($err) echo '<p class="err">'.htmlspecialchars($err).'</p>'; ?>
<h3>Stage a slot</h3>
<form method="post">
<input name="slot" placeholder="slot name (e.g. bundle1)" size="20">
<input name="target" placeholder="bundle target path" size="36">
<button>Stage</button>
</form>
<h3>Slots</h3><ul>
<?php foreach ($slots as $s): $full='/var/vault-backup/'.$s; ?>
<li><?= htmlspecialchars($s) ?>
<?= is_link($full) ? ' → <code>'.htmlspecialchars(@readlink($full) ?: '?').'</code>' : '' ?>
 · <a href="?audit=<?= rawurlencode($s) ?>">audit</a></li>
<?php endforeach; if (!$slots) echo '<li>none</li>'; ?>
</ul>
<?php if ($audit !== null): ?>
<h3>Audit result (rc=<?= $audit['rc'] ?>)</h3>
<pre><?= htmlspecialchars($audit['out']) ?></pre>
<?php endif; ?>
<p style="font-size:11px;color:#777">vault-check audits backup-area paths only.</p>
</div></body></html>
