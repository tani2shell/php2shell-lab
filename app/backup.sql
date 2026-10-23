-- PhpVault backup dump (nightly, sanitized)
-- NOTE: demo credentials only; real secrets are not stored here.
INSERT INTO users (username, role, password_hash) VALUES
  ('demo', 'reader', '$2y$10$demodemodemodemoAAAAAAAAAAAA'),
  ('backup', 'operator', '$2y$10$backupbackupbackupBBBBBBBBBB');
-- flag check: no flags in this dump :)
