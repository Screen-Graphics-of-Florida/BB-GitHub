# Bill's Machine & Environment Setup

Portable bootstrap notes for Claude Code on any machine (desktop or laptop). Copy this
file to `~/CLAUDE.md` (i.e. `C:\Users\Bill\CLAUDE.md`) on a new machine so Claude Code
picks it up automatically as project context from the home directory.

Note: the separate auto-memory system under `.claude/projects/.../memory/` is
per-machine and does NOT sync when you copy this file — it will rebuild itself on the
laptop over time as you work. This file is the one thing meant to travel with you.

## 0. Governing Rule - Accuracy Over Speed

Stated by Bill on 2026-08-28. **This rule outranks everything else in this file.** Where any
other instruction would trade accuracy for speed, this one wins.

> My work with you is not about getting projects done quickly. It's all about getting them done
> right the first time and having them be as accurate as possible. That means nearly 100%
> accurate. Not 75%, 90% or 50%. It is extremely critical that our work is accurate as many
> people depend on me and what I produce for them.

Bill is the source of truth for other people. A confident wrong answer does not just waste his
time, it propagates into what he hands to others. Slow and right beats fast and plausible, every
single time.

What this requires in practice:

- **Never present a hypothesis as a finding.** Every claim carries its status in the same
  sentence: confirmed by <specific evidence>, or hypothesis / unverified / assumption. "Probably"
  and "should be" are not evidence.
- **Prove the mechanism, not just the correlation.** A theory that fits the symptoms is not a
  cause. Do not hand over a command until the mechanism itself is demonstrated.
- **Never generalize from a test that cannot speak to the target.** Measuring the workstation
  says nothing about the IBM i. Name what was actually tested and what it does not cover.
- **Re-verify inherited claims** - memory notes, earlier turns in the same conversation, prior
  project write-ups - before building anything on them.
- **Prefer "show me screen X" over "run command Y"** while a cause is still open. Reading is free;
  a wrong change on production is not.
- **State the specific risk you can see and cannot rule out**, rather than a generic caveat.
- **"I do not know yet" and negative results are acceptable deliverables.** A wrong confident
  answer is not. When something cannot be verified from here, say so and name who can verify it
  (IBM support, the M365 admin, Bill on the box).
- **Correct errors immediately and plainly** when new evidence lands, and say what it changes.

## 1. Network Drives

| Drive | UNC Target | Purpose |
|---|---|---|
| `W:` | `\\192.168.120.40\root` | HarrisData root — SG5/EIP custom PHP, backups |
| `Q:` | `\\MRNATURL\home` | Home share |

Remap on a new machine (PowerShell, run once):
```powershell
net use W: \\192.168.120.40\root /persistent:yes
net use Q: \\MRNATURL\home /persistent:yes
```

Key working paths under `W:`:
- `W:\HarrisData\SG5\Custom\SG\` — Test environment custom PHP
- `W:\HarrisData\EIP\Custom\SG\` — Live environment custom PHP
- `W:\HarrisData\SG5\Custom\Backup Files` — Test backups
- `W:\HarrisData\EIP\Custom\Backup Files` — Live backups

## 2. IBM i / DB2 Connection (AS/400)

- **Host/IP from Windows: `192.168.120.40`** — this is the same box that serves the `W:` share. Confirmed working via `System.Data.Odbc`.
- **Do NOT use `10.10.0.5`** — that's the IBM i's own internal loopback address, unreachable from any Windows workstation (times out with `HYT00 Connection login timed out`). It only appears correct inside PHP `db2_connect()` calls running ON the IBM i itself.
- Driver required on the workstation: **"IBM i Access ODBC Driver"** — must be installed before connecting (not preinstalled on a fresh laptop; download/install from IBM i Access Client Solutions).
- No DSN is pre-registered — connect DSN-less via `System.Data.Odbc.OdbcConnection` in PowerShell:
  ```
  DRIVER={IBM i Access ODBC Driver};SYSTEM=192.168.120.40;UID=<profile>;PWD=<password>;
  ```
- AS/400 user profile: `bill` — password provided ad hoc by Bill when needed, intentionally not stored here (see §7 Credentials).
- Inside PHP running on the IBM i itself, use the HarrisData framework object instead: `$i5Connect->getConnection()`.

## 3. SG5 (Test) vs EIP (Live) Environments

**PHP: the web and the command line are DIFFERENT interpreters.** Measured 2026-08-28, and getting
this wrong ships pages that lint clean and then return a bare 500:

| Where | Binary | Version |
|---|---|---|
| **Apache / the portal pages** | ZendServer 8.5.5 | **PHP 5.6.23** |
| Command line (`php` on PATH) | `/QOpenSys/pkgs/bin/php` (SeidenPHP) | PHP 7.4 |
| Command line, the web's engine | `/usr/local/zendsvr6/bin/php` | PHP 5.6.23 |

Verify any time with `curl -skI https://localhost:5610/... | grep X-Powered-By`, which answers
`PHP/5.6.23 ZendServer/8.5.5`.

