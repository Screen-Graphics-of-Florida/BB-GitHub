---
name: feedback-real-timestamps
description: Never invent the HHMM in a backup filename - read the actual clock
metadata:
  type: feedback
---

Backup files follow `<Name>_MMDDHHMM.php` and the `HHMM` must be the **real** time the
copy was taken. Get it from the filesystem (`stat -c '%y'`) or the system clock - never
approximate it.

**Why:** On 2026-08-24 I wrote `_08241730`, `_08241745` and `_08241915` on backups
actually created at 17:35, 18:09 and 19:19. Bill caught it. Backup names are the
timeline you reconstruct events from - when we were trying to work out whether SYPGMO
rows had been deleted that afternoon, filenames that lie about their time actively
mislead. The file contents were fine; the names were not.

**How to apply:** Create the copy first, then read its mtime and name it from that, or
rename immediately after. Same rule for backup TABLE names in SGOBJ
(`SYPORR_BK0824`-style) - if the name carries a time, it must be the real one.

Related: [[feedback-verify-before-update]]
