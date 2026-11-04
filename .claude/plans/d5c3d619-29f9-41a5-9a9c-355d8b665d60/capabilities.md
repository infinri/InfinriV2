# Capabilities: script that strips license/author docblocks from PHP files

- [ ] A docblock containing any one of @package, @author, @link, @copyright or @license is removed from the file
- [ ] A docblock containing none of those tags is left byte-for-byte unchanged
- [ ] A removed docblock that sits alone on its lines leaves no leftover indentation or empty line
- [ ] Tag text inside a string literal or a plain /* */ comment is not touched
- [ ] Files under a vendor/ or dependencies/ path are skipped
- [ ] A file with no matching docblock is not rewritten and is not listed in the output
- [ ] A single file path argument is processed the same as a directory argument
- [ ] Output lists each changed file and ends with the count of updated files
- [ ] (operational) After running on src/, every changed file passes php -l
