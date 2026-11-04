# ThreadZero ⚡

> **Status:** 🚧 Active Development (MVP Phase)
> **Current build:** Modules 1–3 are implemented (dual authentication, board feed, thread creation, replies with bump/sage, edit/delete, bookmarks and reporting). The profile and moderation modules are still pending — see [`PROGRESS.md`](./PROGRESS.md).
> **Architecture & Developer Guide:** See [`ARCHITECTURE.md`](./ARCHITECTURE.md) for the full system specification, database schema, and module guidelines.

**ThreadZero** is a privacy-first hybrid discussion board and imageboard engine built with **PHP 8 (PDO)**, **MySQL**, **HTML5/CSS3**, and **Vanilla JavaScript**. It combines the structured community boards of Reddit, the fast minimalist thread mechanics of 4chan/Hacker News, and the zero-knowledge authentication model of Mullvad VPN.

---

## ✨ Key Features

- **🔑 Dual Authentication System:**
  - **Standard Accounts:** Traditional `Username + Password` registration with bcrypt hashing.
  - **Mullvad-Style Token Accounts:** Zero-PII 16-digit account token generation (e.g., `4829-1049-8372-6154`) with auto-assigned handles (`@Vault#6154`)—no email required. The token is shown **once**, then stored only as an HMAC-SHA256 lookup hash plus a bcrypt verification hash.
- **🎭 Asymmetric Anonymity (Anti-Harassment Architecture):**
  - **Anonymous Threads (OP):** Users can start threads anonymously (`Anonymous [OP]`) while retaining internal ownership to edit or delete their posts.
  - **Accountable Replies:** Replies display the user's handle (`@username` or `@Vault#6154`) to prevent anonymous dogpiling—unless the original creator of an anonymous thread replies inside their own thread, which automatically masks them as `Anonymous [OP]`.
- **💬 Classic Imageboard Mechanics:**
  - **Bump vs. Sage:** Active threads automatically rise to the top of the board unless a reply is marked `Sage`.
  - **Rich Text Parsing:** Native support for `>greentext` quotes and clickable `>>1001` post reference anchors.
- **🛡️ Moderation & Bookmarks:**
  - Built-in post flagging, thread pinning/locking, and personal thread bookmarks.
- **🌗 Dual Theme Engine (planned for Module 6):**
  - Instant switching between **Retro Light (Yotsuba)** and **Modern Dark (Tomorrow)** modes using CSS variables and `localStorage`.

---

## 🛠️ Tech Stack

| Layer                  | Technology                                                              |
| :--------------------- | :---------------------------------------------------------------------- |
| **Frontend**           | HTML5, custom CSS3 (`assets/css/style.css`), Vanilla JavaScript         |
| **Backend**            | PHP 8.x (Procedural + PDO Prepared Statements, Native Sessions)         |
| **Database**           | MySQL / MariaDB (4 Normalized Tables with `ON DELETE CASCADE`)          |
| **Server Environment** | Laragon (Apache + MySQL), XAMPP, WAMP or LAMP               |

---

## 📂 Project Structure

```text
threadzero/
├── assets/
│   ├── css/style.css        # Shared stylesheet (plain CSS, no framework)
│   ├── js/app.js            # Click-to-quote helper (theme toggle pending Module 6)
│   └── uploads/             # Thread image attachments (created at runtime)
├── config/
│   ├── db.php               # PDO connection, session init, CSRF seed, APP_SECRET
│   └── functions.php        # isLoggedIn, isMod, CSRF, OP masking, text parser
├── includes/
│   ├── header.php           # Shared navigation (POST logout + CSRF)
│   └── footer.php           # Shared footer
├── sql/
│   ├── schema.sql           # 4-table schema + default boards (re-runnable)
│   └── seed.sql             # Demo users, threads and replies (pending)
├── index.php                # Board feed, search, sort & new-thread form
├── thread.php               # Thread view: replies, bump/sage, edit/delete, bookmark, report
├── auth.php                 # Dual login/signup + 16-digit token generator
├── profile.php              # Placeholder shell (Module 4 pending)
├── admin.php                # Mod-only placeholder shell (Module 5 pending)
├── profile.html             # Static prototype: bio, posts & bookmarks
├── ARCHITECTURE.md          # Full technical spec for devs & AI assistants
├── README.md                # Project overview & setup instructions
└── PROGRESS.md              # Live build tracker & module completion log
```

---

## 🚀 Local Setup (Laragon)

1. **Put the project in Laragon's web root:**

```text
C:\laragon\www\threadzero
```

2. **Start Apache and MySQL** from the Laragon menu (or click **Start All**).
3. **Create and seed the database.** Open **HeidiSQL** or **phpMyAdmin** from Laragon, then import/run [`sql/schema.sql`](./sql/schema.sql). It creates the `threadzero` database, the four tables and the four default boards.

   From a terminal you can run the same file with Laragon's MySQL client:

```powershell
C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root -e "source C:/laragon/www/threadzero/sql/schema.sql"
```

4. **Configure database credentials (if needed).**
   [`config/db.php`](./config/db.php) defaults to `host=localhost`, `user=root`, `pass=""`, which matches a stock Laragon install. Change it if your MySQL uses a password.
5. **Set a real `APP_SECRET` before publishing.** Locally the code falls back to a development placeholder. For any shared or deployed copy, set the `THREADZERO_APP_SECRET` environment variable so token lookup hashes cannot be reproduced from the repository.
6. **Launch the app:**
   Visit [`http://localhost/threadzero/`](http://localhost/threadzero/) — the landing page is `index.php`. Authentication lives at [`http://localhost/threadzero/auth.php`](http://localhost/threadzero/auth.php).

> **Frontend note:** The remaining `.html` files are static design references. The production app is the `.php` set, which reuses the same markup and `assets/css/style.css`. There is no CSS framework, no `npm install`, and no build step.

---

## 📋 Development Roadmap

- [x] Architecture & 4-Table Schema Design (`ARCHITECTURE.md`)
- [x] Static frontend prototypes (1 remaining HTML page + `assets/css/style.css`)
- [x] Database schema: `sql/schema.sql` (4 tables + default boards)
- [x] Core config & shared partials (`config/`, `includes/`)
- [x] Module 1: Dual Authentication & 16-Digit Token Generator (`auth.php`)
- [x] Module 2: Board Feed, Search, Sort & Thread Creation (`index.php`)
- [ ] Demo data: `sql/seed.sql`
- [x] Module 3: Thread Replies, Bump/Sage, Edit/Delete, Bookmarks & Reporting (`thread.php`)
- [ ] Module 4: User Profile & Bookmarks (`profile.php` — placeholder only)
- [ ] Module 5: Moderation & Reporting Dashboard (`admin.php` — placeholder only)
- [ ] Module 6: Theme toggle and remaining client-side polish (`assets/js/app.js` — quote helper done)

---

## 🤝 Contributing (For Team Members)

Before editing or generating code for any module, please read [`ARCHITECTURE.md`](./ARCHITECTURE.md) to ensure all SQL queries, CSS class names, and helper functions stay consistent across the project.
