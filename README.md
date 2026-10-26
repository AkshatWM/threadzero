# ThreadZero ⚡

> **Status:** 🚧 Active Development (MVP Phase)  
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
- **🌗 Dual Theme Engine:**
  - Instant switching between **Retro Light (Yotsuba)** and **Modern Dark (Tomorrow)** modes using CSS variables and `localStorage`.

---

## 🛠️ Tech Stack

| Layer                  | Technology                                                                   |
| :--------------------- | :--------------------------------------------------------------------------- |
| **Frontend**           | HTML5, Tailwind CSS (utility classes) + custom theme CSS, Vanilla JavaScript |
| **Backend**            | PHP 8.x (Procedural + PDO Prepared Statements, Native Sessions)              |
| **Database**           | MySQL / MariaDB (4 Normalized Tables with `ON DELETE CASCADE`)               |
| **Server Environment** | Apache + MySQL (XAMPP / WAMP / LAMP)                                         |

---

## 📂 Project Structure

```text
threadzero/
├── assets/
│   ├── css/style.css        # Tailwind overrides + light/dark theme variables
│   ├── js/app.js            # Theme switcher & click-to-quote helpers
│   └── uploads/             # Thread image attachments
├── config/
│   ├── db.php               # PDO database connection & session init
│   └── functions.php        # Auth checks, OP masking & text parsers
├── includes/
│   ├── header.php           # Shared navigation & board bar
│   └── footer.php           # Shared footer & script imports
├── sql/
│   ├── schema.sql           # 4-table database schema
│   └── seed.sql             # Starter boards & sample threads
├── index.php                # Board directory, search & thread feed
├── thread.php               # Thread view, replies & Bump/Sage engine
├── auth.php                 # Dual Login/Signup + 16-digit Token generator
├── profile.php              # User bio editor, post history & bookmarks
├── admin.php                # Moderator dashboard (Pin, Lock, Delete)
├── ARCHITECTURE.md          # Full technical spec for devs & AI assistants
├── README.md                # Project overview & setup instructions
└── PROGRESS.md              # Live build tracker & module completion log

```

---

## 🚀 Local Setup (XAMPP)

1. **Clone the repository into your XAMPP `htdocs` folder:**

```bash
cd C:/xampp/htdocs
git clone https://github.com/<your-username>/threadzero.git

```

2. **Start Apache & MySQL:**
   Open the **XAMPP Control Panel** and start both **Apache** and **MySQL**.
3. **Import the Database Schema:**

- Open [`http://localhost/phpmyadmin`](http://localhost/phpmyadmin) in your browser.
- Click the **Import** tab (or **SQL** tab) and run [`sql/schema.sql`](https://www.google.com/search?q=./sql/schema.sql) to create the `threadzero` database and default boards.
- _(Optional)_ Run [`sql/seed.sql`](https://www.google.com/search?q=./sql/seed.sql) to populate demo accounts and sample threads.

4. **Configure Database Credentials (if needed):**
   By default, [`config/db.php`](https://www.google.com/search?q=./config/db.php) uses standard XAMPP credentials (`host=localhost`, `user=root`, `pass=""`). Update if your local MySQL uses a custom password.
5. **Launch the App:**
   Visit [`http://localhost/threadzero/`](http://localhost/threadzero/) in your browser.

> **Styling note:** Tailwind is loaded via the Play CDN for simplicity, so no `npm install` or build step is required. Custom theme variables and post styling live in `assets/css/style.css`.

---

## 📋 Development Roadmap

- [x] Architecture & 4-Table Schema Design (`ARCHITECTURE.md`)
- [ ] Database Files: `sql/schema.sql` + `sql/seed.sql`
- [ ] Core Config & Shared UI Partials (`config/`, `includes/`, `assets/css/style.css`)
- [ ] Module 1: Dual Authentication & 16-Digit Token Generator (`auth.php`)
- [ ] Module 2: Board Feed, Search & Thread Creation (`index.php`)
- [ ] Module 3: Thread Replies, Asymmetric Anonymity & Bump/Sage (`thread.php`)
- [ ] Module 4: User Profile & Bookmarks (`profile.php`)
- [ ] Module 5: Moderation & Reporting Dashboard (`admin.php`)

---

## 🤝 Contributing (For Team Members)

Before editing or generating code for any module, please read [`ARCHITECTURE.md`](./ARCHITECTURE.md) to ensure all SQL queries, CSS class names, and helper functions stay consistent across the project.
