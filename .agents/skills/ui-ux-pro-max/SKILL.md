---
name: ui-ux-pro-max
description: World-class UI/UX design expert for creating visually stunning, modern, and high-converting Web and Flutter applications. Enforces rich aesthetics, glassmorphism, dark luxury themes, micro-animations, and production-grade responsive design systems.
---

# 🎨 UI/UX Pro Max - Advanced Design System & Mobile App UI Patterns

Welcome to the **UI/UX Pro Max** skill for Antigravity, upgraded with the **UI Skills Mobile App Design Playbook** (https://www.ui-skills.com/collections/mobile-app-design). This skill equips the agent with industry-leading frontend aesthetics, mobile interaction patterns, and ergonomics.

---

## 🌟 1. Core Visual Principles: The "WOW" Factor

1. **Sacred Dark Luxury & Anti-Generic Palette**:
   - Never use default raw primaries (`#FF0000`, `#0000FF`, `#00FF00`).
   - Use curated luxury palettes: Deep cosmic obsidian (`#080A12`, `#0D111E`), royal golds (`#FFD700`, `#F59E0B`, `#E5B842`), spiritual emeralds (`#059669`, `#10B981`), and celestial ruby accents (`#EF4444`).
2. **Layering & Depth (Glassmorphism & Multi-Stop Shadows)**:
   - Elevated glass surfaces: `backdrop-filter: blur(16px)` + translucent borders (`Border.all(color: Colors.white.withAlpha(25))`).
   - Multi-stop ambient shadows: `box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5), 0 0 20px 0 rgba(245, 158, 11, 0.08)`.
3. **Typography Mastery & Hierarchy**:
   - Primary: High-legibility modern sans-serifs (**Inter**, **Plus Jakarta Sans**, **Outfit**, **Roboto**).
   - Display/Hero: Elegant serif or stylized headers (**Cinzel**, **Playfair Display**) for astrology, sacred heritage, and luxury branding.
   - Maintain strict typographic scales (11px micro, 13px caption, 15px body, 18px subtitle, 22px title, 28px headline, 36px display).

---

## 📱 2. UI Skills Mobile App Design Patterns (Playbook Integration)

Based on [UI Skills Mobile App Design Collection](https://www.ui-skills.com/collections/mobile-app-design):

### A. 48px / 44px Minimum Touch Targets (`use-large-touch-targets`)
- Every interactive element (buttons, icon taps, pills, toggle switches) must have a touch target of at least **48x48 dp** on mobile (iOS minimum 44x44 pt).
- Use `IconButton` with `constraints: BoxConstraints(minWidth: 48, minHeight: 48)` or wrap smaller visuals in padding to expand hit area.

### B. Inset Primary Actions (`inset-primary-actions`)
- Never stretch CTAs to the raw screen edges without margins.
- Full-width action buttons must remain inset inside page margins (minimum 16dp horizontal padding) with generous corner radii (16dp-20dp) to look polished and float above backgrounds.
- Always respect `SafeArea` bottom padding (`max(16, safeArea.bottom)`).

### C. Breathing Room Between Controls (`give-targets-breathing-room`)
- Maintain at least **8dp to 12dp** gap between adjacent interactive targets (e.g. Call, Chat, Video, and Profile buttons) to eliminate accidental misclicks.

### D. High-Contrast Readable Secondary Text (`keep-secondary-text-readable`)
- Avoid muddy grey text on dark backgrounds.
- Ensure WCAG AA compliance (contrast ratio ≥ 4.5:1 for body and ≥ 3.0:1 for headers).
- On cosmic dark surfaces (`#080A12`), use `Colors.white.withAlpha(180)` or `#94A3B8` for secondary captions, never low-opacity grays below 60%.

### E. Structural Shimmer Skeletons (`use-structural-skeletons`)
- Discard plain circular spinners for full-screen loading.
- Render shimmering placeholder cards matching the exact layout of astrologer cards, wallet balances, and message bubbles while data loads.

### F. Tabular Numbers for Time & Money (`use-tabular-nums-for-data`)
- Monetary amounts (₹500.00, ₹20/min) and consultation timers (`00:00`) must use monospace/tabular figure styles (`fontFeatures: [FontFeature.tabularFigures()]` or `GoogleFonts.jetBrainsMono()`) to prevent jarring layout twitching as numbers tick.

### G. Thumb Zone Optimization & Bottom CTAs
- Place primary conversion actions (e.g., "Consult Now", "Add Money", "Send Message") within the natural bottom 35% thumb sweep area.
- Use floating bottom sheets with drag handles (`Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2)))`) for filters and detail views.

### H. 100ms Micro-Interactions & Spring Feedback
- Every card tap or CTA button press must scale slightly (`0.97`–`0.98`) using `AnimatedScale` or `InkWell` splash for tactile satisfaction.
- Status badges (Online / Busy / Offline) must include glowing subtle pulse rings.

---

## 🛠️ 3. Reusable Flutter UI Component Templates

### 🌟 Glassmorphic Astrologer Consultation Card
```dart
Widget buildAstrologerCard({
  required String name,
  required String photoUrl,
  required String specialities,
  required double rating,
  required int reviews,
  required int ratePerMin,
  required String status,
  required VoidCallback onTap,
}) {
  final isOnline = status.toLowerCase() == 'online';
  return Container(
    margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
    decoration: BoxDecoration(
      borderRadius: BorderRadius.circular(22),
      gradient: LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [
          const Color(0xFF131A26).withAlpha(220),
          const Color(0xFF0C101A).withAlpha(240),
        ],
      ),
      border: Border.all(
        color: isOnline
            ? const Color(0xFFD4AF37).withAlpha(80)
            : Colors.white.withAlpha(20),
        width: 1.2,
      ),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withAlpha(120),
          blurRadius: 18,
          offset: const Offset(0, 8),
        ),
      ],
    ),
    child: Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(22),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Avatar with Online indicator badge
              Stack(
                children: [
                  CircleAvatar(
                    radius: 34,
                    backgroundImage: NetworkImage(photoUrl),
                  ),
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: Container(
                      width: 16,
                      height: 16,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: isOnline ? const Color(0xFF10B981) : Colors.grey,
                        border: Border.all(color: const Color(0xFF0C101A), width: 2.5),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 16),
              // Details
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(
                        fontFamily: 'Cinzel',
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFFFDE68A),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      specialities,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.white.withAlpha(180),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 16),
                        const SizedBox(width: 4),
                        Text(
                          '$rating ($reviews)',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                        const Spacer(),
                        Text(
                          '₹$ratePerMin/min',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF34D399),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    ),
  );
}
```

---

## ⚡ 4. Verification & Quality Checklist
Before finishing any mobile UI task:
- [ ] Are all touch targets ≥ 48x48 dp?
- [ ] Are bottom action buttons inset with safe area margins?
- [ ] Is secondary text readable (contrast ratio ≥ 4.5:1)?
- [ ] Are timers and currency figures using tabular / monospace digits?
- [ ] Does screen have smooth loading skeletons instead of blank spaces?
- [ ] Does every action provide immediate tactile feedback within 100ms?
