# MuCoreX

MuCoreX es un sistema web companion para servidores MU, que funciona junto a cualquier CMS existente. Permite gestionar usuarios, sesiones y addons web de manera segura y centralizada, con una experiencia de usuario fluida a través de una SPA interna.

Se configura en: config.js, config.php, .htaccess, .env y index.html

CREATE TABLE `users_token` (
  `user_id` INT UNSIGNED NOT NULL,
  `api_token` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
