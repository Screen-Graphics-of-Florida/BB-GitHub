# SYDCST / SYDSGN NUL-Byte Blank-Page Issue - Audit

**Date:** 2026-09-03
**Author:** Claude (session harrisdata), for Bill Busch
**Status:** Data defect confirmed. Root code path and blank-page mechanism NOT confirmed.
**Governing rule:** CLAUDE.md §0 (Accuracy Over Speed) - every claim below is labeled
CONFIRMED (with the evidence that confirms it) or UNVERIFIED. Nothing has been changed
in production. No fix has been applied.

---

## 1. How this surfaced

A prior Claude Code session investigated a report of blank pages ("all pages were
blank," per Bill) and reportedly traced it to a data defect. That session's transcript
was lost - the browser tab rendered white text on a white background, making the
conversation unreadable, and the finding was never written to persistent memory. Bill
recovered one paragraph of that session's conclusion by selecting the invisible text
and pasting it into a new session:

> "The page is blank off SeidenPHP because 79 XML rows in HDSSTDPGM (27 docsets in
> SYDCST, 52 page designs in SYDSGN) carry a stray NUL byte after the closing root tag.
> SeidenPHP's ibm_db2 hands that byte to PHP; ZendServer's doesn't."

This document independently re-verifies that claim against the live IBM i and records
exactly what is now confirmed, what is not, and what the fix options are.

---

## 2. What is CONFIRMED (verified directly against live DB2, 2026-09-03)

Connected via `System.Data.Odbc` to `192.168.120.40` (per CLAUDE.md §2) and queried the
tables directly.

### 2.1 Table structure

| Table | Library | Key column(s) | XML column | XML column type |
|---|---|---|---|---|
| `SYDCST` | `HDSSTDPGM` | `DSTBID` | `DSXML` | `CLOB(2097152)` |
| `SYDSGN` | `HDSSTDPGM` | `PDTBID`, `PDPGID` | `PDXML` | `CLOB(2097152)` |

`SYDCST` = 425 total rows ("docsets"). `SYDSGN` = 626 total rows ("page designs").

### 2.2 Row counts - CONFIRMED exact match to the recovered claim

```sql
SELECT COUNT(*) FROM HDSSTDPGM.SYDCST WHERE HEX(SUBSTR(DSXML, LENGTH(DSXML), 1)) = '00'
-- 27

SELECT COUNT(*) FROM HDSSTDPGM.SYDSGN WHERE HEX(SUBSTR(PDXML, LENGTH(PDXML), 1)) = '00'
-- 52
```

27 + 52 = 79, matching the recovered claim exactly.

### 2.3 Byte-level content - CONFIRMED, and more specific than the recovered claim

Pulled the last 21 bytes (as hex) of the CLOB for 10 sample rows across both tables.
The data is EBCDIC (CCSID 37), decoded below.

**`SYDCST`, 5 sample rows (`DSTBID` 101, 184, 196, 205, 209):**
```
...></link></hdDocSet>\n\x00
```
Root element is `<hdDocSet>`. The tag closes normally, followed by a line feed, then a
single trailing NUL byte (`x'00'`) appended past the end of the document.

**`SYDSGN`, 5 sample rows (`PDTBID` 446, 5, 100, 101, 138):**
```
..."/></link></hdList>\n\x00
```
Root element is `<hdList>`. Same pattern: normal close, line feed, then a trailing NUL.

All 5 sampled `SYDSGN` rows have an **identical trailing byte sequence**, despite very
different total lengths (455 to 6,604 bytes). That points to one common event - a
batch export, migration, or save-routine bug that ran once across many rows - rather
than 52 independent instances of random corruption. This was not sampled exhaustively
(only 5 of 27 and 5 of 52 rows were pulled), so it is reported as a pattern in the
sample, not a proven property of all 79 rows.

**Conclusion:** the data-side half of the recovered claim is CONFIRMED at the byte
level, not just by row count.

---

## 3. What is UNVERIFIED

### 3.1 The runtime-mismatch mechanism

The claim "SeidenPHP's `ibm_db2` hands that byte to PHP; ZendServer's doesn't" has NOT
been tested. Testing it requires running a PHP snippet that reads one of these CLOB
columns under both `/QOpenSys/pkgs/bin/php` (SeidenPHP 7.4) and
`/usr/local/zendsvr6/bin/php` (ZendServer 5.6.23) on the IBM i itself and comparing the
returned string. This session has only an ODBC path from Windows into DB2 - no shell
access to the IBM i - so this could not be attempted.

### 3.2 Which page(s) actually go blank, and how

CLAUDE.md §3 documents SeidenPHP (the 7.4 CLI build) as used for scheduled/CLI jobs
only - never in the web request path, which runs under ZendServer 5.6.23. If that's
still accurate, a portal page rendering blank in a browser should not be able to reach
the SeidenPHP interpreter at all, which sits uneasily next to the recovered claim
("blank off SeidenPHP") and Bill's statement that **all pages** were blank. Two
explanations are possible and neither is confirmed:

- Something changed since §3 was written 2026-08-28 (a scheduled job now feeds
  something the portal displays), or
