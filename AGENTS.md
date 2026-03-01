# AGENTS.md

## Purpose
This file defines project-level operating rules for code edits, file writes, and text handling.
Primary goal: prevent mojibake and keep text consistently UTF-8.

## Encoding Rules (Mandatory)
1. Always use UTF-8 for source files.
2. Respect `.editorconfig`:
   - `charset = utf-8`
   - `end_of_line = lf`
   - `insert_final_newline = true`
3. Do not rely on auto-detected encodings.
4. When writing files from PowerShell, always set encoding explicitly.

## PowerShell Write Rules
1. Prefer `Set-Content -Encoding utf8` (or equivalent explicit UTF-8 write).
2. For .NET write APIs, use UTF-8 explicitly:
   - `[System.IO.File]::WriteAllText(path, content, [System.Text.UTF8Encoding]::new($false))`
3. Never write files with default system encoding.

## Mojibake Prevention Checklist
Before commit:
1. Run a mojibake scan on touched files:
   - `rg -n "Ã|Ä|Å|Â|�" <paths>`
2. If scan finds matches, fix text before commit.
3. Run syntax checks for changed PHP files:
   - `php -l <file>`
4. Verify Turkish labels/UI text on pages touched in this change.

## Safe Edit Practices
1. Prefer targeted patch edits over broad replacements.
2. Avoid blind global text replacement for localized strings.
3. If a file already contains mojibake, fix all affected strings in the touched area.
4. Keep comments and UI labels human-readable and consistent.

## Commit Hygiene
1. Keep commits focused by concern (encoding fix vs feature change).
2. Mention encoding/text normalization in commit message when applicable.
3. Do not commit known mojibake.

## Notes for Future Agents
1. If mojibake appears again, first suspect write path/encoding, not HTML meta tags.
2. `header('Content-Type: text/html; charset=utf-8')` is necessary but not sufficient if source text is already corrupted.
3. Preserve UTF-8 discipline in scripts, shell commands, and editor settings.
