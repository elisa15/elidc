# design/

`tokens.json` is the single source of truth for the theme's design tokens (colour, type, spacing, radius, shadow, z-index). It uses the same format as the "Elisa Dela Cruz Design System" artifact, so the two can be kept in sync by copying the file.

After editing it, regenerate the theme stylesheet from the repo root:

```bash
python3 docs/redesign/tools/build-tokens.py
```

This writes `themes/elisa-dela-cruz/assets/css/tokens.css`. Usage rules for every token are in `docs/redesign/02-design-system.md`.
