---
name: ibmi-operational-lessons
description: "How to lint PHP on the IBM i, the LABEL ON syntax that actually sets field text, and which column types WRKDBF cannot open"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-28T15:22:25.719Z
---

Learned the hard way on 2026-08-27.

**Lint PHP on the server before every deploy, WITH THE RIGHT BINARY.** Counting braces does not
catch real syntax errors - a mangled fragment shipped a 500 to Test and Live. There is no PHP on
Bill's workstation, but the IBM i has two and they are different versions:

```
CALL QSYS2.QCMDEXC('QSH CMD(''/usr/local/zendsvr6/bin/php -d display_errors=1 -l
   ''''/HarrisData/SG5/Custom/SG/Order Entry/BuyerPattern.php'''' 1>/tmp/lint.txt 2>&1'')')
```

Then read `W:\tmp\lint.txt` - **it comes back EBCDIC**, so decode with
`[System.Text.Encoding]::GetEncoding(37)`. Without `-d display_errors=1` you only get
"Errors parsing" with no line number.

**Use `/usr/local/zendsvr6/bin/php` (PHP 5.6.23) for anything the portal serves.** The `php` on
PATH, `/QOpenSys/pkgs/bin/php`, is PHP 7.4 and is NOT what Apache runs - see
[[ibmi-web-php-is-56]]. Linting with the 7.4 binary passes PHP 7 code that then dies at runtime
with a bare 500.

**Never use perl one-liners to edit PHP.** Escaping `$` through bash into perl into PHP silently
ate variables twice in one day, once producing `<?php echo ['overdue'] > 0` and a parse error
that a grep for the new text did not reveal - so the edit looked skipped and got applied twice.
Use structured edits.

**Apache's error log did not contain the PHP fatal.** `/www/sg5eip/logs/error_log.Q1YYMMDD00`
had nothing after the last unrelated entry, so `log_errors` is not capturing PHP fatals. Do not
rely on it for diagnosis; lint instead.

**LABEL ON syntax - the two forms are not interchangeable:**
- `LABEL ON COLUMN lib.tbl.col IS 'x'`      -> sets the column **HEADING**
- `LABEL ON COLUMN lib.tbl.col TEXT IS 'x'` -> sets the column **TEXT** (what WRKDBF shows)
- `LABEL ON lib.tbl (col IS 'x')`           -> also sets the HEADING, 3 groups of 20 chars
- `LABEL ON TABLE lib.tbl IS 'x'`           -> table text

Running the first form and expecting field text wastes a round trip: it reports success and sets
the wrong attribute. **Always give a new table field text and column headings** - Bill checks
this and every Harris file has it.

**WRKDBF cannot open the contact log.** CPF5029 data-mapping error. Converting `BIGINT` to
`DECIMAL(15,0)` and every `VARCHAR` to `CHAR` did **not** fix it, so the remaining cause is
`DATE` and/or `TIMESTAMP`. Making it WRKDBF-able would mean `CHAR(26)` for the timestamp and a
CYMD `NUMERIC(7,0)` for the date - which is Harris convention, but loses DB2's
`CURRENT TIMESTAMP` default and therefore the "stamped by the IBM i, not by PHP" guarantee.
Left as SQL-only for now, by Bill's decision.

**A newly created table defaults to `*PUBLIC *EXCLUDE`** even when the library is `*PUBLIC
*CHANGE`. This breaks reads as well as writes. Grant the object explicitly. SQL0913 "file in use"
will also block `LABEL ON` while someone has the file open in WRKDBF; just retry.

See [[buyer-pattern-access-and-log]].
