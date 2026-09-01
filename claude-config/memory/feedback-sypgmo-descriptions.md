---
name: feedback-sypgmo-descriptions
description: Every SYPGMO row must get a real SOMDES description, never the placeholder 'View'
metadata:
  type: feedback
---

When registering a program in SYPGMO (`SG5STDPGM.SYPGMO` / `HDSSTDPGM.SYPGMO`), the
`SOMDES` column must contain a real, human-readable description of what the program
does - never the literal `'View'`.

Bill's convention, from the rows he wrote himself: ~20 chars max, module prefix then
short phrase. `MO Requirements`, `CS Service Inquiry`, `OO Line Item Cmts`,
`Bookings Dashboard`, `MO Daily Labor`, `Portal Landing Page`.

**Why:** The CLAUDE.md §4 line "Opt Seq `1`, Option Description `View`" describes the
*shape* of a registration row, not a literal value to paste. I read it literally and
registered `SGMOCMPISS` with `SOMDES = 'View'`, which is the only non-conforming row in
the whole file. Bill's words: "I can't have program names without descriptions and you
keep putting them there."

**How to apply:** Before writing any SYPGMO INSERT, pick the description from what the
page actually does and match the existing prefix style. Check neighbouring rows in
SYPGMO for the module's prefix convention first. Also check whether the program is
already registered under a different SOPGID - `MOMATLCMP` and `SGMOCMPISS` appear to be
duplicate registrations of the same MO Material Components page.

Related: [[project-eip-menu-registration]]
