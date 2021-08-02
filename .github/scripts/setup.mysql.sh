#!/usr/bin/env bash
echo -e "Start Mysql..."
sudo /etc/init.d/mysql start
echo -e "Config Mysql..."
mysql -h 127.0.0.1 -uroot -proot -e "alter user 'root'@'%' identified with mysql_native_password by '';"
echo -e "Create MySQL database..."
mysql -h 127.0.0.1 -u root -e "CREATE DATABASE IF NOT EXISTS casbin-test charset=utf8mb4 collate=utf8mb4_unicode_ci;"
echo -e "Done\n"

wait