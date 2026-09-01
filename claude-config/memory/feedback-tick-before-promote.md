---
name: feedback-tick-before-promote
description: Always show who loses access and let Bill grant them BEFORE promoting any access enforcement
metadata:
  type: feedback
---

Before enabling any access check on Live - a new SgRequireAccess guard, a menu
restriction, anything that can deny someone - list every profile that would be denied,
show it to Bill, and let him tick whoever should keep access first. Promote only after.

**Why:** Bill's standing instruction, 2026-08-25: "I always want to be able to tick
before we promote accesses." Turning enforcement on and letting users discover it
generates support calls he has to field. Granting first means the same end state with
no disruption.

**How to apply:** For a guard on program X, run this before promoting and hand him the
list:

    SELECT RTRIM(SPUSER) AS USER_PROFILE, RTRIM(SPOP01) AS GRANTED
    FROM SGHDSDATA.SYPGMS
    WHERE RTRIM(SPPGID) = 'X' AND RTRIM(SPOP01) <> 'Y' ORDER BY 1

He ticks the ones who should keep it (usually in HarrisData's own Program Option
Security Maintenance screen, not by SQL), then the page is promoted to EIP.

Related: [[feedback-caveats-before-code]]
