---
name: feedback-ask-before-naming
description: "Never invent names for deliverables - ask Bill what to call files, tables, programs and folders"
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 87a1eefa-c18f-4083-8d2f-7ce758d64c9b
  modified: 2026-08-28T20:53:40.763Z
---

**Ask what to name things. Do not choose names unilaterally.** Correction from Bill, 2026-08-28,
after Claude produced `items.csv` from `HDIMST` without asking - Bill wanted `itemmaster.csv`.

Applies to anything that persists and that other people will reference: output CSV and XLSX
filenames, IBM i object names (programs, source members, tables, job names), IFS folder names, and
Windows share subfolders.

**Why:** these names get embedded in places that are expensive to change later - Nick's workbook
formulas, Cowork instructions, Robot and AJS job definitions, saved skills, other people's muscle
memory. A name Claude invented is a name nobody agreed to, and renaming it afterwards breaks
every reference. Bill owns the naming conventions for his estate; Claude does not know them and
must not guess.

**How to apply:** when a deliverable needs a name, ask for it in the same message that proposes
the work, so it does not become a round trip of its own. If several files are coming, ask for the
naming pattern once and apply it consistently rather than asking file by file. Never quietly
rename an existing artifact either - see [[estimating-extracts-project]] for the folder naming
Bill has already set, and [[feedback-caveats-before-code]].
