USE threadzero;

-- Ensure UTF-8 is used for this script even when the MySQL client default is
-- a legacy Windows code page (the demo text contains an em dash).
SET NAMES utf8mb4;

-- ============================================================
-- ThreadZero demo seed
--
-- WARNING: this script TRUNCATEs all five tables and rebuilds the
-- demo dataset. Run it only against a development database, never
-- against data you want to keep.
--
-- Typical use:
--   mysql -u root < sql/schema.sql
--   mysql -u root < sql/seed.sql
-- ============================================================

-- Clear existing demo data safely (order respects foreign keys)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE notifications;
TRUNCATE TABLE bookmarks;
TRUNCATE TABLE posts;
TRUNCATE TABLE boards;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Reset post IDs to start at 1001 per ARCHITECTURE.md
ALTER TABLE posts AUTO_INCREMENT = 1001;

-- ============================================================
-- 1. SEED BOARDS (IDs 1 to 4)
-- ============================================================
INSERT INTO boards (id, slug, title, description) VALUES
(1, 'tech', 'Technology & Systems', 'Software engineering, AI architectures, Linux, and hardware.'),
(2, 'science', 'Science & Research', 'Physics, mathematics, space exploration, and breakthroughs.'),
(3, 'gaming', 'Interactive Media & Gaming', 'PC/console gaming, game design, emulation, and anime.'),
(4, 'general', 'General Discussion', 'Open discussions, Q&A, productivity, and creative projects.');

