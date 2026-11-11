# ThreadZero — Project Progress Report

**Project:** ThreadZero — Privacy-First Hybrid Discussion & Imageboard Engine
**Stack:** HTML5, custom CSS3, Vanilla JS · PHP 8 (PDO) · MySQL / MariaDB
**Type:** B.Tech Web Development Lab project (5-member team, 10–15 day timeline)
**Last verified:** 11 Nov 2026 (repo scan of `C:\laragon\www\threadzero`, Modules 1–7 + demo seed implemented)

---

## 1. Executive Summary

> **"All seven modules and the demo dataset are done; viva artefacts remain."**

Modules 1–7 are implemented: dual authentication (`auth.php`), the 5-table schema (`sql/schema.sql`), the PDO/session layer (`config/db.php`), shared helpers (`config/functions.php`), the layout partials (`includes/`), the board feed (`index.php`), the full thread engine (`thread.php`), the profile dashboard (`profile.php`), the moderator panel (`admin.php`), the light/dark theme engine (`assets/css/style.css` + `assets/js/app.js`) and the notification inbox (`notifications.php`). `sql/seed.sql` adds four demo accounts, six threads, seven replies, five bookmarks, five reply notifications and two reported posts. The ER diagram (§3.3) and system request-flow diagram (§5.1) are in `ARCHITECTURE.md`. The remaining work is screenshots and deployment evidence.

| Area | Completion |
|---|---|
| Planning / requirements | ~95% |
| Design / architecture | ~100% |
| Implementation | **~97%** (Modules 1–7 + foundation + demo seed) |
| Testing / deployment | **~65%** (Modules 1–7 and the seed tested against MySQL) |
| Documentation / viva prep | ~90% |
| **Overall project process** | **~92%** |

**Verdict:** every feature module, the demo dataset and the architecture diagrams are complete and tested; only screenshots and deployment evidence remain.

---

## 2. What Actually Exists Right Now

### 2.1 In the repository

| File / Folder | Size | Status | Notes |
|---|---|---|---|
| `README.md` | 8,987 bytes | ✅ Complete | Overview, current build status, feature list, project tree, Laragon setup, demo accounts, roadmap |
| `ARCHITECTURE.md` | 33,320 bytes | ✅ Complete | Coding rules, 5-table schema, ER diagram, privacy rules, form/session conventions, shared helpers, module specs (incl. notifications), request-flow diagram, viva checklist |
| `PROGRESS.md` | this file | ✅ Live | Progress tracker |
| `.gitignore` | 42 bytes | ✅ Present | Ignores `.docx`, `.pdf`, editor folders |
| `assets/css/style.css` | 22,302 bytes | ✅ Complete | Shared stylesheet for every page; light/dark theme variables, page sections, notification cards, active-board and form-message states, action-link colours, mobile breakpoint |
| `index.php` | 13,976 bytes | ✅ Module 2 complete | Board feed, search, sort, new-thread form, image upload, pinned-first ordering |
| `thread.php` | 25,208 bytes | ✅ Module 3 complete | Thread + replies, Bump/Sage, inline edit/delete, bookmark toggle, reporting, locked-thread handling, reply notifications |
| `auth.php` | 17,319 bytes | ✅ Module 1 complete | Standard signup/login, 16-digit token signup/login, POST logout, CSRF, rate limiting |
| `profile.php` | 7,224 bytes | ✅ Module 4 complete | Bio editor, own post/reply history, saved bookmarks with remove, flash messages |
| `admin.php` | 10,264 bytes | ✅ Module 5 complete | Mod-only queue, unflag, pin/unpin, lock/unlock, delete; parent-thread resolution for replies |
| `notifications.php` | 7,261 bytes | ✅ Module 7 complete | Reply inbox, unread count, mark one/all read, delete; CSRF-protected POST actions, recipient-scoped queries |
| `config/db.php` | 1,525 bytes | ✅ Complete | Hardened session, CSRF seed, env-based `APP_SECRET`, PDO to `threadzero`, generic DB error |
| `config/functions.php` | 2,515 bytes | ✅ Complete | `isLoggedIn()`, `isMod()`, `csrfField()`, `verifyCsrfToken()`, `countUnreadNotifications()`, `renderAuthorBadge()`, `formatPostBody()` |
| `includes/header.php` | 2,476 bytes | ✅ Complete | Shared `<head>`, pre-paint theme script, navbar with notification badge, POST logout with CSRF, `app.js` include, theme toggle |
| `includes/footer.php` | 133 bytes | ✅ Complete | Shared footer |
| `sql/schema.sql` | 4,510 bytes | ✅ Complete | Re-runnable 5-table schema + default boards; `AUTO_INCREMENT = 1001`; notification FKs and indexes |
| `sql/seed.sql` | 9,848 bytes | ✅ Complete | Re-runnable demo dataset: 4 users, 4 boards, 6 threads, 7 replies, 5 bookmarks, 5 notifications, 2 reported posts; UTF-8 safe (`SET NAMES utf8mb4`) |
| `assets/uploads/` | — | ✅ Present | Runtime image upload folder, tracked with `.gitkeep` and a no-script `.htaccess` |
| `assets/js/app.js` | 2,973 bytes | ✅ Module 6 complete | Quote helper, theme toggle with `localStorage`, live character counters, minimum-length checks |
| Git repo | ✅ Active | PR #1 merged as `a56d7ad`; Module 1 `99279fd`; Module 2 `90b951b`; Module 3 `130a329`; Modules 4–5 `aa7b8ed`; Module 6 `fd18979`; seed data `7ad3665`; architecture diagrams `802ea40`; Module 7 (PR #4) merged as `7dc1e71` |

