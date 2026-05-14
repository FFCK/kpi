-- Extend token column in kp_user_token from VARCHAR(32) to VARCHAR(64)
-- Tokens are generated with bin2hex(random_bytes(32)) = 64 hex chars
-- VARCHAR(32) was silently truncating tokens, causing "Invalid or expired token" errors

ALTER TABLE `kp_user_token`
  MODIFY COLUMN `token` VARCHAR(64) NOT NULL;
