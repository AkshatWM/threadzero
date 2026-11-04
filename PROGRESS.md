# ThreadZero — Project Progress Report

**Project:** ThreadZero — Privacy-First Hybrid Discussion & Imageboard Engine
**Stack:** HTML5, custom CSS3, Vanilla JS · PHP 8 (PDO) · MySQL / MariaDB
**Type:** B.Tech Web Development Lab project (5-member team, 10–15 day timeline)
**Last verified:** 4 Nov 2026 (repo scan of `C:\laragon\www\threadzero`, Module 1 implemented)

---

## 1. Executive Summary

> **"Modules 1 and 2 work; threads are read-only until Module 3."**

Modules 1 and 2 are implemented: dual authentication (`auth.php`), the 4-table schema (`sql/schema.sql`), the PDO/session layer (`config/db.php`), shared helpers (`config/functions.php`), the layout partials (`includes/`), and the board feed with search, sort, new-thread creation and image upload (`index.php`). `thread.php` renders threads and replies read-only; the reply engine, profile features and moderation are still pending.

| Area | Completion |
|---|---|
| Planning / requirements | ~90% |
| Design / architecture | ~80% |
| Implementation | **~40%** (Modules 1–2 + foundation) |
| Testing / deployment | **~20%** (Modules 1–2 tested against MySQL) |
| Documentation / viva prep | ~75% |
| **Overall project process** | **~55%** |

**Verdict:** authentication and the board feed are complete and tested; the reply engine, profile and moderation modules are still to be built.

---

## 2. What Actually Exists Right Now

### 2.1 In the repository

| File / Folder | Size | Status | Notes |
|---|---|---|---|
| `README.md` | 7,296 bytes | ✅ Complete | Overview, current build status, feature list, project tree, Laragon setup, roadmap |
| `ARCHITECTURE.md` | 22,887 bytes | ✅ Complete | Coding rules, 4-table schema, privacy rules, form/session conventions, shared helpers, module specs, viva checklist |
| `PROGRESS.md` | this file | ✅ Live | Progress tracker |
| `.gitignore` | 42 bytes | ✅ Present | Ignores `.docx`, `.pdf`, editor folders |
| `assets/css/style.css` | 13,151 bytes | ✅ Base complete | Shared stylesheet for every page; green/cream theme, login/register/thread/profile sections, active-board state, mobile breakpoint |
| `thread.html` | 3,642 bytes | ✅ Static prototype | OP + replies, post numbers, reply form with Sage |
| `profile.html` | 4,326 bytes | ✅ Static prototype | Bio editor, post history, bookmarks |
| `index.php` | 13,980 bytes | ✅ Module 2 complete | Board feed, search, sort, new-thread form, image upload, pinned-first ordering |
| `thread.php` | 3,867 bytes | ✅ Read-only view | Thread + replies with OP masking, post numbers and formatting; reply form pending Module 3 |
| `auth.php` | 17,319 bytes | ✅ Module 1 complete | Standard signup/login, 16-digit token signup/login, POST logout, CSRF, rate limiting |
| `profile.php` | 641 bytes | 🚧 Placeholder | Login guard + profile shell; bio/history/bookmarks arrive in Module 4 |
| `admin.php` | 567 bytes | 🚧 Placeholder | Mod-only guard + panel shell; moderation actions arrive in Module 5 |
| `config/db.php` | 1,525 bytes | ✅ Complete | Hardened session, CSRF seed, env-based `APP_SECRET`, PDO to `threadzero`, generic DB error |
| `config/functions.php` | 2,158 bytes | ✅ Complete | `isLoggedIn()`, `isMod()`, `csrfField()`, `verifyCsrfToken()`, `renderAuthorBadge()`, `formatPostBody()` |
| `includes/header.php` | 1,573 bytes | ✅ Complete | Shared `<head>`, navbar, POST logout with CSRF |
| `includes/footer.php` | 133 bytes | ✅ Complete | Shared footer |
| `sql/schema.sql` | 3,464 bytes | ✅ Complete | Re-runnable 4-table schema + default boards; `AUTO_INCREMENT = 1001` |
| `sql/seed.sql` | — | ❌ Missing | Demo users, threads and replies not written yet |
| `assets/uploads/` | — | ✅ Present | Runtime image upload folder, tracked with `.gitkeep` and a no-script `.htaccess` |
| `assets/js/` | — | ❌ Missing | Module 6 JavaScript |
| Git repo | ✅ Active | PR #1 merged as `a56d7ad`; Module 1 committed as `99279fd`; Module 2 committed next |