**So: lint every custom page with `/usr/local/zendsvr6/bin/php -l`, not the one on PATH.** Any page
served by the portal must be PHP 5.6 code — no `intdiv()`, `??`, `<=>`, `Throwable`, scalar type
hints, return types, or `[]` array literals. `array()` everywhere. A CLI-only script (a scheduled
job) may use the 7.4 binary, but say so explicitly in its shebang or the scheduler command.
Extensions confirmed present in the 5.6 web build: `zip`, `zlib`, `mbstring`, `iconv`, `json`.

### Database / environment topology (confirmed by Bill 2026-08-28)

There are **three databases and two program environments**. Both test databases are copied from
`SGHDSDATA` on demand, so their data is a point-in-time snapshot of Live:

| Database | Program library | What it is |
|---|---|---|
| `SGHDSDATA` | `HDSSTDPGM` | **LIVE** |
| `T1HDSDATA` | `HDSSTDPGM` | Test copy of Live, running the **same** environment as Live |
| `S5HDSDATA` | `SG5STDPGM` | Test copy of Live, with its **own** environment (the SG5/HD5 one) |

That matters when anything is created per environment: `T1HDSDATA` shares `HDSSTDPGM` with Live, so
a program-library object serves both Live and T1. `S5HDSDATA` is the only one with a separate
program library. Anything written to cover "Test" must say **which** test database it means -
scripts written before `S5HDSDATA` existed only handle `SGHDSDATA` and `T1HDSDATA`.

| | TEST (SG5) | LIVE (EIP) |
|---|---|---|
| Portal URL | `https://portal.screen-graphics.com:5610` | `https://portal.screen-graphics.com:5601` |
| Program library | `SG5STDPGM` | `HDSSTDPGM` |
| Portal/menu table schema | `S5HDSDATA` | `SGHDSDATA` |
| Business data schema | `SGHDSDATA` (always, even on Test) | `SGHDSDATA` |
| Custom object schema (new tables we create) | `SGOBJ` | `SGOBJ` |
| Custom PHP files | `W:\HarrisData\SG5\Custom\` | `W:\HarrisData\EIP\Custom\` |
| Web root on IBM i | `/www/sg5eip/` | — |
| Apache error log | `/www/sg5eip/logs/error_log.Q1YYMMDD00` | — |

Custom script URL pattern: `https://portal.screen-graphics.com:<port>/Custom/SG/<relative-path>.php`
Example: `https://portal.screen-graphics.com:5610/Custom/SG/Manufacturing/MODailyLaborReport.php`

**Schema rule of thumb:**
- Business data tables (OEORDH, HDMLDM, HDMOHM, HDMWCM, HREMPL, HDIMST, HDCUST, etc.) → always `SGHDSDATA`, both environments.
- Portal/menu tables (SYPORR, SYROLD, SYPORT, SYURLM, SYROLM, SYPGMO, SYPGMS, SYROLU) → `S5HDSDATA` on SG5, `SGHDSDATA` on EIP Live. Getting this backwards on a "SG5 only" script can silently modify Live data (this has happened — see incident note in memory `feedback_portal_schema_rules`).
- Brand-new custom tables (not part of the Harris data model) → schema `SGOBJ`, both environments.

### Library Lists

| Seq | TEST Library | LIVE Library |
|-----|-------------|-------------|
| 10 | S5HDSDATA | SGHDSDATA |
| 20 | SG5MODPGM | HDSMODPGM |
| 30 | SG5STDPGM | HDSSTDPGM |
| 40 | AVATAXR2 | AVATAXR2 |
| 50 | SGPGM | SGPGM |
| 60 | SG | SG |
| 70 | QTEMP | QTEMP |
| 80 | DRVSFLEX | DRVSFLEX |
| 90 | QGPL | QGPL |
| 100 | INFOPRINT | INFOPRINT |
| 110 | HSSF | HSSF |
| 120 | RPGMAIL | RPGMAIL |
| 130 | PROITRG | PROITRG |
| 140 | C3 | C3 |
| 150 | SEQUEL | SEQUEL |
| 160 | ROBOTLIB | ROBOTLIB |
| 170 | ZENDSVR6 | ZENDSVR6 |
| 180 | CGIDEV2 | CGIDEV2 |

