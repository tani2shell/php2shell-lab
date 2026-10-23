<?php
// internal runtime notes page (ops-facing)
session_start();
if (empty($_SESSION['user'])) { http_response_code(401); exit('Authentication required.'); }
?>
<!DOCTYPE html>
<html><head><title>PhpVault - Runtime Notes</title>
<style>body{font-family:Arial,sans-serif;background:#1c2333;color:#e6e6e6} .wrap{max-width:640px;margin:50px auto;background:#273043;padding:28px;border-radius:8px} code{color:#ffd43b} a{color:#74c0fc}</style>
</head><body><div class="wrap">
<h1>Runtime Notes</h1>
<p>App: PhpVault 2.4.1 · <a href="/dashboard.php">back to dashboard</a></p>
<ul>
<li>runtime: PHP 8.4.26 (patched line: last security update 2026-09)</li>
<li>upload progress tracking: enabled (session engine)</li>
<li>per-request memory ceiling: 8M (shared hosting profile)</li>
<li>backup auditor: /usr/local/bin/vault-check (restricted to backup area)</li>
</ul>
<p style="font-size:11px;color:#777">internal page — do not expose externally</p>
</div></body></html>
