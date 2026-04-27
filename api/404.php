# ================================================================
#  InfoHarga Komoditi — .htaccess
#  Letakkan file ini di: C:\xampp\htdocs\InfoHargaa\.htaccess
# ================================================================

Options -Indexes

# ── Custom Error Pages ─────────────────────────────────────────
ErrorDocument 404 /InfoHargaa/404.php
ErrorDocument 403 /InfoHargaa/404.php
ErrorDocument 500 /InfoHargaa/404.php

# ── Security Headers ───────────────────────────────────────────
<IfModule mod_headers.c>
    # Cegah clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    # Cegah MIME sniffing
    Header always set X-Content-Type-Options "nosniff"
    # XSS Protection
    Header always set X-XSS-Protection "1; mode=block"
    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# ── Blokir akses langsung ke folder sensitif ──────────────────
<FilesMatch "^(koneksi|bps_api)\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Blokir akses ke file .sql, .log, .env
<FilesMatch "\.(sql|log|env|bak|sh)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# ── Cache assets statis ────────────────────────────────────────
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css             "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/jpeg           "access plus 1 year"
    ExpiresByType image/png            "access plus 1 year"
    ExpiresByType image/webp           "access plus 1 year"
</IfModule>

# ── Kompres response ───────────────────────────────────────────
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json
</IfModule>