**Still to build:** screenshots and deployment evidence.

> ✅ All static HTML prototypes (`index.html`, `thread.html`, `profile.html`, `login.html`, `register.html`) have been removed now that their PHP pages are real.

### 2.2 Source documents

| File | Status | Notes |
|---|---|---|
| `web_dev_project.docx` (`D:\web_dev_project.docx`) | ⚠️ **Updated 3 Nov** | 25,924 bytes. Describes the pre-notifications build (4 tables); `ARCHITECTURE.md` is now authoritative for the 5-table schema |
| `web_dev_project.md` | 🗑️ **Retired on purpose** | Removed so teammates work from one current document (`ARCHITECTURE.md` + `README.md`). The last version is still recoverable from git if ever needed |

**Recover the old spec (reference only):**

```bash
git show 6a0dd17:web_dev_project.md > old_spec_reference.md
```

### 2.3 Decisions locked by the team (26 Oct 2026)

The spec drift has been settled. `ARCHITECTURE.md` is now the single source of truth for implementation, and the old `PROJECT_SPEC.md` is intentionally retired so human teammates work from one current document.

| Decision | Resolution |
|---|---|
| Table count | **5 tables** — `users`, `boards`, `posts`, `bookmarks`, `notifications` |
| Reporting | Boolean flag `posts.is_reported`; no separate `reports` table |
| CSS approach | **Hand-written CSS** in `assets/css/style.css`; Tailwind was dropped after the merged frontend PR (the prototypes use no framework) |
| Frontend prototype | **No static HTML pages remain**; `login.html`, `register.html`, `index.html`, `thread.html` and `profile.html` were all folded into their PHP pages |
| Boards | **4 boards** — `tech`, `science`, `gaming`, `general` |
| Token storage | **Strong version chosen:** `token_lookup = HMAC-SHA256(token, APP_SECRET)` + `password_hash(token)`. The plaintext token is shown once and never stored |
| Token display | Token signup uses POST → redirect → GET: the token is stashed in the session, shown once, then deleted; the page sends `Cache-Control: no-store` |
| Logout | `POST` with an `action=logout` field and CSRF token; the GET logout link was removed |
| Form contract | Hidden `action` field (`signup`, `login`, `token_signup`, `token_login`, `logout`, `create_thread`, `post_reply`, `edit_post`, `delete_post`, `toggle_bookmark`, `report_post`, `update_bio`, `remove_bookmark`, `unflag`, `toggle_pin`, `toggle_lock`, `mark_read`, `mark_all_read`, `delete`; `delete_post` is shared by authors and moderators, `delete` is notification-only) + CSRF token; full table in `ARCHITECTURE.md` §4.1 |
| Spec file | `PROJECT_SPEC.md` removed on purpose — teammates use `ARCHITECTURE.md` + `README.md` |
| Schema SQL file | ✅ Written — `sql/schema.sql` creates the 5 tables and seeds the boards; safe to re-run |