-- ============================================================
-- 2. SEED DEMO USERS (IDs 1 to 4)
--
-- Standard accounts (mod_arjun, priya_dev, rohan_sec): password123
-- Token account (Vault#6154): 4829-1049-8372-6154
--
-- The hashes below were generated with PHP password_hash() and verify
-- with password_verify(). token_lookup is HMAC-SHA256(token, APP_SECRET)
-- using the development fallback secret in config/db.php. If you set
-- THREADZERO_APP_SECRET before seeding, regenerate that value.
-- ============================================================
INSERT INTO users (id, username, password_hash, token_lookup, bio, role, created_at) VALUES
(
    1,
    'mod_arjun',
    '$2y$10$R3FmTQI.fCUx/D9yVsze5OGHvLXeKUSzMyijc/F2K0DpMmYPeC/pO',
    NULL,
    'ThreadZero System Moderator & Backend Maintainer.',
    'mod',
    '2026-10-25 10:00:00'
),
(
    2,
    'priya_dev',
    '$2y$10$R3FmTQI.fCUx/D9yVsze5OGHvLXeKUSzMyijc/F2K0DpMmYPeC/pO',
    NULL,
    'Systems programmer exploring local LLMs, C++, and Linux.',
    'user',
    '2026-10-26 14:30:00'
),
(
    3,
    'Vault#6154',
    '$2y$10$CrSuEJOhzCrHaBHBHjV/9Oxle23a6cX31g40pGC6lND7IREIF/jBi',
    'e865c13cef624fce75a64bc876a67a168c0ea743f56092fc1c5580345edf00ea',
    'Logged in via zero-knowledge 16-digit Mullvad-style token.',
    'user',
    '2026-10-27 18:15:00'
),
(
    4,
    'rohan_sec',
    '$2y$10$R3FmTQI.fCUx/D9yVsze5OGHvLXeKUSzMyijc/F2K0DpMmYPeC/pO',
    NULL,
    'Hardware tinkering, semiconductor nodes, and retro emulation.',
    'user',
    '2026-10-28 09:45:00'
);

-- ============================================================
-- 3. SEED PARENT THREADS (IDs 1001 to 1006)
-- ============================================================
INSERT INTO posts (id, board_id, user_id, parent_id, title, content, image_path, is_anon, is_pinned, is_locked, is_reported, bumped_at, created_at) VALUES
(
    1001, 1, 2, NULL,
    'Running quantized local LLMs on integrated GPUs: Vulkan vs SYCL benchmarks',
    '>be me\n>trying to run 7B and 8B GGUF models on an ultrabook iGPU\n>switch llama.cpp backend from CPU to Vulkan\n\nToken generation jumped from 6.5 t/s to 18.2 t/s immediately. Has anyone here compared SYCL against Vulkan on recent Intel Arc iGPUs? Share your prompt processing numbers below.',
    NULL, 1, 1, 0, 0,
    '2026-11-05 18:35:00', '2026-11-04 11:00:00'
),
(
    1002, 1, 3, NULL,
    'Why HMAC-SHA256 + bcrypt is mandatory for token-based authentication',
    'A lot of sites store API keys or login tokens as plain SHA-256 hashes. If the database leaks, an attacker can brute-force a 16-digit number in minutes on a modern GPU.\n\nBy combining an HMAC-SHA256 lookup column (keyed with a server-side secret) and verifying with bcrypt password_verify(), you get O(1) indexed lookups AND slow-hash brute-force protection.',
    NULL, 0, 0, 0, 0,
    '2026-11-05 17:10:00', '2026-11-04 15:20:00'
),
(
    1003, 2, 4, NULL,
    'High-NA EUV Lithography and the physics of sub-2nm gate-all-around (GAA) nanosheets',
    'As finFET hits scaling limits due to quantum tunneling and short-channel effects, RibbonFET / Gate-All-Around nanosheets are taking over.\n\n>0.55 NA optics\n>backside power delivery\n\nWhat do you think will be the biggest thermal bottleneck over the next 5 years?',
    NULL, 0, 0, 0, 0,
    '2026-11-05 16:00:00', '2026-11-04 19:00:00'
),
(
    1004, 3, 2, NULL,
    'FromSoftware boss design peak: Dark Souls 1 interconnectivity vs Dark Souls 3 combat',
    '>play Dark Souls Remastered for the first time\n>take the elevator down from Undead Parish back to Firelink Shrine\n>mind blown\n\nEven though later titles have faster boss fights, nothing beats the vertical world design of Lordran. Which ending did you pick on your first playthrough?',
    NULL, 1, 0, 0, 0,
    '2026-11-05 19:00:00', '2026-11-05 10:00:00'
),
(
    1005, 4, 1, NULL,
    'Welcome to ThreadZero — Official Community Rules & Asymmetric Anonymity Guide',
    'Welcome to ThreadZero! Please remember how our hybrid identity model works:\n\n1. You can start any thread anonymously by checking "Post Thread Anonymously".\n2. Replies inside threads show your @handle to keep discussions civil—except if you are the anonymous OP replying in your own thread (which automatically masks you as Anonymous [OP]).\n3. Use > at the start of a line for greentext and >>1001 to quote another post.',
    NULL, 0, 1, 1, 0,
    '2026-11-05 12:00:00', '2026-11-01 09:00:00'
),
(
    1006, 4, 4, NULL,
    'FREE CRYPTO AIRDROP CLICK HERE 100X GUARANTEED!!',
    'Send 0.05 BTC to this address and get 1.0 BTC back instantly! Totally legit promo link!!',
    NULL, 1, 0, 0, 1,
    '2026-11-05 14:00:00', '2026-11-05 14:00:00'
);

-- ============================================================
-- 4. SEED REPLIES (IDs 1007 to 1013)
-- Demonstrates >>quote links, >greentext, and Asymmetric OP Masking
-- ============================================================
INSERT INTO posts (id, board_id, user_id, parent_id, title, content, image_path, is_anon, is_pinned, is_locked, is_reported, bumped_at, created_at) VALUES
(
    1007, 1, 4, 1001, NULL,
    '>>1001\nVulkan is way easier to set up on Windows without installing the giant 15 GB oneAPI base toolkit, though SYCL still edges it out on prefill throughput for long context windows.',
    NULL, 0, 0, 0, 0,
    '2026-11-05 18:10:00', '2026-11-05 18:10:00'
),
(
    1008, 1, 2, 1001, NULL,
    '>>1007\nGood point! Notice how I am replying to my own anonymous thread here—ThreadZero automatically masks my handle as Anonymous [OP] so I can answer follow-ups without doxxing my account.',
    NULL, 0, 0, 0, 0,
    '2026-11-05 18:30:00', '2026-11-05 18:30:00'
),
(
    1009, 1, 1, 1002, NULL,
    '>>1002\nPinned this concept in our architecture docs as well. Combining HMAC-SHA256 for indexed lookup with bcrypt verification is the cleanest way to handle Mullvad-style account numbers.',
    NULL, 0, 0, 0, 0,
    '2026-11-05 17:10:00', '2026-11-05 17:10:00'
),
(
    1010, 2, 3, 1003, NULL,
    '>>1003\nBackside power delivery (PowerVia) helps voltage droop a lot, but concentrating heat under buried power rails makes active cooling much harder.',
    NULL, 0, 0, 0, 0,
    '2026-11-05 16:00:00', '2026-11-05 16:00:00'
),
(
    1011, 3, 3, 1004, NULL,
    '>>1004\n>walking out of the kiln after parrying Gwyn\nDark Lord ending every single time. Linking the First Flame is just prolonging a dying age.',
    NULL, 0, 0, 0, 0,
    '2026-11-05 18:45:00', '2026-11-05 18:45:00'
),
(
    1012, 3, 2, 1004, NULL,
    '>>1011\nSame! Once you survive the Bed of Chaos runback, walking away as the Dark Lord feels completely earned.',
    NULL, 0, 0, 0, 0,
    '2026-11-05 19:00:00', '2026-11-05 19:00:00'
),
(
    1013, 1, 4, 1001, NULL,
    '>>1001\nSpamming off-topic referral links in the tech thread — someone please report this reply so we can test the Moderator Queue in admin.php!',
    NULL, 0, 0, 0, 1,
    '2026-11-05 18:35:00', '2026-11-05 18:35:00'
);

-- ============================================================
-- 5. SEED SAVED BOOKMARKS
-- ============================================================
INSERT INTO bookmarks (user_id, post_id, created_at) VALUES
(1, 1001, '2026-11-05 18:40:00'),
(1, 1004, '2026-11-05 19:05:00'),
(2, 1002, '2026-11-05 17:15:00'),
(2, 1003, '2026-11-05 16:20:00'),
(3, 1001, '2026-11-05 18:50:00');
