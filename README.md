# 🌟 ASTROSUBAM (Vedic Astrology & Agora Consultation Platform)

## 📌 Architecture Overview
- **Backend:** PHP 8.x + MySQL (PDO, Strict JSON, JWT Authentication, Agora RTC Token Builder)
- **Frontend:** Flutter (Single cross-platform codebase: Flutter Mobile [Android/iOS] + Flutter Web)
- **Realtime Audio & Video Call:** Agora RTC Engine with dynamic token generation & 60-second billing heartbeat
- **Live Chat:** Agora Real-time Chat with astrologer messaging & attachments
- **Wallet Engine:** Auto-deduct per-minute consultation charges with real-time balance exhaustion safeguards

---

## 🚀 Quick Start Guide

### 1. Database Setup (MySQL)
1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. In your browser or command line, open:
   ```
   http://localhost/astrosubam/database/install.php
   ```
3. This creates the `astrosubam` database and seeds sample astrologers and a demo test account (with ₹500 balance).

### 2. Agora Configuration
Edit `config/agora.php` to enter your Agora App credentials:
```php
return [
    'app_id'          => 'YOUR_AGORA_APP_ID',
    'app_certificate' => 'YOUR_AGORA_APP_CERTIFICATE',
    'token_expire_seconds' => 3600,
];
```

### 3. Running the Flutter App (Web & Mobile)
Navigate to the Flutter directory:
```bash
cd C:\xampp\htdocs\astrosubam\flutter_app
```

- **Run on Chrome (Web):**
  ```bash
  flutter run -d chrome
  ```

- **Run on Android Device / Emulator:**
  ```bash
  flutter run -d android
  ```

---

## 📡 API Endpoints Reference
| Endpoint | Method | Description |
|---|---|---|
| `/api/auth/register.php` | POST | Client registration + ₹100 welcome bonus |
| `/api/auth/login.php` | POST | Secure login returning JWT token |
| `/api/astrologers/list.php` | GET | Filter astrologers by availability, rating, language |
| `/api/astrologers/detail.php` | GET | Astrologer profile & rate cards |
| `/api/sessions/initiate.php` | POST | Verify wallet balance, generate Agora channel & token |
| `/api/sessions/heartbeat.php` | POST | 60-second live wallet deduction ticker |
| `/api/sessions/end.php` | POST | Complete consultation & update astrologer earnings |
| `/api/wallet/balance.php` | GET | User balance and transaction logs |
| `/api/wallet/recharge.php` | POST | Instant wallet recharge |
| `/api/chat/send.php` | POST | Send in-consultation chat message |
| `/api/chat/messages.php` | GET | Retrieve session chat history |

---
**Safety Note:** Astrohark codebase is completely independent and untouched.
