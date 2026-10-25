# ThreadZero — Project Progress Report

**Project:** ThreadZero — Privacy-First Hybrid Discussion & Imageboard Engine
**Stack:** HTML5, CSS3, Vanilla JS · PHP 8 (PDO) · MySQL / MariaDB
**Type:** B.Tech Web Development Lab project (5-member team, 10–15 day timeline)
**Last verified:** 25 Oct 2026 (folder scan of `D:\web_dev_project`)

---

## 1. Executive Summary

> **"The project charter and feature plan are done; the project itself is not."**

ThreadZero is well specified but not yet built. Planning, scope, and viva preparation are in good shape. There is **no application code, no database schema, no repository, and no test or deployment evidence** yet.

| Area | Completion |
|---|---|
| Planning / requirements | ~85–90% |
| Design / architecture | ~20–30% |
| Implementation | **0%** |
| Testing / deployment | **0%** |
| Documentation / viva prep | ~15–20% |
| **Overall project process** | **~20–25%** |

**Verdict:** roughly one quarter of the full project lifecycle is complete, almost entirely in the planning phase. All six feature modules remain to be built.

---

## 2. What Actually Exists Right Now

Verified contents of the project folder:

| File | Size | Status | Notes |
|---|---|---|---|
| `web_dev_project.docx` | 18,830 bytes | ✅ **Complete** | Full project brief: concept, all 6 modules, 3-stage timeline, SQL CRUD cheat sheet, 5-member role-wise viva guide. Verified: 103 non-empty paragraphs, ends correctly on the closing `====` delimiter; contains no tables or embedded images |
| `web_dev_project.md` | 3,955 bytes (45 lines) | ⚠️ **Truncated** | Technical spec + AI-agent coding rules. Ends mid-file at `admin.php` with a trailing newline — byte-level check confirms the file itself is incomplete, not a read error |
| `progress.md` | this file | ✅ New | Progress tracker |

**Not present:** `schema.sql`, `db.php`, `auth.php`, `index.php`, `thread.php`, `profile.php`, `admin.php`, `style.css`, `app.js`, seed data, `README.md`, ER diagram, wireframes, architecture diagram, screenshots, tests, deployment config, `.git` repository.

### 2.1 Source document audit

**`web_dev_project.docx` — complete.** All 5 sections are present and terminate cleanly:
concept → 6 modules → 3-stage timeline → SQL CRUD cheat sheet → role-wise viva guide (2 frontend, 2 backend, 1 database architect).

**`web_dev_project.md` — incomplete.** It contains only:
- Section 1: Critical instructions for AI coding agents
- Section 2: Project identity & core architecture
- Section 3: Modular file map (cut off)

It stops inside the directory tree at `admin.php` (line 45). Missing or unverifiable from the `.md` alone: the rest of the file map, Module 6 entry, any database schema section, security notes, and all later sections.

**Discrepancies between the two documents:**

| Topic | `.docx` | `.md` |
|---|---|---|
| Module 6 (theme/JS polish) | ✅ Listed with 3 features | ❌ Absent (lost in truncation) |
| Image upload on new thread | ✅ Mentioned in Module 2 | ❌ Not mentioned |
| Tech stack wording | "Vanilla JavaScript (Fetch API)" | "Vanilla JS, optional `fetch()` for simple enhancements" |
| File map count | Not stated | Header says "7 Files" but the tree lists 9 entries before cutting off |

> **Decision:** treat `web_dev_project.docx` as the **single source of truth**. The `.md` should be regenerated from it (or restored from the original) before being handed to any coding agent — an agent reading only the `.md` would never know Module 6, image upload, or the database requirements exist.

---

## 3. Status Against the Full-Stack Process

| # | Step | Status | Detail |
|---|---|---|---|
| 1 | Idea / topic selection | ✅ Done | ThreadZero clearly defined: Reddit boards + 4chan thread mechanics + Mullvad-style auth |
| 2 | Problem statement & requirements | ✅ Mostly done | Core concept, 6 modules, anti-harassment anonymity model documented |
| 3 | Tech stack | ✅ Done | HTML5 / CSS3 / Vanilla JS, PHP 8 + PDO, MySQL, no build step |
| 4 | Database design / ER schema | ⚠️ Partly | 5 tables named conceptually (users, boards, posts, bookmarks, reports); **no `schema.sql`, no columns, keys, indexes, or ER diagram** |
| 5 | Wireframes / UI flow | ❌ Not started | No screens or navigation flow captured |
| 6 | Architecture diagram | ❌ Not started | Module interaction only described in prose |
| 7 | Environment / repo setup | ❌ Not started | No git repo, no folder scaffold, no XAMPP/phpMyAdmin setup notes |
| 8 | Backend / API implementation | ❌ Not started | Modules described, zero PHP written |
| 9 | Frontend implementation | ❌ Not started | No HTML/CSS/JS written |
| 10 | Integration / auth implementation | ❌ Not started | Sessions, dual login, anonymity masking all unimplemented |
| 11 | Testing | ❌ Not started | No test plan or evidence |
| 12 | Deployment | ❌ Not started | No hosting/local deployment steps |
| 13 | Documentation / presentation | ⚠️ Partly | Brief + viva cheat sheet exist; no README, report, screenshots, or demo |

---

## 4. Module-by-Module Status

All six modules are **specified but not started (0%)**.

