<?php
require 'config/config.php';
try {
  $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
  new PDO($dsn, DB_USER, DB_PASS);
  echo "OK\n";
} catch (Throwable $e) {
  echo $e->getMessage() . "\n";
}