### BaseConfiguration.icl (Live)
- `homeURL` = `https://portal.screen-graphics.com:5601`
- `phpPath` = `/`
- `homePath` = `/HarrisData/EIP/`
- `cGIPath` = `/harris-CGI/`
- `dataBaseID` = `SG`
- `pgmLibrary` = `HDSSTDPGM.`

### Technology Stack
IBM i / AS400 V7R5, Apache 2.4.53 + FastCGI, **PHP 5.6.23 under ZendServer 8.5.5 for all portal
pages** (see the PHP table above), Chart.js 4.x, IBM i ODBC via "IBM i Access ODBC Driver".

### Where PHP errors actually go
`error_log = /QOpenSys/var/log/php_error.log` — **not** the Apache error log, which never receives
PHP errors. Caveat found 2026-08-28: that file is mode 644 owned by BILL, so the web server
(QTMHHTTP) can read but **not write** it, and runtime `error_log()` output from a web request is
silently dropped. For diagnostics a web request can actually write, use `/tmp`, which is
world-writable. `display_errors` is Off, so an uncaught fatal reaches the browser as a bare 500.

### Server Timezone
The IBM i physically resides in the **Eastern** time zone (confirmed by Bill 2026-07-07), not Central — it will not move. Existing scripts wrongly hardcode `date_default_timezone_set('America/Chicago')`; this is a longstanding codebase bug, not a deliberate choice. New scripts must NOT hardcode a server timezone for anything shown to a viewer — pull the display timezone from the viewer's own browser/PC via JS (`new Date().toLocaleTimeString('en-US', {timeZoneName:'short'})`), never assume one.

## 4. EIP Menu System

Three IBM i DB2 tables drive navigation:
- **SYURLM** — URL Master (the actual URL/page/report)
- **SYPORT** — Portal Master (groups/sequences URL IDs into menu entries; two visible levels: top nav + flyout)
- **SYROLD** — Role Detail (assigns portals to roles)
- **SYPGMO** — required registration for every program (Opt Seq `1`, Option Description `View`); lives in program libraries (`SG5STDPGM.SYPGMO` / `HDSSTDPGM.SYPGMO`), never in the data schemas.

Key facts:
- `FPID`/`FUID` are CHAR fields — always quote in SQL (`WHERE FPID = '42'`)
- `@@phpPath` resolves to `/`, not `/Custom/`
- URL pattern for a page under Custom/: `@@homeURL@@phpPathCustom/SG/<subpath>.php`
- EIP natively supports only 2 menu levels; true 3-level nesting needs a custom PHP "folder" landing page
- Never add SG custom portals to reserved roles (`SYROLM` where `RLRSV='Y'`)

## 5. Development Workflow Rules

- **Always work in SG5 (Test) first.** Only promote to EIP (Live) after confirmed working there.
- **Copy-to-both, every time:** after any change to a SG5 custom PHP file, immediately copy it to the matching EIP path, then provide both test URLs (5610 and 5601).
- **Backup before any change/execute — no exceptions:** copy the file to Backup Files before editing; dump affected DB tables as INSERT statements before any INSERT/DELETE/UPDATE. Sequence is always Backup → Verify → Execute.
- Every EIP menu item opens in a new browser window/tab.
- SEQUEL Viewpoint is being phased out — new reports/dashboards are PHP pages in EIP, not wrappers around SEQUEL.
- IBM i DB2 rejects trailing semicolons on SQL statements — never add one.

## 6. SG Report Design Standard

Canonical template (locked 2026-07-01): `W:\HarrisData\SG5\Custom\SG\Purchasing\PORequirementsReport.php` — start every new report from this file rather than re-deriving the CSS/layout. Once `W:` is mapped on this machine, the file (and its full color palette / refresh-bar / filter-bar spec) is directly accessible there.

Quick reference (full spec is in the template file itself):
- Title bar: dark gray gradient `#111827 → #6B7280`, white bold H1
- Table header `#374151` bg, white bold text; row hover `#EFF6FF`; links `#2563EB` bold
- Refresh bar `#2563EB`; buttons: Back-to-EIP cyan `#06B6D4`, Logout red `#CC1F20`, Refresh Now purple `#7B1FA2`, Export green `#1DA032`
- Countdown format: `D days HH:MM:SS`, omitting zero-valued leading units
- Filters are always `<select>` dropdowns populated from live query data, never free text
- CYMD date: `(Year-1900)*10000 + Month*100 + Day`; never `(int)`-cast an IBM i DATE column
- No em dashes anywhere in generated text/UI — use ` - ` or rewrite

