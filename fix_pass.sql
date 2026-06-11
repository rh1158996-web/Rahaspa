UPDATE serenity_spa.admins SET password_hash = '$2y$10$I3rIVAhTJf62pmYY.oeIcu2tV.oTF67L0ZywSGV6BEglg.OeKHUqC' WHERE username = 'admin';
SELECT username, LEFT(password_hash,10) as hash_start, LENGTH(password_hash) as hash_len FROM serenity_spa.admins;
