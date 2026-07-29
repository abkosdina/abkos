from pathlib import Path
import shutil

root = Path(r"C:\xampp\htdocs\dinashash")
docs = root / "docs"
for folder in [docs / "overview", docs / "workflow", docs / "database", docs / "implementation", docs / "maintenance", docs / "modules", docs / "architecture"]:
    folder.mkdir(parents=True, exist_ok=True)


def classify(path: Path) -> Path:
    name = path.name.lower()
    parent_parts = path.parts

    if path.is_relative_to(root / "docs") and path.parent != docs:
        return path

    if path.parent == docs:
        if name == "graph.md":
            return docs / "overview"
        if "workflow" in name or "approval" in name:
            return docs / "workflow"
        if "database" in name or "schema" in name or "migration" in name or "mysql" in name:
            return docs / "database"
        if "setup" in name or "jalali" in name or "contact" in name or "view" in name or "implementation" in name or "guide" in name or "summary" in name:
            return docs / "implementation"
        return docs / "overview"

    if "Modules" in parent_parts:
        module = parent_parts[parent_parts.index("Modules") + 1] if "Modules" in parent_parts and parent_parts.index("Modules") + 1 < len(parent_parts) else "module"
        return docs / "modules" / module

    upper = path.name.upper()
    if name == "ai.md":
        return docs / "overview"
    if "WORKFLOW" in upper or "APPROVAL" in upper:
        return docs / "workflow"
    if "DATABASE" in upper or "SCHEMA" in upper or "MIGRATION" in upper:
        return docs / "database"
    if "IMPLEMENTATION" in upper or "PLAN" in upper or "SUMMARY" in upper or "GUIDE" in upper:
        return docs / "implementation"
    if "DOCUMENTATION" in upper or "AUDIT" in upper or "CLEANUP" in upper or "LEGACY" in upper:
        return docs / "maintenance"
    return docs / "overview"


files_to_move = []
for path in list(root.glob("*.md")):
    if path.name.lower() == "readme.md":
        continue
    files_to_move.append(path)

for path in list(docs.glob("*.md")):
    if path.name.lower() == "readme.md":
        continue
    files_to_move.append(path)

for path in root.glob("Modules/**/*.md"):
    files_to_move.append(path)

seen = set()
for src in sorted(files_to_move):
    if not src.exists():
        continue
    rel = src.relative_to(root)
    if rel.parts and rel.parts[0] == "docs" and len(rel.parts) > 1:
        continue
    key = str(src.resolve())
    if key in seen:
        continue
    seen.add(key)

    dest_dir = classify(src)
    dest = dest_dir / src.name
    if dest.exists():
        stem = dest.stem
        counter = 1
        while True:
            candidate = dest_dir / f"{stem}-{counter}{dest.suffix}"
            if not candidate.exists():
                dest = candidate
                break
            counter += 1

    dest.parent.mkdir(parents=True, exist_ok=True)
    if src != dest:
        shutil.move(str(src), str(dest))

index = docs / "README.md"
index.write_text(
    "# Documentation Hub\n\n"
    "This folder now contains the project documentation in a structured layout.\n\n"
    "## Structure\n\n"
    "- overview/: general project and onboarding documents\n"
    "- workflow/: workflow and approval engine documents\n"
    "- database/: schema, migration, and database audit documents\n"
    "- implementation/: implementation plans, summaries, and guides\n"
    "- maintenance/: audits, cleanup, and legacy notes\n"
    "- modules/: module-specific documentation\n"
    "- architecture/: architecture references and module maps\n",
    encoding="utf-8"
)

print("Reorganized markdown files into docs structure.")