**Still to build:** `sql/seed.sql`, the Module 3 reply engine (Bump/Sage, edit/delete, bookmark, report), the Module 4 profile features, the Module 5 moderation suite, `assets/js/app.js`, seed data, ER diagram image, screenshots and deployment evidence.

> ✅ `index.html` was removed after `index.php` became the real feed. Only `thread.html` and `profile.html` remain as static references.

### 2.2 Source documents

| File | Status | Notes |
|---|---|---|
| `web_dev_project.docx` (`D:\web_dev_project.docx`) | ✅ **Updated 3 Nov** | 25,924 bytes. Matches the merged frontend: static HTML pages, custom CSS (no Tailwind), 4 tables, secure token storage, 4 boards, form/session conventions, privacy checklist |
| `web_dev_project.md` | 🗑️ **Retired on purpose** | Removed so teammates work from one current document (`ARCHITECTURE.md` + `README.md`). The last version is still recoverable from git if ever needed |

**Recover the old spec (reference only):**

```bash
git show 6a0dd17:web_dev_project.md > old_spec_reference.md
```

### 2.3 Decisions locked by the team (26 Oct 2026)

The spec drift has been settled. `ARCHITECTURE.md` is now the single source of truth for implementation, and the old `PROJECT_SPEC.md` is intentionally retired so human teammates work from one current document.

| Decision | Resolution |
|---|---|
| Table count | **4 tables** — `users`, `boards`, `posts`, `bookmarks` |
| Reporting | Boolean flag `posts.is_reported`; no separate `reports` table |
| CSS approach | **Hand-written CSS** in `assets/css/style.css`; Tailwind was dropped after the merged frontend PR (the prototypes use no framework) |
| Frontend prototype | **2 static HTML pages** remain (`thread.html`, `profile.html`); `login.html`, `register.html` and `index.html` were folded into `auth.php` / `index.php` |
| Boards | **4 boards** — `tech`, `science`, `gaming`, `general` |
| Token storage | **Strong version chosen:** `token_lookup = HMAC-SHA256(token, APP_SECRET)` + `password_hash(token)`. The plaintext token is shown once and never stored |
| Token display | Token signup uses POST → redirect → GET: the token is stashed in the session, shown once, then deleted; the page sends `Cache-Control: no-store` |
| Logout | `POST` with an `action=logout` field and CSRF token; the GET logout link was removed |
| Form contract | Hidden `action` field (`signup`, `login`, `token_signup`, `token_login`, `logout`, `create_thread`, `reply`, `update_bio`, `remove_bookmark`, `report`, mod actions) + CSRF token; full table in `ARCHITECTURE.md` §4.1 |
| Spec file | `PROJECT_SPEC.md` removed on purpose — teammates use `ARCHITECTURE.md` + `README.md` |
| Schema SQL file | ✅ Written — `sql/schema.sql` creates the 4 tables and seeds the boards; safe to re-run |

---

## 3. Status Against the Full-Stack Process

| # | Step | Status | Detail |
|---|---|---|---|
| 1 | Idea / topic selection | ✅ Done | ThreadZero clearly defined |
| 2 | Problem statement & requirements | ✅ Done | 6 modules, dual auth, asymmetric anonymity, bump/sage, moderation |
| 3 | Tech stack | ✅ Done | HTML5 / custom CSS3 / Vanilla JS, PHP 8 + PDO, MySQL |
| 4 | Database design / ER schema | ⚠️ Mostly | 4 tables implemented in `sql/schema.sql` with FKs, `ON DELETE CASCADE`, unique keys and `AUTO_INCREMENT = 1001`; **no ER diagram image** |
| 5 | Wireframes / UI flow | ✅ Done | Static HTML pages + shared stylesheet; admin screen not mocked up |
| 6 | Architecture diagram | ⚠️ Partly | Folder architecture, shared helpers, and module boundaries documented in prose; **no diagram** |
| 7 | Environment / repo setup | ✅ Done | Git repo, `.gitignore`, Laragon setup documented; `config/`, `includes/`, `sql/` tracked |
| 8 | Backend / API implementation | ⚠️ Partial | Modules 1–2 done (`auth.php`, `index.php`) plus a read-only `thread.php`; Modules 3–5 pending |
| 9 | Frontend implementation | ⚠️ Partial | Working PHP views for auth, board feed and thread view; **no JavaScript, profile or admin UI** |
| 10 | Integration / auth implementation | ✅ Done (Modules 1–2) | Sessions, dual login, token storage, CSRF, rate limiting, thread creation and image upload |
| 11 | Testing | ⚠️ Partial | PHP lint passed; Module 1 and Module 2 flows tested against MySQL; no automated test suite |
| 12 | Deployment | ⚠️ Partial | Laragon steps documented, not yet executed end-to-end |
| 13 | Documentation / presentation | ⚠️ Mostly | README + ARCHITECTURE + viva guide done; missing report, ER diagram, screenshots, demo |

