---
name: kubica-frontend-design
description: Guides distinctive, high-craft Web UI development for Kubica Hub. Enforces tactile controls, anti-default design choices, data-lean performance, and responsive 1200px layouts. Use when building React/HTML/CSS interfaces.
---

# Kubica Frontend Design System

## Core Layout Patterns
- **Page Max-Width:** 1200px centered container with 52px vertical section gaps[cite: 4].
- **Asymmetric Split Hero (55/45):** Left 55% with editorial display copy + Amber Filled Pill CTA; Right 45% with rich portrait photography featuring a subtle Espresso gradient scrim[cite: 4].
- **Faculty 4-Column Grid:** Structured cards with 1px Walnut bottom borders, 40px circular icon slots, and tracked C-Level uppercase badges[cite: 4].

## Anti-Default Guidelines (Avoiding Generic AI Look)
- **Do not output generic blue SaaS cards or purple gradients.**
- **Do not use standard 8px/12px card border-radii everywhere.** Maintain the strict contrast: `9999px` for pill interactive elements vs. `6px` for structural content boxes[cite: 4].
- **Typography must be tightly tracked on headlines:** Apply negative tracking (`-0.30px` to `-1.13px`) for display sizes[cite: 4].
- **Copywriting from the student's perspective:** Use active, dignified verbs ("Submeter Ideia", "Conectar Co-Founder", "Assinar Acordo de Vesting")[cite: 4].

## CSS Reference Tokens
```css
:root {
  --color-espresso: #140b00;
  --color-midnight-cocoa: #0b0600;
  --color-warm-cream: #fff1e0;
  --color-walnut: #43392d;
  --color-cedar: #4f4538;
  --color-driftwood: #85796c;
  --color-amber-forge: #ffb442;
  --font-sans: 'PP Neue Montreal', 'Inter', sans-serif;
  --radius-pill: 9999px;
  --radius-card: 6px;
  --spacing-section: 52px;
}