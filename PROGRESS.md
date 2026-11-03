# ThreadZero — Project Progress Report

**Project:** ThreadZero — Privacy-First Hybrid Discussion & Imageboard Engine
**Stack:** HTML5, custom CSS3, Vanilla JS · PHP 8 (PDO) · MySQL / MariaDB
**Type:** B.Tech Web Development Lab project (5-member team, 10–15 day timeline)
**Last verified:** 3 Nov 2026 (repo scan of `C:\laragon\www\web_dev_project`, PR #1 merged)

---

## 1. Executive Summary

> **"The UI is drawn; the application behind it is still unwritten."**

The repository now has a complete static frontend prototype — five HTML pages plus a shared stylesheet, merged from PR #1. The database is fully specified as a 4-table schema. What is still missing is the application: no PHP, no JavaScript, no `schema.sql`, and no seed data.

| Area | Completion |
|---|---|
| Planning / requirements | ~90% |
| Design / architecture | ~70% |
| Implementation | **~10%** (static frontend only) |
| Testing / deployment | **0%** |
| Documentation / viva prep | ~65% |
| **Overall project process** | **~40%** |

**Verdict:** planning and design are largely done; the frontend is a static prototype and every dynamic feature is still at 0%.

---

## 2. What Actually Exists Right Now

### 2.1 In the repository

| File / Folder | Size | Status | Notes |
|---|---|---|---|
| `README.md` | 7,004 bytes | ✅ Complete | Overview, current build status, feature list, project tree, XAMPP setup, roadmap |
| `ARCHITECTURE.md` | 21,655 bytes | ✅ Complete | Coding rules, 4-table schema, privacy rules, prototype + form conventions, shared helpers, module specs, viva checklist |
| `PROGRESS.md` | this file | ✅ Live | Progress tracker |
| `.gitignore` | 42 bytes | ✅ Present | Ignores `.docx`, `.pdf`, editor folders |
| `assets/css/style.css` | 12,238 bytes | ✅ Prototype complete | 719-line shared stylesheet for all five pages; green/cream theme, responsive block |
| `index.html` | 5,446 bytes | ✅ Static prototype | Home feed, board cards, search/sort, new-thread form |
| `login.html` | 3,466 bytes | ✅ Static prototype | Username/password + 16-digit token login forms |
| `register.html` | 3,638 bytes | ✅ Static prototype | Username signup + token generation + one-time reveal preview |
| `thread.html` | 3,642 bytes | ✅ Static prototype | OP + replies, post numbers, reply form with Sage |
| `profile.html` | 4,326 bytes | ✅ Static prototype | Bio editor, post history, bookmarks |
| `config/` | — | ❌ Missing | `db.php` and `functions.php` **not written yet** |
| `includes/` | — | ❌ Missing | `header.php` and `footer.php` **not written yet** |
| `sql/` | — | ❌ Missing | `schema.sql` and `seed.sql` **not written yet** |
| `assets/js/`, `assets/uploads/` | — | ❌ Missing | No JavaScript or upload folder yet |
| Git repo | 21 commits | ✅ Active | PR #1 (`frontend-work`) merged as `a56d7ad` |

**Not present anywhere in the repo:** `sql/schema.sql`, `sql/seed.sql`, `includes/header.php`, `includes/footer.php`, `assets/js/app.js`, `index.php`, `thread.php`, `auth.php`, `profile.php`, `admin.php`, seed data, ER diagram image, screenshots, tests, deployment config.

> ⚠️ Only `assets/css/` is tracked. `assets/js/`, `assets/uploads/`, `config/`, `includes/` and `sql/` still need creating; Git does not track empty folders, so add `.gitkeep` files.

### 2.2 Source documents

| File | Status | Notes |
|---|---|---|
| `web_dev_project.docx` (`D:\web_dev_project.docx`) | ✅ **Updated 3 Nov** | 25,924 bytes. Regenerated to match the merged frontend: 5 static HTML pages, custom CSS (no Tailwind), 4 tables, secure token storage, 4 boards, form/session conventions, privacy checklist |
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
| Frontend prototype | **5 static HTML pages** merged in PR #1; they are design references, and the production pages remain `.php` |
| Boards | **4 boards** — `tech`, `science`, `gaming`, `general` |
| Token storage | **Strong version chosen:** `token_lookup = HMAC-SHA256(token, APP_SECRET)` + `password_hash(token)`. The plaintext token is shown once and never stored |
| Form contract | Hidden `action` field (`signup`, `login`, `token_signup`, `token_login`, `logout`, `create_thread`, `reply`, `update_bio`, `remove_bookmark`, `report`, mod actions) + CSRF token; full table in `ARCHITECTURE.md` §4.1 |
| Spec file | `PROJECT_SPEC.md` removed on purpose — teammates use `ARCHITECTURE.md` + `README.md` |
| Schema SQL file | Still pending — the design is done, `sql/schema.sql` must be written |

---

## 3. Status Against the Full-Stack Process

| # | Step | Status | Detail |
|---|---|---|---|
| 1 | Idea / topic selection | ✅ Done | ThreadZero clearly defined |
| 2 | Problem statement & requirements | ✅ Done | 6 modules, dual auth, asymmetric anonymity, bump/sage, moderation |
| 3 | Tech stack | ✅ Done | HTML5 / custom CSS3 / Vanilla JS, PHP 8 + PDO, MySQL |
| 4 | Database design / ER schema | ⚠️ Mostly | 4 tables fully specified with columns, FKs, `ON DELETE CASCADE`, unique keys, `AUTO_INCREMENT = 1000`; **`sql/schema.sql` not created; no ER diagram image** |
| 5 | Wireframes / UI flow | ✅ Done | Five static HTML pages + shared stylesheet merged in PR #1; admin screen not mocked up |
| 6 | Architecture diagram | ⚠️ Partly | Folder architecture, shared helpers, and module boundaries documented in prose; **no diagram** |
| 7 | Environment / repo setup | ✅ Done | Git repo + 21 commits, `.gitignore`, XAMPP setup documented; **config/includes/sql folders still missing** |
| 8 | Backend / API implementation | ❌ Not started | Zero PHP written |
| 9 | Frontend implementation | ⚠️ Partial | 5 static HTML pages + 11 KB stylesheet; **no JavaScript, no PHP integration, no admin page** |
| 10 | Integration / auth implementation | ❌ Not started | Sessions, dual login, anonymity masking unimplemented |
| 11 | Testing | ❌ Not started | No test plan or evidence |
| 12 | Deployment | ❌ Not started | Documented steps only, never executed |
| 13 | Documentation / presentation | ⚠️ Mostly | README + ARCHITECTURE + viva guide done; missing report, ER diagram, screenshots, demo |

---

## 4. Module-by-Module Status

The static UI exists, but every dynamic unit is still at 0%.

### Frontend prototype

| Unit | File(s) | Scope | Status |
|---|---|---|---|
| Static UI mockups | `index.html`, `login.html`, `register.html`, `thread.html`, `profile.html`, `assets/css/style.css` | Shared 719-line stylesheet; home/board cards, dual login, signup + token reveal, thread/replies, bio + bookmarks; mobile breakpoint | ✅ Done (PR #1) |

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
| 1. Dual Authentication & Session Engine | `auth.php` | Username/password signup+login; 16-digit token via `random_int()`; handle `Vault#6154`; `token_lookup` (HMAC-SHA256) + `password_hash(token)`; `password_verify()` on login | ❌ 0% (login/register mockups only) |
| 2. Board Directory, Search & Catalog Feed | `index.php` | Board nav from `boards`; keyword search (`LIKE`); sort by `bumped_at` / `created_at`; new-thread form (title, content, image upload, `is_anon`); pinned-first ordering | ❌ 0% (index mockup only) |
| 3. Core Thread & Reply Engine | `thread.php` | Parent + replies; post numbers (#1001…); Bump vs. Sage; `>greentext`; `>>ID` anchors; author edit/delete; bookmark; report | ❌ 0% (thread mockup only) |
| 4. User Dashboard & Bookmarks | `profile.php` | Edit bio; own post history (incl. anonymous threads); saved bookmarks with remove | ❌ 0% (profile mockup only) |
| 5. Trust, Safety & Moderation Suite | `admin.php` | Mod-only (`isMod()`); queue `WHERE is_reported = 1`; unflag, pin/unpin, lock/unlock, delete | ❌ 0% (no mockup yet) |
| 6. UI Theme & Client-Side Polish | `assets/css/style.css`, `assets/js/app.js` | Light/dark via CSS variables + `localStorage`; click-to-quote; min-length check; delete `confirm()` | ⚠️ Partial — base CSS done; JS, dark mode and theme toggle pending |

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
- **Directory layout:** `assets/{css,js,uploads}`, `config/`, `includes/`, `sql/`, with the five `.php` page routes at the root. The five `.html` files are static design references, not routes.
- **Shared helpers, not copy-paste:** `renderAuthorBadge()` and `formatPostBody()` live once in `config/functions.php`; `csrf_token()`, `csrf_field()` and `csrf_verify()` live there too; navbar/footer live in `includes/`.
- **Theme names:** Retro Light (Yotsuba) and Modern Dark (Tomorrow) — planned for Module 6; the merged stylesheet currently ships one green/cream light theme.
- **Token security (strong version):** the plaintext token is shown once; the DB stores `token_lookup = HMAC-SHA256(token, APP_SECRET)` for indexed lookup plus `password_hash(token)` for verification. Login runs `password_verify()` before `session_regenerate_id(true)`.
- **Privacy hardening:** generic login errors, `HttpOnly`/`SameSite` cookies, CSRF token on every `POST`, no email or IP logging, and all output escaped with `htmlspecialchars()`.
- **Styling:** one hand-written stylesheet (`assets/css/style.css`, 719 lines) with a shared green/cream theme, per-page sections and a mobile breakpoint. No Tailwind, no Bootstrap, no build step.
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
- [x] Static frontend prototypes: 5 HTML pages + `assets/css/style.css` (PR #1)

### Stage 1 — Scaffold, DB & MVP Foundation (Days 1–5)
- [x] Create `assets/css` and write the shared prototype stylesheet
- [ ] Create remaining folders: `assets/js`, `assets/uploads`, `includes`, `sql` (add `.gitkeep`)
- [ ] Write `sql/schema.sql` exactly as specified in `ARCHITECTURE.md` §3 (4 tables + default boards)
- [ ] Write `sql/seed.sql` (demo users, sample threads and replies)
- [ ] Write `config/db.php` (PDO + session cookie flags + `APP_SECRET`)
- [ ] Write `config/functions.php` (`isLoggedIn`, `isMod`, `renderAuthorBadge`, `formatPostBody`, CSRF helpers)
- [ ] Write `includes/header.php` and `includes/footer.php` by extracting the repeated markup from the HTML prototypes
- [ ] Module 1: `auth.php` — standard signup/login + 16-digit token generator/login with HMAC lookup + bcrypt verification
- [ ] Module 2 (basic): `index.php` — convert `index.html` to a working board feed, thread creation, search, sort

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
2. **Create the remaining folders** (`assets/js`, `assets/uploads`, `includes`, `sql`) with `.gitkeep`, then **write `config/db.php` + `config/functions.php`** — PDO, hardened session cookie, `APP_SECRET`, auth helpers and the CSRF functions.
3. **Extract `includes/header.php` + `footer.php`** from the repeated markup in the HTML prototypes, so every PHP page uses one navbar and one stylesheet link.
4. **Convert prototypes to PHP in this order:** `index.html` → `index.php`, then `login.html`/`register.html` → `auth.php`, then `thread.html` → `thread.php`, then `profile.html` → `profile.php`. Keep the existing CSS classes and add `csrf_field()` + hidden `action` inputs.
5. **Add `assets/js/app.js`** for the theme toggle, click-to-quote, minimum-length checks and delete confirmation; then build `admin.php` from the Module 5 spec.

---

## 8. Risks / Blockers

| Risk | Impact | Mitigation |
|---|---|---|
| `APP_SECRET` committed to a public repo | If both DB and repo leak, `token_lookup` can be brute-forced | Keep only a dev placeholder in git; document rotation; move the real secret to an untracked local config before publishing |
| 16-digit token ≈ 53 bits of entropy | Online guessing if login is unthrottled | Enforce the `sleep()` delay + failed-attempt limit; never log tokens |
| Static HTML and PHP views drift apart | Duplicate markup diverges and the UI breaks during conversion | Treat `.html` as reference only; move shared markup into `includes/`; delete or archive prototypes once each page is converted |
| Prototype forms lack CSRF and `action` fields | Copy-pasting markup into PHP would ship insecure forms | Follow `ARCHITECTURE.md` §4.1; add `csrf_field()` and a hidden `action` to every POST form during conversion |
| Users may assume "anonymous" means untraceable | Privacy promise overstated in the viva | Document clearly: `user_id` is stored, so it is pseudonymity, not untraceability |
| Only `assets/css/` is tracked | `assets/js`, `config`, `includes`, `sql` vanish on clone | Create them with `.gitkeep` before parallel work starts |
| Dynamic implementation at 0% with a 10–15 day plan | No slack for debugging | Start Stage 1 now; schema and helpers first |
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