---

## 4. Module-by-Module Status

The foundation and Modules 1–2 are implemented; the remaining modules are placeholders or not started.

### Frontend prototype

| Unit | File(s) | Scope | Status |
|---|---|---|---|
| Static UI mockups | `thread.html`, `profile.html`, `assets/css/style.css` | Shared stylesheet; thread/replies, bio + bookmarks; mobile breakpoint | ✅ Done |
| Auth UI | `auth.php` views | Dual login, username signup, token generation, one-time token reveal | ✅ Done |
| Feed UI | `index.php` views | Board cards, new-thread form, search/sort bar, thread cards | ✅ Done |

### Shared foundation

| Unit | File(s) | Scope | Status |
|---|---|---|---|
| DB connection | `config/db.php` | Hardened session cookie, CSRF seed, env-based `APP_SECRET`, PDO to `threadzero`, `utf8mb4`, exception mode, `FETCH_ASSOC`, generic error page | ✅ Done |
| Helpers | `config/functions.php` | `isLoggedIn()`, `isMod()`, `csrfField()`, `verifyCsrfToken()`, `renderAuthorBadge($post, $parentThread)`, `formatPostBody($text)` | ✅ Done |
| Layout partials | `includes/header.php`, `includes/footer.php` | Shared `<head>`, navbar, POST logout + CSRF, footer | ✅ Done |
| Data layer | `sql/schema.sql`, `sql/seed.sql` | 4 tables + default boards done; demo threads/users still pending | ⚠️ Schema done, seed missing |

### Feature modules

