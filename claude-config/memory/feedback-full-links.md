---
name: feedback-full-links
description: Always give Bill the complete clickable URL, never a page name or partial path
metadata:
  type: feedback
---

Whenever a step involves opening a page, give the **full URL**, including protocol, host,
port and any query parameters that pre-select what he needs. Never write "open
SgProgramAccess.php and search for X" - write the link that lands on X.

    https://portal.screen-graphics.com:5610/Custom/SG/SgProgramAccess.php?pgm=SGPORTROLE

**Why:** Bill's instruction, 2026-08-25: "always give me the full link." Naming a file
makes him construct the URL himself, and I have got the path wrong before
(sg_pgmsec_diag.php sits in Custom/ not Custom/SG/, which produced a "No input file
specified" error and a wasted round trip).

**How to apply:** Port 5610 = SG5 Test, 5601 = EIP Live. Custom pages are under
/Custom/SG/<subpath>.php, but check the actual location on W: before writing the link -
some files sit directly in /Custom/. Give both environment links when a step applies to
both, and add query parameters (?pgm=, ?role=) so the page opens on the right record.

Related: [[feedback-caveats-before-code]]