## 7. Standard PHP File Header

```php
<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

function dateToCYMD(DateTime $d): int {
    return ((int)$d->format('Y') - 1900) * 10000
         + (int)$d->format('n') * 100
         + (int)$d->format('j');
}
```
(Deliberately omits `date_default_timezone_set` — see §3 Server Timezone. Any viewer-facing time must come from browser JS, not a server-side assumption.)

## 8. Claude Code / MCP Setup

- The `claude.ai` connectors (Shopify, Asana, Atlassian, Box, Canva, Figma, HubSpot, Intercom, Linear, Microsoft 365, Notion, monday.com, Amplemarket, etc.) are tied to the Anthropic **account**, not this machine — they'll show up automatically on the laptop once signed into the same claude.ai account. Any still marked "needs authorization" must be authorized once per account via claude.ai connector settings (or `claude mcp` / `/mcp` in an interactive session) — this is a one-time step, not per-machine.
- No project-local MCP servers are defined in `settings.json` on this machine as of 2026-07-07 — everything currently in use is an account-level connector.
- The auto-memory system (`~/.claude/projects/.../memory/`) is local per machine and won't carry over — treat this CLAUDE.md as the seed; memory will rebuild from scratch on the laptop as you work there.

## 9. Credentials & Secrets

Intentionally **not stored in plaintext in this file** — this file may end up copied around or backed up in places you don't fully control.

What you'll need to have on hand (from a password manager, not from this file):
- AS/400 user profile `bill` password (for ODBC connections — see §2)
- Any Windows domain credentials needed to remap `W:`/`Q:` on the new machine
- Portal login credentials for `portal.screen-graphics.com` (both :5610 and :5601), if prompted

If you do end up pasting a secret into a local config for convenience (e.g. an ODBC connection string saved to a script), keep it out of anything that syncs to git or cloud storage unencrypted.
## 10. Portable Config — Always Write Both Copies

Set up 2026-08-27. The durable Claude Code config lives in a git repo so it follows Bill
between machines:

`C:\Users\Bill\SG GitHub\claude-config\` — holds `CLAUDE.md`, `memory\`, `settings.json`,
and a README with the new-machine steps.

**Standing rule for Claude: whenever you change `CLAUDE.md` or `settings.json`, write the
change to BOTH locations in the same turn.** Never update one and leave the other stale.

| Live path | Repo copy |
|---|---|
| `C:\Users\Bill\CLAUDE.md` | `SG GitHub\claude-config\CLAUDE.md` |
| `C:\Users\Bill\.claude\settings.json` | `SG GitHub\claude-config\settings.json` |

The auto-memory folder needs no copying — `.claude\projects\c--Users-Bill\memory` is a
**junction** pointing at `claude-config\memory`, so memory writes land in the repo already.
(This supersedes the note in §8 saying memory does not carry over.)

Rules:
- **Never** add `.claude\.credentials.json`, or any real password, to the repo.
- The project folder name `c--Users-Bill` derives from the working directory, so a new machine
  must also be `C:\Users\Bill` for the junction to line up.
- Don't run Claude Code on two machines at once against the shared memory folder.
- Session transcripts (`*.jsonl`) are deliberately not synced.
- Synced memory carries distilled facts, not conversations — anything worth keeping has to be
  written into `memory\` or this file on purpose.

## 11. Program Security — Required On Every Page

Standing rule, set 2026-08-27: **every custom SG page must be registered in program security.**
The portal only decides whether the menu link shows; it does not decide who may open the page.
`SGINQ` on Live reaches 41 users across 15 roles, so an ungated page there is open to all of them.

Three steps for any new page:

1. Register in `SYPGMO` — `SG5STDPGM.SYPGMO` on Test, `HDSSTDPGM.SYPGMO` on Live, never in the
   data schemas. `SOMDES` gets a real description, never `'View'`.
2. Gate the page, before any output:
   ```php
   require_once dirname(__FILE__) . '/../SgRequireAccess.php';
   sgRequireAccess('PGMID');      // the 10-char SYPGMO program id
   ```
3. Grant users through `SgProgramAccess.php`, which maintains `SYPGMS` in the environment's data
   schema (resolved from `SERVER_PORT`).

**Avoiding a self-inflicted outage:** a user with no `SYPGMS` row is denied, so enforcing before
anyone is granted locks out everybody. Count the program's `SYPGMS` rows first and only call
`sgRequireAccess()` when at least one exists — enforcement then begins automatically on the first
grant. `BuyerPattern.php` does this and shows a badge for which state it is in.

This is separate from any row-level filtering a page does internally (for Buyer Pattern, the
`BUYPATTERN`/`SALESPRSN` rows in `PROITRG.UDCDETAIL`).

## 12. Project Time & Cost - Track and Cost Out Every Project

Standing rule, set 2026-08-28 (outside-cost method and rate band both revised the same day):
**track working time on every project from the start, and cost it out at the end.** Three figures,
always given in full.

**1. In-house cost** = actual working time, hours **and minutes**, x **$76.16/hr** (Bill's salary
per hour). **Never round the time for this figure** - 14 h 20 m is 14.3333 h.

**2. Outside contract cost** = a realistic **bottom-up estimate of what a normal programmer would
take working conventionally without AI**, including testing, rework and deployment. Build it as a
**line-item table of the real pieces of work**, each with low and high hours; total the range and
take the **midpoint**. Show the midpoint in weeks at 37.5 h/week as well.

Cost the **midpoint hours** at the outside rate band - **$90/hr low end, $150/hr high end** for an
outside IBM i contract programmer - giving a dollar range, with **$120/hr** as the single headline
figure when one number is needed. Dollars are always costed on midpoint hours, never on the ends of
the hour range. Show the absolute envelope too (low hours x $90 as floor, high hours x $150 as
ceiling) so the number cannot be called cherry-picked.

**3. Savings** = outside cost minus in-house cost, given at $90 / $120 / $150, plus the multiple.

Rules for getting the numbers right:
- Measure actual time from the Claude Code session transcripts, not from memory. Sum intervals
  between consecutive events and drop idle gaps over 15 minutes; **state the threshold used.**
- Attribute time only to the project in question. Concurrent sessions on other work are excluded.
- If a phase ran on the other PC and its transcript did not sync, say so and fall back to the
  file-timestamp span.
- Enumerate the actual artifacts for the outside estimate - each page and view, each table and its
  DDL, each export writer, the security layer, the menu wiring, the deployment. Never estimate the
  project as a single blob; a blanket multiplier is not defensible to management, a named line item
  is.
- Post the whole thing in the chat, not just to a file.

**The closing line - required, never omit.** Every project costing ends with these two sentences,
in this shape, worded like this:

> Even at the low end of the estimate the project saved **$X**. At the midpoint, each hour of your
> time produced **$Y** of contracted-out work.

- **$X** = low-end hours x **$90/hr**, minus the in-house cost. The true floor, so the case
  survives the most pessimistic reading anyone can put on it.
- **$Y** = (midpoint hours x **$120/hr**) divided by actual working hours, to the dollar.

Recompute both every time from the current rate band. Never carry a previous project's figures
forward, and do not reword the pair into something blander - this is the part that gets shown to
management.


**Superseded, do not use:** the original morning-of-2026-08-28 rule "rounded hours x 3 x $150" (it
gave $6,300 for Buyer Pattern where the line-item estimate gives $22,950 to $38,250), and the flat
single $150/hr outside rate that preceded the $90-$150 band.

### Where costings are recorded - required

Every completed costing is appended to the running register:

`C:\Users\Bill\SG GitHub\claude-config\PROJECT-COSTS.md`

This is the **source of truth** for project cost figures and the thing Bill presents to management.
Rules:

- Append a **summary row** and a **full detail section** when a project closes. Never edit a closed
  row; corrections go in as a dated note under that project's detail.
- **Recompute the TOTAL row** every time a row is added. Never carry a stale total forward.
- Post the full writeup in the chat as well, per the deliverables rule - the register is the
  archive, not a substitute for showing Bill the work.
- **Always also produce an Evernote-ready writeup for the individual project**, posted in
  full in the chat so Bill can paste it straight into Evernote. Standard shape: problem,
  root cause, the fix, verification, damage or impact found, facts worth keeping, then the
  three cost figures. One note per project, every time, in addition to the register row.
- The register lives in the `claude-config` repo so it follows Bill between machines and is
  version-controlled. It is not a config file, so the "write both copies" rule in section 10 does
  not apply to it - there is only one copy, in the repo.