| Module | File(s) | Scope | Status |
|---|---|---|---|
| 1. Dual Authentication & Session Engine | `auth.php` | Username/password signup+login; 16-digit Mullvad-style token via `random_int()`; `SHA-256` lookup + `password_hash()`; `Vault#6154` handle | ❌ 0% |
| 2. Board Directory, Search & Catalog Feed | `index.php` | Board nav (`/tech/`, `/science/`, `/gaming/`, `/design/`, `/general/`); keyword search; sort by bumped/newest/replies; new-thread form with anonymous toggle | ❌ 0% |
| 3. Core Thread & Reply Engine | `thread.php` | Chronological replies with post numbers; Bump vs. Sage; `>greentext` parser; `>>1001` anchor links; author edit/delete | ❌ 0% |
| 4. User Dashboard & Bookmarks | `profile.php` | Edit bio (`UPDATE`); post history incl. own anonymous threads; bookmark add/remove | ❌ 0% |
| 5. Trust, Safety & Moderation Suite | `admin.php` | Report form; moderator panel for pin / lock / resolve report / delete | ❌ 0% |
| 6. UI Theme & Client-Side Polish | `style.css`, `app.js` | Light/dark theme via CSS variables + `localStorage`; click-to-quote; live character counter; delete confirmation | ❌ 0% |

---

## 5. Design Decisions Already Locked In

These are settled and should not be re-litigated during implementation:

- **Dual auth:** standard account *and* zero-PII 16-digit token account, auto-handle `Vault#6154` derived from the last 4 digits.
- **Asymmetric anonymity:** anonymous OP is masked publicly as `Anonymous [OP]`, but `user_id` is still stored so the author keeps edit/delete rights.
- **Accountable replies:** replies show `@handle` — except when the OP of an anonymous thread replies in their own thread, which auto-masks as `Anonymous [OP]`.
- **Bump vs. Sage:** replies set `bumped_at = NOW()` unless "Sage" is checked.
- **Self-referencing `posts` table:** `parent_id IS NULL` = thread, `parent_id = <id>` = reply.
- **Cascade deletes:** `ON DELETE CASCADE` cleans up replies and bookmarks when a thread is deleted.
- **Code style for viva safety:** flat procedural PHP, PDO prepared statements with `?` placeholders, native `$_SESSION`, plain Vanilla JS, no frameworks or build steps, commented SQL blocks.

---

## 6. What's Missing — Build Checklist

### Stage 1 — Core DB & MVP Foundation (Days 1–5)
- [ ] Create repo + folder scaffold (`threadzero/`)
- [ ] Write `schema.sql`: `users`, `boards`, `posts`, `bookmarks`, `reports` — PKs, FKs, `ON DELETE CASCADE`, indexes
- [ ] Draw ER diagram
- [ ] Write `db.php`: PDO connection, `session_start()`, 3 helper functions
- [ ] Module 1: `auth.php` — standard signup/login + 16-digit token generator
- [ ] Module 2 (basic): `index.php` — thread listing + create thread (`INSERT`)
- [ ] Minimal `style.css` layout

### Stage 2 — Hybrid Mechanics & Full CRUD (Days 6–10)
- [ ] Module 3: `thread.php` — replies, post numbers, Bump/Sage, edit/delete
- [ ] Asymmetric anonymity masking logic
- [ ] `>greentext` parser + `>>ID` anchor links
- [ ] Module 4: `profile.php` — bio `UPDATE`, history, bookmarks `INSERT`/`DELETE`
- [ ] Module 5: `admin.php` — report form + moderator pin/lock/delete

### Stage 3 — Polish, Testing & Viva Prep (Days 11–15)
- [ ] Module 6: theme switcher, click-to-quote, char counter, delete confirm
- [ ] Seed realistic sample threads across all 5 boards
- [ ] Test plan + evidence (auth, CRUD, permissions, cascade delete)
- [ ] Deployment (local XAMPP steps documented, or hosting)
- [ ] `README.md` with setup instructions + screenshots
- [ ] Rehearse live-modification tasks from the viva guide
- [ ] Restore the truncated sections of `web_dev_project.md`

---

## 7. Immediate Next Steps (Priority Order)

1. **Use `web_dev_project.docx` as the source of truth.** The `.md` is confirmed truncated; regenerate it from the `.docx` (or restore the original) before handing it to a coding agent.
2. **Initialize the repo and folder scaffold** so the 5-person team can work in parallel without collisions.
3. **Write `schema.sql` first.** Everything else depends on it; it also unblocks the database architect's viva prep.
4. **Build `db.php` + `auth.php`** as the shared foundation before anyone touches feeds or threads.
5. **Split modules by role** (2 frontend, 2 backend, 1 database) per the brief's viva guide, and track each module in section 4 of this file.

---

## 8. Risks / Blockers

| Risk | Impact | Mitigation |
|---|---|---|
| Spec `.md` truncated at line 45 | Builders may miss later requirements (Module 6, image upload, DB rules) | `.docx` is complete and verified — use it as source of truth; regenerate `.md` |
| No git repo yet | No version control, teammates overwrite each other | `git init` before any code |
| Implementation at 0% with a 10–15 day plan | Timeline compression; no slack for debugging | Start Stage 1 immediately; schema first |
| 6 modules, 5 people | Module 6 + integration unowned | Assign frontend devs to own Modules 2/6 UI and Module 3 UI |
| No tests planned | Anonymity/permission bugs are subtle and high-risk | Test author-edit, OP-masking, and cascade delete explicitly |

---

## 9. How to Update This File

- Update the **Last verified** date and the completion percentages in section 1 after each work session.
- Change a module's status in section 4 only when its core flows work end-to-end (not when the file merely exists).
- Tick checklist items in section 6 as they are genuinely done and verified.
- Keep the executive summary honest — a module is not "done" because a file was created.