- The recovered claim's attribution of "SeidenPHP" to the failure is itself imprecise,
  and the real trigger is something else that also happens to expose the same NUL-byte
  data (e.g., a ZendServer `ibm_db2` behavior under specific option flags, or a
  downstream cache/file write that a NUL byte would truncate silently at the OS level -
  C-string handling in file I/O stops at a NUL regardless of PHP version).

### 3.3 What code reads `SYDCST`/`SYDSGN`

Searched `W:\HarrisData\SG5\Custom` and the `SG` subfolder for any reference to
`SYDCST`, `SYDSGN`, `hdDocSet`, `hdList`, `DSXML`, or `PDXML`. Found nothing in the
ad hoc custom report pages (BookingsDashboard, CustServiceInquiry, KitsStructureReport,
etc.) - those are unrelated business reports. This strongly suggests `SYDCST`/`SYDSGN`
belong to a base EIP/vendor-level subsystem (a document- or page-design feature built
into the portal product itself) rather than anything in the custom `SG` codebase, but
this was not confirmed - a broader search across the rest of `W:\HarrisData` (which is
a slow network share; full-tree content search was timing out repeatedly) was started
twice via a background agent and did not complete before this document was written.
**This is the single biggest open gap**: without the actual read path, no fix can be
verified to actually resolve the blank-page symptom, only to remove clearly-anomalous
data.

### 3.4 Whether anything else about these 79 rows is damaged

Only the trailing byte was inspected. The rest of each XML document's structure
(well-formedness, whether it parses cleanly once the NUL is removed) has not been
validated.

---

## 4. Possible fixes

None of these have been applied. Listed in order of how much they depend on the open
questions in Section 3.

### Option A - Data cleanup: strip the trailing NUL byte

```sql
UPDATE HDSSTDPGM.SYDCST SET DSXML = SUBSTR(DSXML, 1, LENGTH(DSXML)-1)
WHERE HEX(SUBSTR(DSXML, LENGTH(DSXML), 1)) = '00'

UPDATE HDSSTDPGM.SYDSGN SET PDXML = SUBSTR(PDXML, 1, LENGTH(PDXML)-1)
WHERE HEX(SUBSTR(PDXML, LENGTH(PDXML), 1)) = '00'
```

- **Pros:** Small, targeted, matches the byte-level evidence exactly (removes only the
  one anomalous byte, keeps the trailing newline that looks intentional). A full backup
  of all 79 rows (every column, full CLOB content) was already taken before this
  document was written - see Section 5.
- **Cons:** `HDSSTDPGM` is the **live** program library, also shared by the `T1HDSDATA`
  test copy (per CLAUDE.md's library table) - this is a production write, not a Test
  change. It fixes the data defect confirmed in Section 2, but if the actual blank-page
  cause is something else (Section 3.2/3.3 still open), it may not resolve the symptom
  Bill is seeing, and would be applied without having reproduced the failure first.

### Option B - Code-side: make the reader tolerant of trailing NUL/whitespace

Wherever `DSXML`/`PDXML` is read and handed to an XML parser, trim trailing control
bytes before parsing (e.g. `rtrim($xml, "\x00\r\n ")` before `simplexml_load_string`/
`DOMDocument::loadXML`).

- **Pros:** Fixes the symptom regardless of which PHP build is involved, and protects
  against the same corruption recurring from whatever produced these 79 rows in the
  first place.
- **Cons:** Cannot be done until Section 3.3 is resolved - the reading code has not
  been located yet.

### Option C - Do both

Clean the existing 79 rows (Option A) and harden the reader (Option B) so future
occurrences don't reproduce the symptom. This is the durable fix if Option B's code
path is found, with Option A as an immediate data hygiene pass.

### Recommended order of work

1. Find the code that reads `SYDCST`/`SYDSGN` (resolves 3.3) - needed before any fix
   can be verified to actually address the reported symptom, and before deciding
   whether Option A alone is sufficient.
2. Reproduce the blank page against one specific known-bad row, to close 3.2.
3. Only then apply a fix, backing up per CLAUDE.md §5 immediately before executing
   (backup for the current 79 rows already exists - see Section 5 - but should be
   re-taken immediately before any actual UPDATE, in case data changes between now and
   then).

---

## 5. Backup already taken (2026-09-03, no changes made)

Full content of all 79 affected rows (every column, complete CLOB text) was dumped to:

- `SYDCST_backup_20260903_213915.txt` (27 rows)
- `SYDSGN_backup_20260903_213915.txt` (52 rows)

Location: copied into `W:\HarrisData\SG5\Custom\Backup Files\` alongside the PHP file
backups. Note that folder is documented in memory as being for PHP file backups
specifically, not DB dumps - flagging this placement for Bill to confirm or redirect,
since no rule yet exists for where database backups belong.

---

## 6. Open questions for Bill

1. Do you know what feature/subsystem `SYDCST` ("docsets") and `SYDSGN` ("page
   designs") belong to? Nothing in the custom `SG` codebase references them, which
   suggests a vendor/base-EIP feature - if you know which screen or function uses
   these, that immediately closes Section 3.3.
2. Is "all pages were blank" still happening right now, or was it transient? If it's
   reproducible, which specific URL/page, so it can be tied to a specific `DSTBID` or
   `PDTBID`?
3. Where should DB-table backups (as opposed to PHP file backups) be kept going
   forward? CLAUDE.md §5 and the existing memory note only cover file backups.
