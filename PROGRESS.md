# ThreadZero — Project Progress Report

**Project:** ThreadZero — Privacy-First Hybrid Discussion & Imageboard Engine
**Stack:** HTML5, Tailwind CSS, Vanilla JS · PHP 8 (PDO) · MySQL / MariaDB
**Type:** B.Tech Web Development Lab project (5-member team, 10–15 day timeline)
**Last verified:** 26 Oct 2026 (repo scan of `D:\web_dev_project`, 4 commits)

---

## 1. Executive Summary

> **"The blueprint is now locked; not a single line of the app is written yet."**

The project has moved past the outline stage. The repository exists, the folder architecture is defined, and the database is fully specified as a 4-table schema. What is still missing is the entire application: no PHP, no CSS, no JS, no `schema.sql`, no seed data.

| Area | Completion |
|---|---|
| Planning / requirements | ~90% |
| Design / architecture | ~65% |
| Implementation | **0%** |
| Testing / deployment | **0%** |
| Documentation / viva prep | ~60% |
| **Overall project process** | **~35%** |

**Verdict:** planning and design are largely done; the build has not started. Every module and every shared file is still at 0%.

---

## 2. What Actually Exists Right Now

### 2.1 In the repository

| File / Folder | Size | Status | Notes |
|---|---|---|---|
| `README.md` | 6,115 bytes | ✅ Complete | Overview, feature list (Tailwind + secure token), project tree, XAMPP setup, roadmap |
| `ARCHITECTURE.md` | 16,932 bytes | ✅ Complete | Coding rules, 4-table schema, privacy rules, shared code (`db.php`, `functions.php`), module specs, viva checklist, AI prompt |
| `PROGRESS.md` | this file | ✅ Live | Progress tracker |
| `.gitignore` | 42 bytes | ✅ Present | Ignores `.docx`, `.pdf`, editor folders |
| `assets/` | — | ⚠️ Empty | `css/`, `js/`, `uploads/` subfolders **not created yet** |
| `config/` | — | ⚠️ Empty | `db.php` and `functions.php` **not written yet** |
| Git repo | 4 commits | ✅ Active | `6a0dd17` → `d2192a1` → `e821ba6` → `7e465bc` |

**Not present anywhere in the repo:** `sql/schema.sql`, `sql/seed.sql`, `includes/header.php`, `includes/footer.php`, `assets/css/style.css`, `assets/js/app.js`, `index.php`, `thread.php`, `auth.php`, `profile.php`, `admin.php`, seed data, ER diagram image, screenshots, tests, deployment config.

> ⚠️ `assets/` and `config/` exist as empty folders. Git does not track empty folders, so they will disappear on a fresh clone. Add `.gitkeep` files once the structure is created.

### 2.2 Source documents

| File | Status | Notes |
|---|---|---|
| `web_dev_project.docx` (`D:\web_dev_project.docx`) | ✅ **Updated 26 Oct** | 24,562 bytes. Regenerated to match the locked decisions: 4 tables, Tailwind, secure token storage, 4 boards, new folder layout, privacy checklist |
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
| CSS framework | **Tailwind CSS allowed** (CDN/utility classes) for speed, plus custom theme CSS |
| Boards | **4 boards** — `tech`, `science`, `gaming`, `general` |
| Token storage | **Strong version chosen:** `token_lookup = HMAC-SHA256(token, APP_SECRET)` + `password_hash(token)`. The plaintext token is shown once and never stored |
| Spec file | `PROJECT_SPEC.md` removed on purpose — teammates use `ARCHITECTURE.md` + `README.md` |
| Schema SQL file | Still pending — the design is done, `sql/schema.sql` must be written |

---

## 3. Status Against the Full-Stack Process

| # | Step | Status | Detail |
|---|---|---|---|
| 1 | Idea / topic selection | ✅ Done | ThreadZero clearly defined |
| 2 | Problem statement & requirements | ✅ Done | 6 modules, dual auth, asymmetric anonymity, bump/sage, moderation |
| 3 | Tech stack | ✅ Done | HTML5 / Tailwind CSS / Vanilla JS, PHP 8 + PDO, MySQL |
| 4 | Database design / ER schema | ⚠️ Mostly | 4 tables fully specified with columns, FKs, `ON DELETE CASCADE`, unique keys, `AUTO_INCREMENT = 1000`; **`sql/schema.sql` not created; no ER diagram image** |
| 5 | Wireframes / UI flow | ❌ Not started | No screens or navigation flow captured |
| 6 | Architecture diagram | ⚠️ Partly | Folder architecture, shared helpers, and module boundaries documented in prose; **no diagram** |
| 7 | Environment / repo setup | ✅ Done | Git repo + 4 commits, `.gitignore`, XAMPP setup documented; **folder scaffold still empty** |
| 8 | Backend / API implementation | ❌ Not started | Zero PHP written |
| 9 | Frontend implementation | ❌ Not started | Zero HTML/CSS/JS written |
| 10 | Integration / auth implementation | ❌ Not started | Sessions, dual login, anonymity masking unimplemented |
| 11 | Testing | ❌ Not started | No test plan or evidence |
| 12 | Deployment | ❌ Not started | Documented steps only, never executed |
| 13 | Documentation / presentation | ⚠️ Mostly | README + ARCHITECTURE + viva guide done; missing report, ER diagram, screenshots, demo |

