---
name: ibmi-mail-not-delivered
description: "RESOLVED 2026-08-28 - CHGSMTPA SMTP domain alias made the box claim screen-graphics.com, trapping 3 users' mail locally; cleared to *NONE and mail flows"
metadata:
  type: project
---

**RESOLVED 2026-08-28.** This note previously said "email does not leave the IBM i". That was
**wrong** - most mail always left fine. Corrected root cause and fix below.

## What was actually broken

`CHGSMTPA` -> **SMTP domain alias** was set to `'SCREEN-GRAPHICS.COM'`, so the box claimed the
company mail domain as one of its own names. That alias occupied slot `0001`, and
`/QTCPTMM/CONFIG/USERS.DAT` bound exactly three users to that slot:

```
BILL     BBUSCH       01:SCREEN-GRAPHICS.COM   <- trapped
CHUTCH   CHUTCHINSON  01:SCREEN-GRAPHICS.COM   <- trapped
HDFILES  HDFILES      01:SCREEN-GRAPHICS.COM   <- trapped
REPORTS  REPORTS      00:*NONE                 <- delivered fine
(every other user)    00:*NONE                 <- delivered fine
```

Mail to those three was written to `/QTCPTMM/MAIL/<profile>` instead of being sent to the
forwarding mailhub. Everyone else fell through to M365 normally. Symptoms this produced:

- `bbusch@` and `hdfiles@` silently accumulated - 700 messages, 68 MB, since 2026-04-18.
- Bounce DSNs are addressed to `hdfiles@`, which was itself trapped, so **four months of delivery
  failures were invisible**. That is the real lesson.
- A single-recipient message to `reports@` delivered, but the multi-recipient
  "Open Order Date GT Today" envelope (`bbusch@` + `reports@`) failed `reports@` with a bare
  `5.1.1` - a mixed local/remote envelope.

## The fix

`CHGSMTPA` -> SMTP domain alias -> **`*NONE`**, then
`ENDTCPSVR SERVER(*SMTP)` / `STRTCPSVR SERVER(*SMTP)`.
Blanking the field does NOT work - the prompter refills `*SAME`, which means "no change".

Verified immediately: test mail to `bbusch@` and `reports@` both left the box (queues stayed
empty), and a commissions report Bill had not been receiving arrived.

## Facts worth keeping

- The box **already relays to M365 correctly** - forwarding mailhub
  `screengraphics-com01e.mail.protection.outlook.com`, port 25, TLS 1.3, `spf=pass` from public IP
  **50.172.49.110**. Note that is a *different* egress IP than a workstation (71.210.4.45), so
  never test the box's connectivity from a PC and generalise.
- This release supports **OAUTH for SMTP** (`CHGSMTPA` additional parameters), set up with IBM
  support. Do not repeat the claim that IBM i cannot do XOAUTH2.
- `SNDSMTPEMM` reports success even when mail is not delivered - never trust its return code. The
  honest signals are `/QTCPTMM/MAIL/<profile>` and the recipient's inbox.
- `SNDSMTPEMM` needs the address quoted inside the list: `RCP(('user@domain.com'))`.
- `WRKNAMSMTP` (alias table) and `WRKDIRE` (directory entries) were both **ruled out** - `BILL` and
  `REPORTS` are identical in both yet behaved oppositely.
- Backups from the change: `/tmp/QATMSMTP-CONFIG.MBR.backup-20260828-2132` and
  `/tmp/USERS.DAT.backup-20260828-2044`. Config member SHA256 was `F7F78E8E...` before the change,
  `3BD34E4F...` after.

See [[feedback-label-confidence-when-diagnosing]] and [[buyer-pattern-project]].

## Post-fix verification (2026-08-28, same evening)

Bill re-ran "Open Order Date GT Today" manually from **Robot Scheduler** after the fix. It arrived
in his mailbox **sent from `hdfiles@screen-graphics.com`**, and no DSN appeared in
`/QTCPTMM/MAIL/HDFILES`. That single run closed both remaining risks:

- **`VFYFROMUSR(*ALL)` is not affected** by removing the domain alias - the box still verifies and
  sends as `hdfiles@`. This was the one concrete risk flagged before the change; it did not
  materialise.
- **The mixed local/remote envelope is fixed** - the same job used to bounce `reports@` with 5.1.1
  every night (134 times). No bounce was generated, so the second recipient was accepted.

Schedules confirmed from the archived queue, useful for future verification:
- `ORD118R` - Mon-Fri 05:08 and 17:08, **Saturday 16:08 only, never Sunday**.
- `Open Order Date GT Today` - every day including weekends at 20:26.
