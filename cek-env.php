<?php
// HAPUS FILE INI SETELAH SELESAI TESTING!
echo '<pre>';
echo 'DB_HOST : ' . (getenv('DB_HOST') ?: '❌ KOSONG') . "\n";
echo 'DB_USER : ' . (getenv('DB_USER') ?: '❌ KOSONG') . "\n";
echo 'DB_PASS : ' . (getenv('DB_PASS') ? '✅ Ada' : '❌ KOSONG') . "\n";
echo 'DB_NAME : ' . (getenv('DB_NAME') ?: '❌ KOSONG') . "\n";
echo 'DB_PORT : ' . (getenv('DB_PORT') ?: '❌ KOSONG') . "\n";
echo '</pre>';