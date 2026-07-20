# Baseline Deployment Security

Dokumen ini menjadi checklist minimum sebelum aplikasi dipublikasikan.

## Environment

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kdmp.example.go.id
APP_KEY=<hasil php artisan key:generate --show>
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
TRUSTED_PROXIES=<IP proxy yang benar-benar digunakan>
```

Jangan commit `.env`, `storage`, kredensial database, API key, atau file dump/debug.
Setelah perubahan environment jalankan `php artisan config:cache` dan pastikan cache
tidak berisi rahasia di repository.

## Nginx dan PHP-FPM

Document root wajib hanya mengarah ke `.../kdmp/public`, bukan root repository.
Contoh baseline:

```nginx
root /srv/kdmp/public;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    fastcgi_param DOCUMENT_ROOT $realpath_root;
    fastcgi_pass unix:/run/php/php8.4-fpm.sock;
}

location ~ /\.(?!well-known).* {
    deny all;
}

location ~* \.(?:env|log|sql|sqlite|bak|old|dist|ini)$ {
    deny all;
}
```

Jangan memberi akses tulis ke `public/`, source code, atau file konfigurasi.
Worker PHP-FPM harus memakai user non-root. Atur `display_errors=Off`,
`log_errors=On`, `expose_php=Off`, dan batasi `upload_max_filesize` serta
`post_max_size` sesuai kebutuhan bisnis.

## HTTPS headers

Tambahkan header di reverse proxy setelah memastikan seluruh subdomain memang HTTPS:

```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
```

CSP perlu disesuaikan dengan aset aplikasi dan diuji melalui `Content-Security-Policy-Report-Only`
sebelum ditegakkan.
