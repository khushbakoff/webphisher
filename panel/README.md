# Webphisher Uzbekistan

Ta'lim laboratoriyasi uchun Webphisher Uzbekistan boshqaruv paneli (PHP + SQLite + HTML/CSS/JS).

## Ishga tushirish

**Windows:**
```bat
panel\start-panel.bat
```

**Linux / macOS:**
```bash
chmod +x panel/start-panel.sh
./panel/start-panel.sh
```

Brauzerda oching: **http://127.0.0.1:9090**

## Talablar

- PHP 8.0+ (`curl` tavsiya etiladi)
- `pdo_sqlite` yo'q bo'lsa ham panel **JSON rejimida** ishlaydi (`panel/data/store.json`)
- Zphisher `.sites` shablonlari loyiha ildizida bo'lishi kerak

### PHP tekshiruvi

```bat
php panel\check-php.php
```

SQLite yoqish (ixtiyoriy): `php.ini` da `extension=pdo_sqlite` ni yoqing va PHP ni qayta ishga tushiring.

## Funksiyalar

- 35+ platforma shablonlari (terminaldagi kabi variantlar bilan)
- Tunnel: Localhost, Cloudflared, LocalXpose
- Maxsus port, Mask URL, URL qisqartirish
- Jonli IP va login monitoring
- SQLite bazada tarix
- Cloudflared avtomatik yuklab olish (Windows/Linux)

## Ogohlantirish

Faqat ruxsat etilgan o'quv laboratoriyasida foydalaning.
