#!/usr/bin/env python3
"""Build themes/elisa-dela-cruz/assets/css/tokens.css from design/tokens.json.

design/tokens.json is the single source of truth (same file as the Claude
Design System artifact). Never edit tokens.css by hand.

Usage (from the repo root):  python3 docs/redesign/tools/build-tokens.py
"""
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
SRC = ROOT / "design" / "tokens.json"
OUT = ROOT / "themes" / "elisa-dela-cruz" / "assets" / "css" / "tokens.css"

# Fluid sizes live here because tokens.json stores the desktop maximum only.
FLUID = {
    "fs-hero-h1": "clamp(44px, 7vw, 92px)",
    "fs-contact-h2": "clamp(40px, 6.4vw, 80px)",
    "fs-page-h1": "clamp(38px, 6vw, 74px)",
    "fs-section-h2": "clamp(34px, 5vw, 56px)",
    "fs-card-h3": "clamp(21px, 2vw, 25px)",
    "fs-card-h3-lead": "clamp(24px, 2.6vw, 32px)",
    "fs-bridge": "clamp(28px, 4vw, 48px)",
    "fs-quote": "clamp(19px, 1.8vw, 22px)",
    "fs-stat": "clamp(44px, 6vw, 72px)",
    "fs-lede": "clamp(18px, 1.8vw, 21px)",
    "gutter": "clamp(16px, 4vw, 40px)",
    "section-pad": "clamp(80px, 11vw, 140px)",
    "grid-gap": "clamp(18px, 2.5vw, 28px)",
}

# Values that are not single tokens (gradients, easing) and the names the
# pre-redesign stylesheet still uses. Legacy aliases can go once style.css
# no longer references them.
COMPOSED = {
    "grad": "linear-gradient(120deg, var(--magenta) 0%, var(--violet) 55%, var(--coral) 100%)",
    "grad-btn": "linear-gradient(120deg, var(--btn-start) 0%, var(--btn-mid) 55%, var(--btn-end) 100%)",
    "ease": "cubic-bezier(.2, .7, .2, 1)",
    "dur-ui": "200ms",
    "dur-panel": "300ms",
    "dur-reveal": "450ms",
}
LEGACY = {
    "bg": "var(--night-900)",
    "bg-2": "var(--night-800)",
    "bg-3": "var(--night-700)",
    "bg-light": "var(--cream-100)",
    "card-warm": "var(--cream-50)",
    "ink": "var(--ink-900)",
    "text": "var(--paper)",
    "dim": "var(--mist)",
    "dim-2": "var(--haze)",
    "border": "rgba(255,255,255,0.09)",
    "border-2": "rgba(255,255,255,0.16)",
}

NAME_RE = re.compile(r"^[A-Za-z0-9][A-Za-z0-9_.-]{0,63}$")
ALIAS_RE = re.compile(r"^\{([A-Za-z0-9_.-]+)\}$")


def css_value(value):
    if isinstance(value, (int, float)):
        return str(value)
    m = ALIAS_RE.match(value)
    return f"var(--{m.group(1)})" if m else value


def main():
    data = json.loads(SRC.read_text())
    themes = [t["id"] for t in data["color"]["themes"]]
    first, rest = themes[0], themes[1:]
    seen = set()

    root, themed = [], {t: [] for t in rest}
    for tok in data["color"]["tokens"]:
        name = tok["name"]
        if not NAME_RE.match(name) or name in seen:
            sys.exit(f"Bad or duplicate token name: {name}")
        seen.add(name)
        val = tok["value"]
        if isinstance(val, str):
            root.append((name, css_value(val)))
        else:
            root.append((name, css_value(val.get(first) or next(iter(val.values())))))
            for t in rest:
                if t in val:
                    themed[t].append((name, css_value(val[t])))

    families = []
    for key in ("spacing", "radius", "shadow", "zIndex"):
        for tok in data.get(key, {}).get("tokens", []):
            if tok["name"] in seen:
                sys.exit(f"Duplicate token name: {tok['name']}")
            seen.add(tok["name"])
            families.append((tok["name"], css_value(tok["value"])))

    fonts = [(f"font-{k}", v) for k, v in data["type"]["families"].items()]

    lines = [
        "/* Elisa Dela Cruz — generated from design/tokens.json by docs/redesign/tools/build-tokens.py. Do not edit. */",
        "",
        ":root {",
    ]
    lines += [f"  --{n}: {v};" for n, v in root]
    lines.append("")
    lines += [f"  --{n}: {v};" for n, v in families + fonts]
    lines.append("")
    lines += [f"  --{n}: {v};" for n, v in FLUID.items()]
    lines.append("")
    lines += [f"  --{n}: {v};" for n, v in COMPOSED.items()]
    lines.append("")
    lines.append("  /* Legacy names used by the pre-redesign style.css */")
    lines += [f"  --{n}: {v};" for n, v in LEGACY.items()]
    lines.append("}")
    for t in rest:
        lines += ["", f"/* {t} sections: add class=\"light\" or data-surface=\"{t}\" to the section */",
                  f".light,\n[data-surface=\"{t}\"] {{"]
        lines += [f"  --{n}: {v};" for n, v in themed[t]]
        lines.append("}")
    lines.append("")

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text("\n".join(lines))
    print(f"Wrote {OUT.relative_to(ROOT)} ({len(seen)} tokens)")


if __name__ == "__main__":
    main()
