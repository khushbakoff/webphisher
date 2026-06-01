<p align="center">
  <img src=".github/misc/logo.png" alt="Webphisher Uzbekistan" width="120">
</p>

<h1 align="center">Webphisher Uzbekistan</h1>

<p align="center">
  <strong>Ta'lim laboratoriyasi uchun zamonaviy web boshqaruv paneli</strong><br>
  <sub>35+ phishing shabloni · 3 til · jonli monitoring · SQLite / JSON</sub>
</p>

<p align="center">
  <a href="https://github.com/khushbakoff/webphisher/blob/main/LICENSE">
    <img src="https://img.shields.io/badge/License-GPL--3.0-blue?style=for-the-badge" alt="GPL-3.0">
  </a>
  <a href="https://github.com/khushbakoff/webphisher">
    <img src="https://img.shields.io/badge/Panel-v1.0.0-8b5cf6?style=for-the-badge" alt="Panel version">
  </a>
  <a href="https://github.com/khushbakoff/webphisher">
    <img src="https://img.shields.io/badge/Core-v2.3.5-06b6d4?style=for-the-badge" alt="Core version">
  </a>
  <img src="https://img.shields.io/badge/Languages-UZ%20%7C%20RU%20%7C%20EN-ec4899?style=for-the-badge" alt="Languages">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777bb4?style=flat-square&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/SQLite-optional-003B57?style=flat-square&logo=sqlite&logoColor=white">
  <img src="https://img.shields.io/badge/Windows-✓-0078d4?style=flat-square&logo=windows">
  <img src="https://img.shields.io/badge/Linux-✓-FCC624?style=flat-square&logo=linux&logoColor=black">
</p>

---

## 📖 Loyiha haqida