| Module | File(s) | Scope | Status |
|---|---|---|---|
| 1. Dual Authentication & Session Engine | `auth.php` | Username/password signup+login; 16-digit token via `random_int()`; handle `Vault#6154`; `token_lookup` (HMAC-SHA256) + `password_hash(token)`; `password_verify()`; POST logout; CSRF; rate limiting | ✅ Done |
| 2. Board Directory, Search & Catalog Feed | `index.php` | Board nav from `boards`; keyword search (`LIKE`); sort by `bumped_at` / `created_at`; new-thread form (title, content, image upload, `is_anon`); pinned-first ordering | ✅ Done |
| 3. Core Thread & Reply Engine | `thread.php` | Parent + replies; post numbers (#1001…); Bump vs. Sage; `>greentext`; `>>ID` anchors; author edit/delete; bookmark; report | ⚠️ Read-only view done; reply engine pending |
| 4. User Dashboard & Bookmarks | `profile.php` | Edit bio; own post history (incl. anonymous threads); saved bookmarks with remove | ⚠️ Placeholder shell |
| 5. Trust, Safety & Moderation Suite | `admin.php` | Mod-only (`isMod()`); queue `WHERE is_reported = 1`; unflag, pin/unpin, lock/unlock, delete | ⚠️ Placeholder shell |
| 6. UI Theme & Client-Side Polish | `assets/css/style.css`, `assets/js/app.js` | Light/dark via CSS variables + `localStorage`; click-to-quote; min-length check; delete `confirm()` | ⚠️ Base CSS done; JS, dark mode and theme toggle pending |

---

## 5. Design Decisions Already Locked In

- **4 normalized tables only:** `users`, `boards`, `posts`, `bookmarks`. No `reports` table.
- **Reporting is a flag:** `posts.is_reported TINYINT(1)`; moderation queue filters on it.
- **Moderation state lives on `posts`:** `is_pinned`, `is_locked`.
- **Post IDs start at 1001** (`AUTO_INCREMENT = 1001`), so the first thread is `#1001`.
- **Self-referencing `posts`:** `parent_id IS NULL` = thread, `parent_id = <id>` = reply.
- **Cascade deletes:** deleting a thread removes its replies and bookmarks.
- **Dual auth in one `users` table:** `password_hash` for standard accounts *and* for the hashed token; `token_lookup` (HMAC-SHA256) for indexed token login; `role ENUM('user','mod')`.
- **Asymmetric anonymity:** anonymous OP shows `Anonymous [OP]` while `user_id` is kept; replies are accountable (`@handle`) except when the OP of an anonymous thread replies in their own thread.
- **Bump vs. Sage:** replies set `bumped_at = NOW()` unless Sage is checked.
- **Directory layout:** `assets/{css,js,uploads}`, `config/`, `includes/`, `sql/`, with the `.php` page routes at the root. `thread.html` and `profile.html` are static design references, not routes.
- **Image uploads:** extension whitelist plus `getimagesize()` MIME validation, 2 MB limit, random filenames, and a no-script `.htaccess` in `assets/uploads/`.
- **Shared helpers, not copy-paste:** `renderAuthorBadge()` and `formatPostBody()` live once in `config/functions.php`; `csrfField()` and `verifyCsrfToken()` live there too; navbar/footer live in `includes/`.
- **Theme names:** Retro Light (Yotsuba) and Modern Dark (Tomorrow) — planned for Module 6; the merged stylesheet currently ships one green/cream light theme.
- **Token security (strong version):** the plaintext token is shown once; the DB stores `token_lookup = HMAC-SHA256(token, APP_SECRET)` for indexed lookup plus `password_hash(token)` for verification. Login runs `password_verify()` before `session_regenerate_id(true)`. `APP_SECRET` comes from `THREADZERO_APP_SECRET` with a dev fallback.
- **Token display:** POST → redirect → GET; the token lives in the session for exactly one render, then is deleted. `Cache-Control: no-store` keeps it out of browser caches.
- **Privacy hardening:** generic login errors, `HttpOnly`/`SameSite`/HTTPS-aware cookies, CSRF token on every `POST`, no email or IP logging, and all output escaped with `htmlspecialchars()`.
- **Styling:** one hand-written stylesheet (`assets/css/style.css`) with a shared green/cream theme, per-page sections and a mobile breakpoint. No Tailwind, no Bootstrap, no build step.
- **Form contract:** every POST form posts a hidden `action` plus a CSRF token; field names and redirect rules are standardised in `ARCHITECTURE.md` §4.1.
- **Docs:** `ARCHITECTURE.md` is the single source of truth; `PROJECT_SPEC.md` is retired intentionally.
- **Code style for viva safety:** flat procedural PHP, PDO prepared statements with `?`, native `$_SESSION`, plain Vanilla JS, no JS frameworks, no mandatory build step, commented SQL blocks.

---

## 6. Build Checklist

### Stage 0 — Completed groundwork
- [x] Define concept, modules, and viva plan
- [x] Initialize git repo + `.gitignore`
- [x] Write `README.md` and `ARCHITECTURE.md`
- [x] Design the 4-table schema (columns, keys, cascades)
- [x] Define the repository directory layout
- [x] Static frontend prototypes: static HTML pages + `assets/css/style.css` (PR #1)

### Stage 1 — Scaffold, DB & MVP Foundation (Days 1–5)
- [x] Create `assets/css` and write the shared prototype stylesheet
- [x] Create the foundation folders: `config`, `includes`, `sql`
- [x] Write `sql/schema.sql` exactly as specified in `ARCHITECTURE.md` §3 (4 tables + default boards)
- [ ] Write `sql/seed.sql` (demo users, sample threads and replies)
- [x] Write `config/db.php` (PDO + session cookie flags + env-based `APP_SECRET`)
- [x] Write `config/functions.php` (`isLoggedIn`, `isMod`, `renderAuthorBadge`, `formatPostBody`, CSRF helpers)
- [x] Write `includes/header.php` and `includes/footer.php`
- [x] Module 1: `auth.php` — standard signup/login + 16-digit token generator/login with HMAC lookup + bcrypt verification
- [x] Module 2: `index.php` — board feed, thread creation, image upload, search and sort
- [x] Module 3 (read side): `thread.php` — parent thread, replies, post numbers, anonymity masking, greentext

### Stage 2 — Hybrid Mechanics & Full CRUD (Days 6–10)
- [ ] Module 3 (write side): reply form, Bump/Sage, edit/delete
- [x] Asymmetric anonymity masking via `renderAuthorBadge()`
- [x] `>greentext` parser + `>>ID` links
- [ ] Bookmark toggle + report flag from `thread.php`
- [ ] Module 4: `profile.php` — bio `UPDATE`, history, bookmarks `INSERT`/`DELETE`
- [ ] Module 5: `admin.php` — mod queue (`is_reported`), pin/lock/unflag/delete

### Stage 3 — Polish, Testing & Viva Prep (Days 11–15)
- [ ] Module 6: theme switcher, click-to-quote, min-length check, delete confirm
- [ ] Seed realistic threads across all 4 boards
- [ ] Test plan + evidence (auth, CRUD, permissions, cascade delete, anonymity masking)
- [ ] Security checks: token never stored/displayed after signup, rate limiting, CSRF rejection, generic login errors
- [ ] Deployment (Laragon steps executed end-to-end)
- [ ] README screenshots + ER diagram
- [ ] Rehearse live-modification tasks from `ARCHITECTURE.md` §6

---

## 7. Immediate Next Steps (Priority Order)

1. **Write `sql/seed.sql`** with demo users and sample threads across the four boards so the feed has content to render.
2. **Finish Module 3 (`thread.php`):** add the reply form, Bump/Sage, author edit/delete, bookmark toggle and report button on top of the read-only view.
3. **Build Module 4 (`profile.php`):** bio editor, own post history (including anonymous threads) and bookmarks with remove.
4. **Build Module 5 (`admin.php`):** reported-post queue, unflag, pin/unpin, lock/unlock and delete.
5. **Build Module 6 (`assets/js/app.js`):** theme toggle, click-to-quote, minimum-length checks and delete confirmation.

---

## 8. Risks / Blockers

| Risk | Impact | Mitigation |
|---|---|---|
| `APP_SECRET` committed to a public repo | If both DB and repo leak, `token_lookup` can be brute-forced | Only a dev fallback is committed; set `THREADZERO_APP_SECRET` for real deployments and rotate before publishing |
| 16-digit token ≈ 53 bits of entropy | Online guessing if login is unthrottled | Enforce the `sleep()` delay + failed-attempt limit; never log tokens |
| Static HTML and PHP views drift apart | Duplicate markup diverges and the UI breaks during conversion | Treat `.html` as reference only; move shared markup into `includes/`; delete or archive prototypes once each page is converted |
| Remaining prototype forms lack CSRF and `action` fields | Copy-pasting markup into PHP would ship insecure forms | Follow `ARCHITECTURE.md` §4.1; add `csrfField()` and a hidden `action` to every POST form during conversion |
| Users may assume "anonymous" means untraceable | Privacy promise overstated in the viva | Document clearly: `user_id` is stored, so it is pseudonymity, not untraceability |
| User-uploaded images | Malicious files or script execution from `assets/uploads/` | Extension + `getimagesize()` MIME checks, 2 MB cap, random filenames, and a no-script `.htaccess` |
| Remaining modules due with a 10–15 day plan | No slack for debugging | Finish Module 3 next, then 4–6; keep the schema and helpers stable |
| 6 modules, 5 people | Module 6 + shared partials unowned | Assign frontend devs to Module 6 and the `includes/` partials |
| No tests planned | Anonymity and permission bugs are subtle and high-risk | Explicitly test author-edit, OP masking, cascade delete, CSRF rejection |

---

## 9. How to Update This File

- Update the **Last verified** date and the percentages in section 1 after each work session.
- Change a module's status in section 4 only when its core flows work end-to-end — not when the file merely exists.
- Tick checklist items in section 6 only when verified.
- Keep the executive summary honest: a module is not "done" because a file was created.

---

## Change log

| Date | Change |
|---|---|
| 25 Oct 2026 | Initial progress report |
| 26 Oct 2026 | Synced with `web_dev_project.md` §3 (new directory layout) and §4 (4-table schema). Recorded repo bootstrap and new docs. |
| 26 Oct 2026 | Team locked the spec: 4 tables, Tailwind CSS, 4 boards, `PROJECT_SPEC.md` retired, secure token storage (HMAC lookup + bcrypt). Updated `README.md`, `ARCHITECTURE.md`, and regenerated `web_dev_project.docx` to match. |
| 3 Nov 2026 | Merged PR #1 (`frontend-work`): 5 static HTML pages + `assets/css/style.css`. Adopted hand-written CSS, documented the prototype → PHP workflow and form/session conventions, aligned mockup post numbers/badges/token reveal, and updated all progress figures. |
| 4 Nov 2026 | Implemented Module 1 (`auth.php`) plus `config/db.php`, `config/functions.php`, `includes/` and `sql/schema.sql`. Standardised on `action` + POST logout, 3–20/8 validation, token PRG with `no-store`, `AUTO_INCREMENT = 1001`, and a generic DB error page. Added landing/profile/admin PHP shells and updated README, ARCHITECTURE and PROGRESS. |
| 4 Nov 2026 | Implemented Module 2 in `index.php`: board feed, search, sort, pinned-first ordering, new-thread form and validated image uploads. Added a read-only `thread.php` with OP masking and greentext, removed `index.html`, and updated the docs. Tested auth, thread creation, search, uploads, masking and 404 handling against MySQL. |
