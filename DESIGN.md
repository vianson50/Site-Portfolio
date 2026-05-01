---
name: Ivorian Cyber-Professional
colors:
  surface: '#1c110a'
  surface-dim: '#1c110a'
  surface-bright: '#44362e'
  surface-container-lowest: '#160c06'
  surface-container-low: '#241912'
  surface-container: '#291d15'
  surface-container-high: '#34271f'
  surface-container-highest: '#3f322a'
  on-surface: '#f4ded2'
  on-surface-variant: '#dec1af'
  inverse-surface: '#f4ded2'
  inverse-on-surface: '#3b2e25'
  outline: '#a68b7b'
  outline-variant: '#574235'
  surface-tint: '#ffb785'
  primary: '#ffb785'
  on-primary: '#502400'
  primary-container: '#ff8200'
  on-primary-container: '#5f2c00'
  inverse-primary: '#954a00'
  secondary: '#61dd98'
  on-secondary: '#00391f'
  secondary-container: '#17a566'
  on-secondary-container: '#00311a'
  tertiary: '#c6c6c7'
  on-tertiary: '#2f3131'
  tertiary-container: '#a3a4a4'
  on-tertiary-container: '#383a3b'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffdcc6'
  primary-fixed-dim: '#ffb785'
  on-primary-fixed: '#301400'
  on-primary-fixed-variant: '#723700'
  secondary-fixed: '#7efab3'
  secondary-fixed-dim: '#61dd98'
  on-secondary-fixed: '#002110'
  on-secondary-fixed-variant: '#00522f'
  tertiary-fixed: '#e2e2e2'
  tertiary-fixed-dim: '#c6c6c7'
  on-tertiary-fixed: '#1a1c1c'
  on-tertiary-fixed-variant: '#454747'
  background: '#1c110a'
  on-background: '#f4ded2'
  surface-variant: '#3f322a'
typography:
  h1:
    fontFamily: Space Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  h2:
    fontFamily: Space Grotesk
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: '0'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  code-sm:
    fontFamily: Space Grotesk
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.4'
    letterSpacing: 0.05em
  label-caps:
    fontFamily: Space Grotesk
    fontSize: 12px
    fontWeight: '700'
    lineHeight: '1'
    letterSpacing: 0.1em
spacing:
  unit: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 48px
  container-margin: 20px
  gutter: 12px
---

## Brand & Style

The design system establishes a high-performance visual language that bridges the gap between professional software engineering and underground hacker culture. It is built for a multi-disciplinary creator who identifies as a developer, designer, and gamer. 

The aesthetic is a hybrid of **Minimalist Cyberpunk** and **Professional Brutalism**. It utilizes deep, dark canvases to make the vibrant Côte d'Ivoire national colors pulsate with energy. The emotional response is one of technical mastery, precision, and cultural pride. By combining high-density data displays with generous whitespace and bold editorial typography, this design system signals both creative flair and engineering rigor.

## Colors

The palette is rooted in the national colors of Côte d'Ivoire, reimagined for a digital "hacker" context. 

- **Vibrant Orange (#FF8200):** Used for primary actions, critical syntax highlighting, and "active" states. It serves as the high-energy focal point.
- **Deep Forest Green (#009E60):** Utilized for success states, terminal-style text, and secondary accents. It provides a grounding contrast to the orange.
- **Pure White (#FFFFFF):** Reserved for primary content, ensuring maximum legibility against the dark background.
- **The Void (#0A0A0B):** The foundation. A near-black neutral that provides the necessary depth for glowing elements and sharp typography to thrive.

Color application should follow a 60-30-10 rule, with the dark neutral dominating, white handling the content, and the Orange/Green pair providing the "cyber" highlights.

## Typography

Typography is used to distinguish between "Human" content and "Machine" data. 

- **Headlines:** Space Grotesk is used for its technical, geometric edge. Headlines should be tight and impactful.
- **Body:** Inter provides a clean, neutral balance for long-form reading, ensuring professional readability.
- **Monospace/Data:** Space Grotesk is also used for labels and code snippets to maintain a cohesive "tech" look. Use uppercase for labels and tags to lean into the brutalist influence.

## Layout & Spacing

This design system employs a **Fluid Grid** model optimized for mobile-first interaction. 

- **Grid:** A 4-column grid for mobile devices with a 12px gutter.
- **Rhythm:** A 4px baseline grid ensures vertical consistency.
- **Philosophy:** Use generous `xl` padding (48px) to separate major sections, creating a "breathable" high-end feel. However, internal component spacing should be tight (`sm` or `md`) to mimic the density of a software IDE or a gaming HUD.

## Elevation & Depth

Depth is conveyed through **Tonal Layers** and **Glow Effects** rather than traditional shadows.

1.  **Base Layer:** The deepest neutral (#0A0A0B).
2.  **Surface Layer:** A slightly lighter grey (#161618) used for cards and containers.
3.  **Borders:** Instead of heavy shadows, use 1px solid borders in a low-opacity white (10%) or the primary orange/green to define boundaries.
4.  **The "Glow":** High-priority interactive elements (like an active CTA) should use a subtle outer glow (box-shadow) using the primary orange with high blur and low opacity to simulate a terminal screen or neon light.

## Shapes

The shape language is strictly **Sharp (0)**. 

To reinforce the hacker/professional aesthetic, all buttons, cards, and input fields must have square corners. This creates a rigid, architectural look that feels engineered rather than "molded." Avoid all circular or rounded treatments except for user avatars, which should remain square or hexagonal to fit the technical theme.

## Components

- **Buttons:** Primary buttons are solid Orange (#FF8200) with Black text. Secondary buttons are outlined in Green (#009E60) with Green text. All buttons use the `label-caps` typography style.
- **Cards:** Use the "Surface Layer" background with a 1px top border in Green or Orange to categorize content (e.g., Orange for "Development", Green for "Design").
- **Chips/Tags:** Small, square-bordered boxes with a low-opacity background of the accent color and a high-opacity text color.
- **Input Fields:** Bottom-border only or full-outline with 1px thickness. When focused, the border glows in the primary Orange.
- **Code Blocks:** Deep black background with a subtle "scanline" pattern overlay. Use Green text for a classic terminal feel.
- **Progress Bars:** Thin, 2px lines. The "filled" portion should have a slight glow effect to mimic a loading sequence in a game.
- **Navigation:** A fixed bottom-bar using icons with label-caps text. The active state is indicated by an Orange top-line on the nav item.