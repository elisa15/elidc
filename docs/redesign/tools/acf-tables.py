#!/usr/bin/env python3
"""Regenerate section 7 of docs/redesign/03-acf-schema.md from the ACF local JSON.

Usage (from the repo root):  python3 docs/redesign/tools/acf-tables.py
"""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
BASE = ROOT / "themes" / "elisa-dela-cruz" / "acf-json"
DOC = ROOT / "docs" / "redesign" / "03-acf-schema.md"
MARKER = "## 7. Field reference (generated)"
ORDER = [
    ("group_edc_profile", "Options page → Site Content"),
    ("group_edc_home_v2", "Options page → Site Content"),
    ("group_edc_project_fields", "Post type = project"),
    ("group_edc_testimonial_fields", "Post type = testimonial"),
    ("group_edc_page_about", "Page template = page-templates/about.php"),
    ("group_edc_page_services", "Page template = page-templates/services.php"),
    ("group_edc_page_contact", "Page template = page-templates/contact.php"),
]


def esc(s):
    return (s or "").replace("|", "\\|").replace("\n", " ")


def typ(f):
    t = f["type"]
    if t == "taxonomy":
        return f"taxonomy → `{f['taxonomy']}` (checkbox, saves terms)"
    if t == "relationship":
        return f"relationship → {', '.join(f['post_type'])} (IDs" + (f", max {f['max']}" if f.get("max") else "") + ")"
    if t == "post_object":
        return f"post object → {', '.join(f['post_type'])} (ID{'s' if f.get('multiple') else ''})"
    if t == "select":
        return "select: " + " / ".join(f"`{k}`" for k in f["choices"])
    if t == "image":
        return "image (ID)"
    if t == "file":
        return f"file ({f.get('mime_types')}, array)"
    if t == "repeater":
        return "repeater" + (f", max {f['max']}" if f.get("max") else "")
    if t == "page_link":
        return "page link (URL)"
    if t == "true_false":
        return f"true/false (default {'on' if f.get('default_value') else 'off'})"
    return t


def tables():
    out = []
    for key, loc in ORDER:
        g = json.loads((BASE / f"{key}.json").read_text())
        out.append(f"### {g['title']} — `{key}`\n\nLocation: {loc}. {esc(g.get('description'))}\n")
        out.append("| Field name | Type | Required | Default | Notes |\n|---|---|---|---|---|")

        def walk(fields, prefix=""):
            for f in fields:
                if f["type"] == "tab":
                    out.append(f"| **— {f['label']} tab —** | | | | |")
                    continue
                cond = ""
                if f.get("conditional_logic"):
                    rules = [f"`{r['field'].replace('field_edc_', '')}` {r['operator']} {r['value']}"
                             for grp in f["conditional_logic"] for r in grp]
                    cond = " Shown when " + " or ".join(rules) + "."
                d = f.get("default_value", "")
                if f["type"] == "true_false" or d in ("", None):
                    d = ""
                else:
                    s = esc(str(d))
                    d = f"`{s[:60]}{'…' if len(s) > 60 else ''}`"
                out.append(f"| {prefix}`{f['name']}` | {typ(f)} | {'yes' if f.get('required') else ''} | {d} | {esc(f.get('instructions'))}{cond} |")
                if f.get("sub_fields"):
                    walk(f["sub_fields"], prefix + "↳ ")

        walk(g["fields"])
        out.append("")
    return "\n".join(out)


def main():
    doc = DOC.read_text()
    head = doc.split(MARKER)[0]
    DOC.write_text(head + MARKER + "\n\n" + tables())
    print(f"Updated {DOC.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
