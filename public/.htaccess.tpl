RewriteEngine on

php_value include_path "VENDOR_ROOT"

RewriteCond %{REQUEST_FILENAME} !\.(php)$
RewriteRule ^(client|user)/(.+)$ $1/$2.php [END]

RewriteRule ^.+ - [F]
