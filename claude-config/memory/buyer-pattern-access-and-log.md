---
name: buyer-pattern-access-and-log
description: "How Buyer Pattern access control and the contact log work - UDC-driven visibility, the append-only note table, and the authority trap"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T21:22:32.392Z
---

Built 2026-08-27. Both features are configured by data, not code.

**Access control** - `PROITRG.UDCDETAIL`, system `BUYPATTERN`, code `SALESPRSN`:
- `UDCKEY` = EIP profile, `UDCDESC1..15` = the sales numbers they may see
- value `*ALL` = no filter; key `ALL_CAN_SEE` = numbers everyone sees (98, 99)
- no row = sees only 98 and 99
- 14 rows as at 2026-08-27. `BILL`, `BBUSCH`, `TIFFANY`, `DBROWNE`, `NICK` are `*ALL`.
  `USERTEST` = 64, for testing a restricted view. Bill signs in as `BILL`.
- Every comparison is upper-cased both sides - Bill asked for case-insensitive.
- Applied as `AND c.CMSLSM IN (...)` **inside the line-grain derived table**, so it reaches
  every level, chart and export with no view able to bypass it. The insert path re-checks
  independently before logging a contact.
- `PROITRG` is a single library, not split per environment, so Test and Live share these rows.

**The logged-in profile** comes from `sgAccessUser()` in `SG/SgRequireAccess.php`, which reads
`$GLOBALS['userProfile']` then `eUser` then `i5UserProfile`. Including that file is safe - it
only defines functions, enforcement is an explicit call. Web jobs run under **each user's own
profile** (job user QTMHHTTP, authorization name the real person), so the stamp is per-person.
`SYUSER.USSLSM` is 0 for all 52 users - it cannot map a profile to a sales number.

**Contact log** - `BPCALLLOG`, `SG5OBJ` on Test and `SGOBJ` on Live, chosen by `SERVER_PORT`.
Append-only: no UPDATE or DELETE code exists, and the grant is deliberately
`GRANT SELECT, INSERT ... TO PUBLIC` so the database enforces it. `CLTSTP` defaults to DB2's
`CURRENT TIMESTAMP` and is never sent from PHP; `CLUSER` comes from `sgAccessUser()`. Insert is
parameterised via `db2_prepare`/`db2_execute`, then post/redirect/get so a refresh cannot
double-log. The note - not the checkbox - is the record, so abandoning the page logs nothing;
a browser cannot be prevented from closing, and `beforeunload` is only a courtesy.
Rules: note minimum 15 characters, waived only when the outcome is `No answer`; follow-up date
or an explicit reason always required; outcomes must fit `CLOUTC CHAR(20)` (a 24-character
option was silently truncated once - there is now a startup check).

**AUTHORITY TRAP:** a newly created table defaults to `*PUBLIC *EXCLUDE`, which broke reads as
well as writes. The library was already `*PUBLIC *CHANGE`; only the object needed granting. Any
new table in `SG5OBJ`/`SGOBJ` will need the same grant, Live included.

**CMac.ws button** - `PROITRG.UDCDETAIL`, system `BPCOSEARCH`, code `CLASS`: `UDCKEY` is a
label, `UDCDESC1` the full search term, `UDCDESC2..15` the customer classes it covers, comma or
space separated. `CMCTRY = 'US'` searches `?q=<first 5 of zip>&q2=<term>`; non-US pops
"Not a US Based Business" and does not navigate; an unmapped class opens cmac.ws plain. Note
`UDCKEY` is CHAR(20) so the full term will not fit there - that is why it lives in `UDCDESC1`.
`UDCEXT1..14` are CHAR(1) flags and cannot hold text.

See [[buyer-pattern-project]] and [[harris-revenue-rules]].
