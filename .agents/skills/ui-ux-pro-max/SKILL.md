---
name: ui-ux-pro-max
description: World-class UI/UX design expert for creating visually stunning, modern, and high-converting Web and Flutter applications. Enforces rich aesthetics, glassmorphism, dark luxury themes, micro-animations, and production-grade responsive design systems.
---

# 🎨 UI/UX Pro Max - Advanced Design System & Frontend Guidelines

Welcome to the **UI/UX Pro Max** skill for Antigravity. This skill equips the AI assistant with top-tier frontend aesthetic and interaction design capabilities.

---

## 🌟 1. Core Visual Principles: The "WOW" Factor

1. **Anti-Generic Aesthetic**:
   - Never use default plain primary colors (`#FF0000`, `#0000FF`, `#00FF00`).
   - Use curated luxury palettes: Deep cosmic darks (`#090A15`, `#0F1226`), royal golds (`#FFD700`, `#F59E0B`), electric accents (`#6366F1`, `#EC4899`), and translucent frosted glass.
2. **Layering & Depth (Glassmorphism & Shadows)**:
   - Use multi-layer elevation rather than flat boxes:
     - `backdrop-filter: blur(16px)` + `background: rgba(255, 255, 255, 0.05)`
     - Soft multi-stop ambient box shadows (`0 8px 32px 0 rgba(0, 0, 0, 0.37)`).
3. **Typography Mastery**:
   - Primary: High-legibility modern sans-serifs (**Inter**, **Outfit**, **Plus Jakarta Sans**, **Roboto**).
   - Display/Hero: Elegant serif or stylized headers (**Cinzel**, **Playfair Display**, **Syne**) for astrology, luxury, and premium brands.
   - Maintain strict typographic scales (12px, 14px, 16px, 20px, 24px, 32px, 48px, 64px).

---

## 📱 2. Flutter UI Patterns & Best Practices

When building or refining Flutter apps:
- **Responsive Layout**: Use `LayoutBuilder`, `MediaQuery`, and clamp widths (`BoxConstraints(maxWidth: 600)`) for desktop/tablet responsiveness.
- **Glassmorphic Cards**:
  ```dart
  ClipRRect(
    borderRadius: BorderRadius.circular(20),
    child: BackdropFilter(
      filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white.withAlpha(15),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.white.withAlpha(30)),
        ),
        padding: const EdgeInsets.all(20),
        child: content,
      ),
    ),
  );
  ```
- **Micro-Interactions**:
  - Animated buttons using `AnimatedScale` or `AnimatedContainer` on hover/tap.
  - Smooth page transitions using custom route transitions or `Hero` widgets.
  - Shimmer skeletons instead of plain circular progress spinners.

---

## 🌐 3. Web & CSS Design System Tokens

When styling Web pages:
```css
:root {
  --bg-cosmic: #090a15;
  --bg-card: rgba(20, 23, 44, 0.7);
  --border-glass: rgba(255, 255, 255, 0.12);
  --accent-gold: #ffd700;
  --accent-amber: #f59e0b;
  --text-primary: #ffffff;
  --text-muted: #94a3b8;
  --radius-xl: 18px;
  --shadow-glow: 0 0 25px rgba(255, 215, 0, 0.2);
}
```

---

## 🚀 4. Interaction & Feedback Rules
- **Feedback within 100ms**: Every button press, tap, or hover must provide immediate visual feedback (glow, slight scale down `0.98`, or color transition).
- **Graceful Loading**: Never leave empty screens; always use animated shimmer placeholders with shimmering linear gradients.
- **Accessible Contrast**: Ensure WCAG AA compliance (4.5:1 contrast ratio for body text, 3:1 for large headers).
