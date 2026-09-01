# HarrisData Work Rules - SG5 (Test) and EIP (Live)

This file lives on the IBM i at `/HarrisData/CLAUDE.md` (`W:\HarrisData\CLAUDE.md`).

It is here so that **any** Claude Code session opened anywhere under `W:\HarrisData`
picks these rules up automatically. `C:\Users\Bill\CLAUDE.md` does NOT load for sessions
rooted on `W:`, because it is not in the directory tree above the working directory.
That gap is what this file closes.

Because it lives on the server, it survives an OS wipe of any workstation and is
identical for every machine and every user who opens the tree.

- **This file** = work rules. Environments, schemas, workflow, standards. Source of truth.
- **`C:\Users\Bill\CLAUDE.md`** = machine bootstrap. Drive mappings, ODBC driver install,
  credentials handling, portable-config paths.

Sections keep their original numbers so existing references (§3, §5, §12) still resolve.
Machine-specific sections 1, 8, 9 and 10 are deliberately not duplicated here.

---

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

## 12. Project Time & Cost

Deliberately NOT duplicated here. `W:\HarrisData` is a shared file server and section 12
contains Bill's personal hourly rate. The costing rules and the rate live only in
`C:\Users\Bill\CLAUDE.md` section 12, and the register is
`claude-config\PROJECT-COSTS.md`.

If a session in this tree needs to cost a project, read the rules from the machine copy.
