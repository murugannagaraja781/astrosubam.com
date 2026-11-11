# 🌟 AGENTS.md - AstroSubam Autonomous AI System & Engineering Rules

> **CRITICAL INSTRUCTION FOR ALL AI CODING AGENTS (Antigravity, Cursor, Claude Code, Windsurf, Copilot):**
> Read this file FIRST before touching or inspecting any code. This project is a **Production Live Vedic Astrology & Agora Consultation Platform** (`AstroSubam`).
> Follow these strict system laws, architecture standards, workflow maps, and automated diagnostics so that any task, bug fix, or feature request is solved with 100% precision in seconds.

---

## 🧭 1. HIGH-SPEED CODEBASE MAP (QUICK TARGET LOCATOR)

Whenever the user asks you to modify or fix a feature, go directly to the designated files:

| Feature / Domain | Primary File(s) to Edit | Supporting / Config Files | What to Watch Out For |
|---|---|---|---|
| **Web Devotee Portal** (UI, Landing, Astrologer Cards) | `index.php` | `assets/logo.jpg`, CDN CSS/JS | Always use `getApiEndpoint('/...')` to resolve paths for both local & live. |
| **Mobile App** (Flutter cross-platform) | `flutter_app/lib/main.dart` | `flutter_app/pubspec.yaml` | Maintain `AstrologerState` and `UserState` MVVM architecture. Don't add unnecessary packages. |
| **Consultation Initiation & Handshake** | `api/sessions/request.php`<br>`api/sessions/accept.php`<br>`api/sessions/user_accept.php` | `api/sessions/check_status.php`<br>`api/sessions/pending.php`<br>`api/sessions/reject.php` | Session statuses: `requested` ➡️ `ringing_user` ➡️ `active` ➡️ `completed` / `cancelled`. |
| **Real-Time 2-Way Chat** | `api/chat/send.php`<br>`api/chat/messages.php` | `index.php` (Web Modal)<br>`main.dart` (`_AstrologerActiveConsultationScreenState`) | Always poll using `last_id`. Keep message structure: `session_id`, `sender_type` ('user'\|'astrologer'), `sender_id`, `content`. |
| **Audio / Video Call & Agora RTC** | `helpers/AgoraToken.php`<br>`config/agora.php` | `api/sessions/heartbeat.php`<br>`api/sessions/end.php` | Real tokens generated per channel & UID. Heartbeat auto-bills every 60s. |
| **Wallet & Real-Time Billing** | `api/wallet/balance.php`<br>`api/wallet/recharge.php`<br>`api/sessions/heartbeat.php` | `database/schema.sql` | 5-minute minimum balance safeguard must always be enforced. |
| **Astrologer Dashboard & Queue** | `api/astrologers/dashboard.php`<br>`api/astrologers/update_status.php` | `astrologer/index.php` (Web Dashboard)<br>`main.dart` (`AstrologerDashboardScreen`) | **NO FAKE / DUMMY QUEUE DATA**. If zero pending, show empty state. |
| **Live Server Deployment** | `deploy_ftp.php` | `.env`, `.htaccess` | Runs 1-click FTP upload to Hostinger live server (`subam.navanagari.com`). |

---

## ⚡ 2. THE 5 GOLDEN RULES FOR AI AGENTS

1. **ZERO MOCK / FAKE DATA**:
   - Never inject hardcoded waiting devotees (e.g. "Karthik R. 2 mins", "Priya M. 4 mins"). All queue data MUST strictly come from `consultation_sessions` where `status = 'requested'`.
2. **STRICT INPUT COMPATIBILITY (`Response::getJsonInput()`)**:
   - All backend APIs parse request payloads using `Response::getJsonInput()`. This supports both `application/json` raw body and `multipart/form-data` / `application/x-www-form-urlencoded`. Always return response via `Response::json($ok, $msg, $data, $statusCode)`.
3. **HTTPS / SCHEME INTEGRITY**:
   - Production server forces HTTPS (`https://subam.navanagari.com`). When calling internal APIs from Flutter, use `https://subam.navanagari.com/api` to prevent 301 redirects that drop POST payload bodies.
4. **SAFE DUAL-DATABASE COMPATIBILITY**:
   - `config/database.php` auto-detects MySQL (Production) and SQLite (Local development). Always use standard SQL (`PDO::prepare`) and check driver if using driver-specific date functions (`CURRENT_TIMESTAMP` vs `datetime('now')`).
5. **VERIFY BEFORE DECLARING DONE**:
   - Never say a feature is fixed without executing:
     - `php test_quick_check.php` (Backend API sanity)
     - `cd flutter_app ; flutter test` (Mobile App unit tests)

---

## 🛠️ 3. ONE-COMMAND AUTOMATED WORKFLOWS

### A. Deploy to Live Server (Hostinger):
```bash
php deploy_ftp.php
```

### B. Run Full Test Suite:
```bash
# 1. Flutter UI & Unit Tests
cd flutter_app && flutter test

# 2. End-to-End API Workflow Test
php tests/test_workflow.php
```

### C. Build Fresh Production APK:
```bash
cd flutter_app && flutter build apk --release
copy build\app\outputs\flutter-apk\app-release.apk ..\AstroSubam.apk
cd ..
php deploy_ftp.php
```

---

## 👥 4. TEST ACCOUNTS FOR INSTANT VERIFICATION

- **Astrologer 1 (Primary)**: Phone `9876543210` • ID: `1` • Name: `Guruji S. Sundaramurthy` (Audio: ₹20/m, Video: ₹35/m, Chat: ₹12/m)
- **Astrologer 2**: Phone `9876543211` • ID: `2` • Name: `Dr. K. Jayalakshmi` (Audio: ₹18/m, Video: ₹30/m, Chat: ₹10/m)
- **Astrologer 3**: Phone `9876543212` • ID: `3` • Name: `Acharya Ramanan Sarma` (Audio: ₹25/m, Video: ₹40/m, Chat: ₹15/m)
- **Devotee (Test User)**: User ID: `2` • Phone: `9876543299` • Initial Wallet Balance: ₹500.00
