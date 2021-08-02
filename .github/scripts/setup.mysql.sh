#!/usr/bin/env bash
echo -e "Start Mysql..."
sudo /etc/init.d/mysql start
echo -e "Config Mysql..."
mysql -h 127.0.0.1 -uroot -proot -e "use mysql;update user set plugin='mysql_native_password',authentication_string='' where User='root';FLUSH PRIVILEGES;"
echo -e "Create MySQL database..."
mysql -h 127.0.0.1 -uroot -e "CREATE DATABASE IF NOT EXISTS casbin_test charset=utf8mb4 collate=utf8mb4_unicode_ci;"
echo -e "Done\n"

wait