---

## 4. Module-by-Module Status

All buildable units are **specified but not started (0%)**.

### Shared foundation

| Unit | File(s) | Scope | Status |
|---|---|---|---|
| DB connection | `config/db.php` | `session_set_cookie_params()`, `session_start()`, `APP_SECRET`, PDO to `threadzero`, `utf8mb4`, exception mode, `FETCH_ASSOC` | ❌ 0% |
| Helpers | `config/functions.php` | `isLoggedIn()`, `isMod()`, `renderAuthorBadge($post, $parentThread)`, `formatPostBody($text)` | ❌ 0% |
| Layout partials | `includes/header.php`, `includes/footer.php` | Shared `<head>`, navbar, board links, theme button, script include | ❌ 0% |
| Data layer | `sql/schema.sql`, `sql/seed.sql` | 4 tables + default boards + demo threads | ❌ 0% (design done, files missing) |

### Feature modules

| Module | File(s) | Scope | Status |
|---|---|---|---|
| 1. Dual Authentication & Session Engine | `auth.php` | Username/password signup+login; 16-digit token via `random_int()`; handle `Vault#6154`; `token_lookup` (HMAC-SHA256) + `password_hash(token)`; `password_verify()` on login | ❌ 0% |
| 2. Board Directory, Search & Catalog Feed | `index.php` | Board nav from `boards`; keyword search (`LIKE`); sort by `bumped_at` / `created_at`; new-thread form (title, content, image upload, `is_anon`); pinned-first ordering | ❌ 0% |
| 3. Core Thread & Reply Engine | `thread.php` | Parent + replies; post numbers (#1001…); Bump vs. Sage; `>greentext`; `>>ID` anchors; author edit/delete; bookmark; report | ❌ 0% |
| 4. User Dashboard & Bookmarks | `profile.php` | Edit bio; own post history (incl. anonymous threads); saved bookmarks with remove | ❌ 0% |
| 5. Trust, Safety & Moderation Suite | `admin.php` | Mod-only (`isMod()`); queue `WHERE is_reported = 1`; unflag, pin/unpin, lock/unlock, delete | ❌ 0% |
| 6. UI Theme & Client-Side Polish | `assets/css/style.css`, `assets/js/app.js` | Light/dark via CSS variables + `localStorage`; click-to-quote; min-length check; delete `confirm()` | ❌ 0% |

---

## 5. Design Decisions Already Locked In

- **4 normalized tables only:** `users`, `boards`, `posts`, `bookmarks`. No `reports` table.
- **Reporting is a flag:** `posts.is_reported TINYINT(1)`; moderation queue filters on it.
- **Moderation state lives on `posts`:** `is_pinned`, `is_locked`.
- **Post IDs start at 1000** (`AUTO_INCREMENT = 1000`), so the first thread is `#1001`.
- **Self-referencing `posts`:** `parent_id IS NULL` = thread, `parent_id = <id>` = reply.
- **Cascade deletes:** deleting a thread removes its replies and bookmarks.
- **Dual auth in one `users` table:** `password_hash` for standard accounts *and* for the hashed token; `token_lookup` (HMAC-SHA256) for indexed token login; `role ENUM('user','mod')`.
- **Asymmetric anonymity:** anonymous OP shows `Anonymous [OP]` while `user_id` is kept; replies are accountable (`@handle`) except when the OP of an anonymous thread replies in their own thread.
- **Bump vs. Sage:** replies set `bumped_at = NOW()` unless Sage is checked.
- **Directory layout:** `assets/{css,js,uploads}`, `config/`, `includes/`, `sql/`, with the five page routes at the root.
- **Shared helpers, not copy-paste:** `renderAuthorBadge()` and `formatPostBody()` live once in `config/functions.php`; navbar/footer live in `includes/`.
- **Theme names:** Retro Light (Yotsuba) and Modern Dark (Tomorrow).
- **Token security (strong version):** the plaintext token is shown once; the DB stores `token_lookup = HMAC-SHA256(token, APP_SECRET)` for indexed lookup plus `password_hash(token)` for verification. Login runs `password_verify()` before `session_regenerate_id(true)`.
- **Privacy hardening:** generic login errors, `HttpOnly`/`SameSite` cookies, CSRF token on every `POST`, no email or IP logging, and all output escaped with `htmlspecialchars()`.
- **Styling:** Tailwind CSS via CDN for layout and utility classes; `assets/css/style.css` holds the light/dark theme variables and custom post/badge styles.
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

### Stage 1 — Scaffold, DB & MVP Foundation (Days 1–5)
- [ ] Create real folders: `assets/css`, `assets/js`, `assets/uploads`, `includes`, `sql` (add `.gitkeep`)
- [ ] Write `sql/schema.sql` exactly as specified in `ARCHITECTURE.md` §3 (4 tables + default boards)
- [ ] Write `sql/seed.sql` (demo users, sample threads and replies)
- [ ] Write `config/db.php` (PDO + session cookie flags + `APP_SECRET`)
- [ ] Write `config/functions.php` (`isLoggedIn`, `isMod`, `renderAuthorBadge`, `formatPostBody`, `csrf_token`)
- [ ] Write `includes/header.php` and `includes/footer.php` (with Tailwind CDN + theme setup)
- [ ] Module 1: `auth.php` — standard signup/login + 16-digit token generator/login with HMAC lookup + bcrypt verification
- [ ] Module 2 (basic): `index.php` — board feed, thread creation, search, sort
- [ ] Base `assets/css/style.css` (Tailwind overrides + light/dark theme variables)

### Stage 2 — Hybrid Mechanics & Full CRUD (Days 6–10)
- [ ] Module 3: `thread.php` — replies, post numbers, Bump/Sage, edit/delete
- [ ] Asymmetric anonymity masking via `renderAuthorBadge()`
- [ ] `>greentext` parser + `>>ID` links
- [ ] Bookmark toggle + report flag from `thread.php`
- [ ] Module 4: `profile.php` — bio `UPDATE`, history, bookmarks `INSERT`/`DELETE`
- [ ] Module 5: `admin.php` — mod queue (`is_reported`), pin/lock/unflag/delete

### Stage 3 — Polish, Testing & Viva Prep (Days 11–15)
- [ ] Module 6: theme switcher, click-to-quote, min-length check, delete confirm
- [ ] Seed realistic threads across all 4 boards
- [ ] Test plan + evidence (auth, CRUD, permissions, cascade delete, anonymity masking)
- [ ] Security checks: token never stored/displayed after signup, rate limiting, CSRF rejection, generic login errors
- [ ] Deployment (XAMPP steps executed end-to-end)
- [ ] README screenshots + ER diagram
- [ ] Rehearse live-modification tasks from `ARCHITECTURE.md` §6

---

## 7. Immediate Next Steps (Priority Order)

1. **Write `sql/schema.sql` + `sql/seed.sql`** from `ARCHITECTURE.md` §3 — 4 tables, `token_lookup`, `is_reported`, default boards. Everything else depends on it.
2. **Create the folder scaffold** (`assets/css`, `assets/js`, `assets/uploads`, `includes`, `sql`) with `.gitkeep` so the team can work in parallel.
3. **Write `config/db.php` + `config/functions.php`** — PDO, hardened session cookie, `APP_SECRET`, auth helpers and `csrf_token()`.
4. **Build `includes/header.php` + `footer.php`** with the Tailwind CDN link and theme toggle, then **`auth.php` + `index.php`** to get a login-and-post MVP.
5. **Add `assets/css/style.css`** for the light/dark variables and custom post/badge styles, and keep `assets/js/app.js` free of framework code.

---

## 8. Risks / Blockers

| Risk | Impact | Mitigation |
|---|---|---|
| `APP_SECRET` committed to a public repo | If both DB and repo leak, `token_lookup` can be brute-forced | Keep only a dev placeholder in git; document rotation; move the real secret to an untracked local config before publishing |
| 16-digit token ≈ 53 bits of entropy | Online guessing if login is unthrottled | Enforce the `sleep()` delay + failed-attempt limit; never log tokens |
| Tailwind Play CDN needs internet and is not production-grade | Styling breaks offline / examiner demo without Wi-Fi | Acceptable for the lab; optionally compile Tailwind to `assets/css/style.css` before the demo |
| Users may assume "anonymous" means untraceable | Privacy promise overstated in the viva | Document clearly: `user_id` is stored, so it is pseudonymity, not untraceability |
| Empty `assets/` and `config/` folders are invisible to git | Scaffold vanishes on clone | Add `.gitkeep` |
| Implementation at 0% with a 10–15 day plan | No slack for debugging | Start Stage 1 now; schema and helpers first |
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
