#!/usr/bin/env bash
# Creates a separate testing database with the same user credentials.
# Runs automatically on first container start via docker-entrypoint-initdb.d.

mariadb -u root -p"${MARIADB_ROOT_PASSWORD}" <<-SQL
    CREATE DATABASE IF NOT EXISTS \`${MARIADB_DATABASE}_testing\`
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    GRANT ALL PRIVILEGES ON \`${MARIADB_DATABASE}_testing\`.* TO '${MARIADB_USER}'@'%';
    FLUSH PRIVILEGES;
SQL