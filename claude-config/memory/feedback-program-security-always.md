---
name: feedback-program-security-always
description: Every custom SG page must be registered in SYPGMO and gated with sgRequireAccess so it is grantable per user in SgProgramAccess.php
metadata: 
  node_type: memory
  type: feedback
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-27T22:40:01.971Z
---

**Every page we build goes into program security. No exceptions.** Bill said this on
2026-08-27, emphatically, after noticing Buyer Pattern had been built without it.

Three things are required for a new custom page:

1. **Register it in `SYPGMO`** - `SG5STDPGM.SYPGMO` on Test, `HDSSTDPGM.SYPGMO` on Live. Never
   in the data schemas. `SOMDES` gets a real description, never `'View'` - see
   [[feedback-sypgmo-descriptions]].
2. **Call the gate** near the top of the page, before any output:
   ```php
   require_once dirname(__FILE__) . '/../SgRequireAccess.php';
   sgRequireAccess('PGMID');       // 10-char SYPGMO program id
   ```
3. **Grant users** through `SgProgramAccess.php`, which maintains `SYPGMS`
   (`SPUSER`, `SPPGID`, `SPOP01..15`) in the environment's data schema, resolved by
   `sgAccessSchema()` off `SERVER_PORT`.

**Why:** the EIP portal only controls whether the menu link appears; it does not control who may
open the page. On Live, `SGINQ` alone reaches 41 users across 15 roles, so without a SYPGMS gate
any page added to that portal is effectively open to all of them. Program security is the layer
that answers "may you open this at all", and it is separate from any row-level filtering the
page does internally.

**How to apply, without causing an outage:** a user with no `SYPGMS` row is denied, so enforcing
before anyone is granted locks out everybody including Bill. The safe pattern, used in
`BuyerPattern.php`, is to count the program's `SYPGMS` rows first and only call
`sgRequireAccess()` once at least one exists - registering therefore cannot lock people out, and
enforcement begins automatically on the first grant. Show a badge saying which state the page is
in. This also satisfies [[feedback-tick-before-promote]]: list who would be denied and let Bill
grant them before enforcement bites.

Buyer Pattern's program id is `BUYPATTERN` (exactly 10 characters). Its row-level layer is the
`BUYPATTERN`/`SALESPRSN` UDC table - see [[buyer-pattern-access-and-log]].