**Webphisher Uzbekistan** — [Zphisher](https://github.com/htr-tech/zphisher) asosidagi phishing laboratoriya vositalarini **brauzer orqali** boshqarish uchun yaratilgan web-panel.

Terminal buyruqlarini eslab qolish shart emas: shablon tanlaysiz, tunnel sozlaysiz, havolani olasiz va natijalarni real vaqtda kuzatasiz.

| Rejim | Port | Vazifasi |
|-------|------|----------|
| **Web panel** | `9090` | Boshqaruv, monitoring, ma'lumotlar bazasi |
| **Lab server** | `8080` | Tanlangan phishing shabloni (PHP built-in) |

---

## ✨ Asosiy imkoniyatlar

### Web panel (`panel/`)

- 🎨 **Zamonaviy UI** — yorqin dizayn, o‘ng panel (sessiya, statistika, so‘nggi yozuvlar)
- 🌍 **3 til** — O‘zbek, Rus, Ingliz (`UZ` / `RU` / `EN`)
- 📋 **35+ platforma** — Facebook, Instagram, Google, Telegram ijtimoiy tarmoqlari va boshqalar
- 🔗 **Tunnel** — Localhost, Cloudflared, LocalXpose
- 🎭 **Mask URL** va **URL qisqartirish** (is.gd, shrtco, tinyurl)
- 📡 **Jonli monitoring** — IP va login ma’lumotlari darhol
- 💾 **Ma’lumotlar bazasi** — SQLite yoki JSON (`panel/data/`)
- ☁️ **Cloudflared** — Windows uchun PowerShell skripti (`panel/install-cloudflared.bat`)

### Terminal (ixtiyoriy)

Asl `zphisher.sh` skripti ham saqlanib qolgan — klassik CLI rejimida ishlatish mumkin.

---

## ⚠️ Ogohlantirish (muhim)

> **Faqat qonuniy ta’lim va ruxsat etilgan laboratoriya muhitida foydalaning.**

- Ushbu loyiha phishing **qanday ishlashini o‘rgatish** uchun mo‘ljallangan.
- Noqonuniy foydalanish **jinoyat javobgarligiga** olib kelishi mumkin.
- Muallif va contributorlar noto‘g‘ri foydalanish uchun **javobgar emas**.
- Hech qachon haqiqiy foydalanuvchilarga ruxsatsiz hujum qilmang.

---

## 🚀 Tezkor boshlash

### 1. Repozitoriyni klonlash

```bash
git clone https://github.com/khushbakoff/webphisher.git
cd webphisher
```

### 2. Talablar

| Dastur | Versiya | Izoh |
|--------|---------|------|
| **PHP** | 8.0+ | `curl` tavsiya etiladi |
| **pdo_sqlite** | ixtiyoriy | Yo‘q bo‘lsa JSON rejimi ishlaydi |
| **Git / Internet** | — | Cloudflared yuklab olish uchun |

PHP holatini tekshirish:

```bash
php panel/check-php.php
```

### 3. Web panelni ishga tushirish

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

### 4. Birinchi sessiya

1. **Shablonlar** bo‘limidan platforma tanlang (masalan, Google, Instagram).
2. **Tunnel** tanlang: avval **Localhost** bilan sinab ko‘ring.
3. **Ishga tushirish** tugmasini bosing.
4. **Jonli monitoring**da IP va login yozuvlarini kuzating.

---

## 🖥️ Windows uchun qo‘shimcha

| Muammo | Yechim |
|--------|--------|
| `curl_init()` xatosi | `panel\setup-php.bat` yoki `php.ini` da `extension=curl` |
| `pdo_sqlite` yo‘q | Panel avtomatik **JSON** rejimida ishlaydi |
| Cloudflared yuklanmaydi | `panel\install-cloudflared.bat` (PHP kerak emas) |

---

## 📁 Loyiha tuzilmasi

```
webphisher/
├── panel/                    # Webphisher Uzbekistan panel
│   ├── views/app.php         # Asosiy interfeys
│   ├── api/router.php        # REST API
│   ├── lib/                  # PHP backend (Store, Session, Tunnel...)
│   ├── assets/               # CSS, JS, i18n (uz, ru, en)
│   ├── data/                 # SQLite / JSON (gitignore)
│   ├── start-panel.bat       # Windows launcher
│   └── start-panel.sh        # Linux launcher
├── .sites/                   # 35+ phishing shablonlari
├── zphisher.sh               # Asl terminal skripti (ixtiyoriy)
├── auth/                     # Yig‘ilgan ma’lumotlar (gitignore)
└── .server/                  # Runtime, cloudflared (gitignore)
```

---

## 🌐 Tillar

Panel interfeysi 3 tilda:

| Til | Kod | Fayl |
|-----|-----|------|
| O‘zbek | `UZ` | `panel/assets/i18n/uz.json` |
| Русский | `RU` | `panel/assets/i18n/ru.json` |
| English | `EN` | `panel/assets/i18n/en.json` |

Til chap menyudan tanlanadi va brauzerda saqlanadi.

---

## 🔧 Terminal rejimi (Zphisher CLI)

Klassik terminal versiyasi ham mavjud:

```bash
bash zphisher.sh
```

Birinchi ishga tushirishda `php`, `curl`, `cloudflared` avtomatik o‘rnatiladi (Linux).

---

## 🐳 Docker (asl Zphisher)

```bash
docker pull htrtech/zphisher
docker run --rm -ti htrtech/zphisher
```

Web panel uchun Docker hali alohida image emas — mahalliy PHP server tavsiya etiladi.

---

## 🔒 Xavfsizlik

Quyidagilar **hech qachon** GitHubga yuklanmaydi (`.gitignore`):

- `auth/` — yig‘ilgan credential va IP fayllari
- `.server/` — cloudflared, vaqtinchalik www
- `panel/data/panel.db` va `store.json`

---

## 📜 Litsenziya

Ushbu loyiha **[GNU General Public License v3.0](LICENSE)** (GPL-3.0) ostida tarqatiladi.

Asl Zphisher loyihasi: [htr-tech/zphisher](https://github.com/htr-tech/zphisher) — muallif **TAHMID RAYAT (HTR-TECH)**.

Web panel qatlami: **Webphisher Uzbekistan** — [khushbakoff](https://github.com/khushbakoff).

GPL shartlariga ko‘ra o‘zgartirilgan kodni ham GPL ostida ulashish kerak.

---

## 🙏 Minnatdorchilik

| Ishtirokchi | Hissa |
|------------|-------|
| [htr-tech / Zphisher](https://github.com/htr-tech/zphisher) | Asosiy engine va shablonlar |
| [khushbakoff](https://github.com/khushbakoff) | Web panel, UI, i18n, SQLite |

---

<p align="center">
  <sub>⭐ Agar loyiha foydali bo‘lsa, repoga star qo‘ying</sub><br><br>
  <a href="https://github.com/khushbakoff/webphisher">github.com/khushbakoff/webphisher</a>
</p>