---

## 3. Status Against the Full-Stack Process

| # | Step | Status | Detail |
|---|---|---|---|
| 1 | Idea / topic selection | ✅ Done | ThreadZero clearly defined |
| 2 | Problem statement & requirements | ✅ Done | 7 modules, dual auth, asymmetric anonymity, bump/sage, moderation, notifications |
| 3 | Tech stack | ✅ Done | HTML5 / custom CSS3 / Vanilla JS, PHP 8 + PDO, MySQL |
| 4 | Database design / ER schema | ✅ Done | 5 tables implemented in `sql/schema.sql` with FKs, `ON DELETE CASCADE`/`SET NULL`, unique keys and `AUTO_INCREMENT = 1001`; ER diagram in `ARCHITECTURE.md` §3.3 |
| 5 | Wireframes / UI flow | ✅ Done | All static prototypes were converted into the real PHP pages; shared stylesheet retained |
| 6 | Architecture diagram | ✅ Done | ER and system request-flow diagrams in `ARCHITECTURE.md` §3.3 and §5.1 (Mermaid) |
| 7 | Environment / repo setup | ✅ Done | Git repo, `.gitignore`, Laragon setup documented; `config/`, `includes/`, `sql/` tracked |
| 8 | Backend / API implementation | ✅ Done (Modules 1–7) | `auth.php`, `index.php`, `thread.php`, `profile.php`, `admin.php` and `notifications.php` implemented |
| 9 | Frontend implementation | ✅ Done | All six PHP pages have working views plus the light/dark theme toggle |
| 10 | Integration / auth implementation | ✅ Done (Modules 1–7) | Sessions, dual login, CSRF, rate limiting, threads, replies, bookmarks, profiles, moderation, notifications and theming |
| 11 | Testing | ⚠️ Partial | PHP lint passed; Modules 1–7 flows tested against MySQL; no automated test suite |
| 12 | Deployment | ⚠️ Partial | Laragon steps documented, not yet executed end-to-end |
| 13 | Documentation / presentation | ⚠️ Mostly | README + ARCHITECTURE + ER/request-flow diagrams + viva guide done; missing report, screenshots and deployment evidence |

---

## 4. Module-by-Module Status

The foundation and all seven feature modules are implemented.

### Frontend prototype

| Unit | File(s) | Scope | Status |
|---|---|---|---|
| Shared stylesheet | `assets/css/style.css` | Green/cream light theme and dark theme via CSS variables, login/register/thread/profile/notification sections, active-board and form-message states, action-link colours, mobile breakpoint | ✅ Done |
| Auth UI | `auth.php` views | Dual login, username signup, token generation, one-time token reveal | ✅ Done |
| Feed UI | `index.php` views | Board cards, new-thread form, search/sort bar, thread cards | ✅ Done |
| Thread UI | `thread.php` views | Parent thread, replies, reply form, edit/delete, bookmark/report controls | ✅ Done |
| Profile UI | `profile.php` views | Bio editor, post history, bookmark list with remove | ✅ Done |
| Moderation UI | `admin.php` views | Flagged-post table, approve, pin/lock and delete controls | ✅ Done |
| Notification UI | `notifications.php` views | Unread summary, notification cards, mark-read/mark-all/delete controls, empty state | ✅ Done |

### Shared foundation

| Unit | File(s) | Scope | Status |
|---|---|---|---|
| DB connection | `config/db.php` | Hardened session cookie, CSRF seed, env-based `APP_SECRET`, PDO to `threadzero`, `utf8mb4`, exception mode, `FETCH_ASSOC`, generic error page | ✅ Done |
| Helpers | `config/functions.php` | `isLoggedIn()`, `isMod()`, `csrfField()`, `verifyCsrfToken()`, `countUnreadNotifications($pdo, $userId)`, `renderAuthorBadge($post, $parentThread)`, `formatPostBody($text)` | ✅ Done |
| Layout partials | `includes/header.php`, `includes/footer.php` | Shared `<head>`, pre-paint theme script, navbar with unread notification badge, POST logout + CSRF, theme toggle, footer | ✅ Done |
| Data layer | `sql/schema.sql`, `sql/seed.sql` | 5 tables + default boards; demo users, threads, replies, bookmarks, notifications and reported posts | ✅ Done |

