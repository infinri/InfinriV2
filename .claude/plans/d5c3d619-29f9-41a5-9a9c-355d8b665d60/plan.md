# Plan: script that strips license/author docblocks from PHP files

The user asked for a script that removes every docblock containing @package, @author, @link, @copyright or @license. They approved removing the whole docblock (not just the tag lines) and leaving third-party code alone.

## Files

- `scripts/strip-docblocks.php` (create) -- CLI script that finds matching docblocks with the PHP tokenizer and deletes them in place
- `tests/Scripts/StripDocblocksTest.php` (create) -- runs the script against temp fixture files and pins the behaviors listed under ## Capabilities

## Analysis

Usage: `php scripts/strip-docblocks.php [path ...]`, where each path is a file or directory; with no arguments it processes `src/`. Directories are walked recursively for `*.php`; any path containing `/vendor/` or `/dependencies/` is skipped so third-party license headers stay.

Detection uses `token_get_all()` and only considers `T_DOC_COMMENT` tokens, so `/** ... */` text inside strings and plain `/* */` or `//` comments are never touched. A docblock is removed when it matches `/@(package|author|link|copyright|license)\b/`. Byte offsets are accumulated from token lengths; ranges are removed back to front so earlier offsets stay valid. When the docblock is alone on its line(s), its leading indentation and the trailing line break (LF or CRLF) are removed too, so no blank stub line is left; otherwise only the comment itself is removed.

The script prints each changed file path and a final `N file(s) updated` line. Unchanged files are not rewritten. Out of scope: tag-only removal, dry-run mode, reformatting surrounding blank lines.

## Rules Applied

- **SKL-PROC-BRAIN-001** -- whole-block vs tag-only removal and third-party exclusion were put to the user before any code; they approved whole-block removal.
- **PBK-PROC-TDD-001** -- the test file is written first and must fail before the script exists.
- **SKL-PROC-VERIFY-001** -- completion is claimed only after running PHPUnit and `php -l` on the changed files in the same message.

## Capabilities

- [ ] A docblock containing any one of @package, @author, @link, @copyright or @license is removed from the file
- [ ] A docblock containing none of those tags is left byte-for-byte unchanged
- [ ] A removed docblock that sits alone on its lines leaves no leftover indentation or empty line
- [ ] Tag text inside a string literal or a plain /* */ comment is not touched
- [ ] Files under a vendor/ or dependencies/ path are skipped
- [ ] A file with no matching docblock is not rewritten and is not listed in the output
- [ ] A single file path argument is processed the same as a directory argument
- [ ] Output lists each changed file and ends with the count of updated files
- [ ] (operational) After running on src/, every changed file passes php -l