### Feature modules

| Module | File(s) | Scope | Status |
|---|---|---|---|
| 1. Dual Authentication & Session Engine | `auth.php` | Username/password signup+login; 16-digit token via `random_int()`; handle `Vault#6154`; `token_lookup` (HMAC-SHA256) + `password_hash(token)`; `password_verify()`; POST logout; CSRF; rate limiting | ✅ Done |
| 2. Board Directory, Search & Catalog Feed | `index.php` | Board nav from `boards`; keyword search (`LIKE`); sort by `bumped_at` / `created_at`; new-thread form (title, content, image upload, `is_anon`); pinned-first ordering | ✅ Done |
| 3. Core Thread & Reply Engine | `thread.php` | Parent + replies; post numbers (#1001…); Bump vs. Sage; `>greentext`; `>>ID` anchors; author edit/delete; bookmark toggle; report; locked-thread handling | ✅ Done |
| 4. User Dashboard & Bookmarks | `profile.php` | Edit bio; own post history (incl. anonymous threads); saved bookmarks with remove; POST → redirect → GET | ✅ Done |
| 5. Trust, Safety & Moderation Suite | `admin.php` | Mod-only (`isMod()`); 403 guard; queue `WHERE is_reported = 1`; unflag, pin/unpin, lock/unlock, delete; replies resolve to their parent thread | ✅ Done |
| 6. UI Theme & Client-Side Polish | `assets/css/style.css`, `assets/js/app.js` | Light/dark via CSS variables + `localStorage`; pre-paint theme script; click-to-quote; live character counters; min-length check; delete `confirm()` | ✅ Done |
| 7. Reply Notifications | `notifications.php`, `includes/header.php`, `thread.php` | Reply insert notifies the thread owner (not self); navbar unread count; recipient-scoped inbox; mark one/all read and delete; POST + CSRF | ✅ Done |

---

## 5. Design Decisions Already Locked In

- **5 normalized tables only:** `users`, `boards`, `posts`, `bookmarks`, `notifications`. No `reports` table.
- **Reporting is a flag:** `posts.is_reported TINYINT(1)`; moderation queue filters on it.
- **Moderation state lives on `posts`:** `is_pinned`, `is_locked`.
- **Post IDs start at 1001** (`AUTO_INCREMENT = 1001`), so the first thread is `#1001`.
- **Self-referencing `posts`:** `parent_id IS NULL` = thread, `parent_id = <id>` = reply.
- **Cascade deletes:** deleting a thread removes its replies, bookmarks and notifications; deleting an actor sets `notifications.actor_id` to `NULL`.
- **Notifications are recipient-scoped:** every query filters on `recipient_id = $_SESSION['user_id']`; the thread owner is notified on a new reply unless they replied to themselves.
- **Dual auth in one `users` table:** `password_hash` for standard accounts *and* for the hashed token; `token_lookup` (HMAC-SHA256) for indexed token login; `role ENUM('user','mod')`.
- **Asymmetric anonymity:** anonymous OP shows `Anonymous [OP]` while `user_id` is kept; replies are accountable (`@handle`) except when the OP of an anonymous thread replies in their own thread.
- **Bump vs. Sage:** replies set `bumped_at = NOW()` unless Sage is checked.
- **Directory layout:** `assets/{css,js,uploads}`, `config/`, `includes/`, `sql/`, with the `.php` page routes at the root. No static HTML routes remain.
- **Image uploads:** extension whitelist plus `getimagesize()` MIME validation, 2 MB limit, random filenames, and a no-script `.htaccess` in `assets/uploads/`.
- **Shared helpers, not copy-paste:** `renderAuthorBadge()` and `formatPostBody()` live once in `config/functions.php`; `csrfField()` and `verifyCsrfToken()` live there too; navbar/footer live in `includes/`.
- **Theme names:** Retro Light (Yotsuba) and Modern Dark (Tomorrow) — implemented in Module 6 with `data-theme="dark"` on `<html>`, CSS custom properties and a `localStorage` preference.
- **Token security (strong version):** the plaintext token is shown once; the DB stores `token_lookup = HMAC-SHA256(token, APP_SECRET)` for indexed lookup plus `password_hash(token)` for verification. Login runs `password_verify()` before `session_regenerate_id(true)`. `APP_SECRET` comes from `THREADZERO_APP_SECRET` with a dev fallback.
- **Token display:** POST → redirect → GET; the token lives in the session for exactly one render, then is deleted. `Cache-Control: no-store` keeps it out of browser caches.
- **Privacy hardening:** generic login errors, `HttpOnly`/`SameSite`/HTTPS-aware cookies, CSRF token on every `POST`, no email or IP logging, and all output escaped with `htmlspecialchars()`.
- **Styling:** one hand-written stylesheet (`assets/css/style.css`) with a shared light theme, a `data-theme="dark"` override layer, per-page sections and a mobile breakpoint. No Tailwind, no Bootstrap, no build step.
- **Form contract:** every POST form posts a hidden `action` plus a CSRF token; field names and redirect rules are standardised in `ARCHITECTURE.md` §4.1.
- **Docs:** `ARCHITECTURE.md` is the single source of truth; `PROJECT_SPEC.md` is retired intentionally.
- **Code style for viva safety:** flat procedural PHP, PDO prepared statements with `?`, native `$_SESSION`, plain Vanilla JS, no JS frameworks, no mandatory build step, commented SQL blocks.

---

## 6. Build Checklist

### Stage 0 — Completed groundwork
- [x] Define concept, modules, and viva plan
- [x] Initialize git repo + `.gitignore`
- [x] Write `README.md` and `ARCHITECTURE.md`
- [x] Design the 5-table schema (columns, keys, cascades)
- [x] Define the repository directory layout
- [x] Static frontend prototypes: static HTML pages + `assets/css/style.css` (PR #1)

### Stage 1 — Scaffold, DB & MVP Foundation (Days 1–5)
- [x] Create `assets/css` and write the shared prototype stylesheet
- [x] Create the foundation folders: `config`, `includes`, `sql`
- [x] Write `sql/schema.sql` exactly as specified in `ARCHITECTURE.md` §3 (5 tables + default boards)
- [x] Write `sql/seed.sql` (4 demo users, 6 threads, 7 replies, bookmarks, notifications and reports)
- [x] Write `config/db.php` (PDO + session cookie flags + env-based `APP_SECRET`)
- [x] Write `config/functions.php` (`isLoggedIn`, `isMod`, `renderAuthorBadge`, `formatPostBody`, CSRF helpers)
- [x] Write `includes/header.php` and `includes/footer.php`
- [x] Module 1: `auth.php` — standard signup/login + 16-digit token generator/login with HMAC lookup + bcrypt verification
- [x] Module 2: `index.php` — board feed, thread creation, image upload, search and sort
- [x] Module 3: `thread.php` — parent thread, replies, post numbers, anonymity masking, greentext
- [x] Reply form with Bump/Sage, reply image upload and locked-thread handling
- [x] Inline edit/delete for authors; moderator delete
- [x] Bookmark toggle + report flag from `thread.php`

### Stage 2 — Hybrid Mechanics & Full CRUD (Days 6–10)
- [x] Asymmetric anonymity masking via `renderAuthorBadge()`
- [x] `>greentext` parser + `>>ID` links
- [x] Module 4: `profile.php` — bio `UPDATE`, history, bookmarks `INSERT`/`DELETE`
- [x] Module 5: `admin.php` — mod queue (`is_reported`), pin/lock/unflag/delete

### Stage 3 — Polish, Testing & Viva Prep (Days 11–15)
- [x] Module 6: light/dark theme switcher, live character counters and minimum-length checks (quote helper and delete confirm already done)
- [x] Module 7: reply notifications, navbar unread badge, notification inbox with mark-read and delete
- [x] Seed realistic threads across all 4 boards
- [ ] Test plan + evidence (auth, CRUD, permissions, cascade delete, anonymity masking)
- [ ] Security checks: token never stored/displayed after signup, rate limiting, CSRF rejection, generic login errors
- [ ] Deployment (Laragon steps executed end-to-end)
- [x] ER + system request-flow diagrams in `ARCHITECTURE.md` (§3.3 and §5.1)
- [ ] README screenshots
- [ ] Rehearse live-modification tasks from `ARCHITECTURE.md` §6

---

## 7. Immediate Next Steps (Priority Order)

1. **Run the full manual test pass** and capture screenshots for the report.
2. **Rehearse the viva walkthrough** using the ER (§3.3) and request-flow (§5.1) diagrams.
3. **Execute the Laragon deployment steps end-to-end** and record the evidence.

---

## 8. Risks / Blockers

| Risk | Impact | Mitigation |
|---|---|---|
| `APP_SECRET` committed to a public repo | If both DB and repo leak, `token_lookup` can be brute-forced | Only a dev fallback is committed; set `THREADZERO_APP_SECRET` for real deployments and rotate before publishing |
| 16-digit token ≈ 53 bits of entropy | Online guessing if login is unthrottled | Enforce the `sleep()` delay + failed-attempt limit; never log tokens |
| Users may assume "anonymous" means untraceable | Privacy promise overstated in the viva | Document clearly: `user_id` is stored, so it is pseudonymity, not untraceability |
| User-uploaded images | Malicious files or script execution from `assets/uploads/` | Extension + `getimagesize()` MIME checks, 2 MB cap, random filenames, and a no-script `.htaccess` |
| Presentation artefacts still due | No screenshots for the report | Capture screenshots before the viva |
| Module ownership spread across 5 people | Handover gaps during the viva | Keep `ARCHITECTURE.md` §6 ownership list current and rehearse each demo task |
| No tests planned | Anonymity and permission bugs are subtle and high-risk | Explicitly test author-edit, OP masking, cascade delete, CSRF rejection |
| Notification inbox exposure | A user could read or delete another user's notifications | Every notification query filters on `recipient_id`; actions are POST + CSRF and scoped to the session user |

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
| 4 Nov 2026 | Completed Module 3 in `thread.php`: replies with Bump/Sage, validated reply image uploads, inline edit/delete, moderator delete, bookmark toggle, reporting and locked-thread handling. Added `assets/js/app.js` (`quotePost()`), wired it into the header, added `.form-success`, removed the now-redundant `thread.html`, and updated the docs. Tested the full thread lifecycle against MySQL. |
| 5 Nov 2026 | Completed Modules 4–5: `profile.php` (bio editor, post/reply history, saved bookmarks with remove) and `admin.php` (403 guard, reported queue, unflag, pin/unpin, lock/unlock, delete). Standardised moderation forms on `action`, resolved pin/lock to parent threads, added POST → redirect → GET flash messages and shared form-message classes, removed the last static prototype (`profile.html`), and updated the docs. Tested both modules against MySQL. |
| 5 Nov 2026 | Completed Module 6: light/dark theme engine with CSS variables, `data-theme` and a pre-paint `localStorage` script in `includes/header.php`, plus live character counters and minimum-length form checks in `assets/js/app.js`. Added dark-mode styling for cards, inputs, tables, badges, form messages and action links, kept `quotePost()` in sync with the counter, and allowed an empty bio to be saved. Verified both themes, persistence and the JS helpers in the browser. |
| 6 Nov 2026 | Added `sql/seed.sql`: four demo accounts, six threads across all four boards, seven replies (including OP masking and quote links), five bookmarks and two reported posts for the moderator queue. Replaced the invalid placeholder bcrypt hashes and token lookup with verified values, added `SET NAMES utf8mb4` for Windows client compatibility, documented the demo logins in `README.md`/`ARCHITECTURE.md`, and tested the schema + seed against MySQL and the running app. |
| 6 Nov 2026 | Added Mermaid ER (§3.3) and system request-flow (§5.1) diagrams to `ARCHITECTURE.md`, validated both against the Mermaid renderer, and updated the README/PROGRESS status to show the architecture documentation complete. |
| 11 Nov 2026 | Merged PR #4 (Module 7, notifications): added the `notifications` table to `sql/schema.sql`, reply notifications in `thread.php`, the unread navbar badge in `includes/header.php`, the `notifications.php` inbox, themed notification styles and seeded demo notifications. Removed the redundant `sql/notifications.sql` migration, added the `countUnreadNotifications()` helper, added flash messages and singular/plural wording to the inbox, restored the descriptive ER-diagram attributes, and synchronised README, ARCHITECTURE and PROGRESS on the 5-table schema. |
