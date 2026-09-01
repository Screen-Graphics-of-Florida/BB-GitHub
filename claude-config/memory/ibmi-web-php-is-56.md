---
name: ibmi-web-php-is-56
description: "Portal pages run PHP 5.6.23 under ZendServer, not the 7.4 on the command line - lint with /usr/local/zendsvr6/bin/php"
metadata: 
  node_type: memory
  type: project
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-28T15:22:43.463Z
---

**The portal runs PHP 5.6.23 under ZendServer 8.5.5. The command line runs PHP 7.4. They are not
the same interpreter.** Measured 2026-08-28 and it cost three failed deploys before I checked.

| Where | Binary | Version |
|---|---|---|
| Apache / every portal page | ZendServer 8.5.5 | **PHP 5.6.23** |
| Command line, `php` on PATH | `/QOpenSys/pkgs/bin/php` (SeidenPHP) | PHP 7.4 |
| Command line, the web's engine | `/usr/local/zendsvr6/bin/php` | PHP 5.6.23 |

Prove it in one command:
`curl -skI https://localhost:5610/... | grep X-Powered-By` -> `PHP/5.6.23 ZendServer/8.5.5`

CLAUDE.md previously claimed both environments run SeidenPHP and "never ZendSvr6". That is wrong for
the web SAPI and is now corrected in section 3.

**So any page the portal serves must be PHP 5.6 code.** No `intdiv()`, `??`, `<=>`, `Throwable`,
scalar type hints, return types, or `[]` literals - use `array()`. Confirmed present in the 5.6 web
build: `zip`, `zlib`, `mbstring`, `iconv`, `json`, so `ZipArchive` and therefore real `.xlsx`
generation do work there.

A `catch (Throwable $e)` is the nastiest form of this: it compiles fine on 5.6 and then never
matches, so every error you thought you were handling becomes a bare 500 instead.

Scheduled CLI scripts may use the 7.4 binary, but name it explicitly in the scheduler command
rather than relying on PATH.

How the real failure presented, for next time: page lints clean on 7.4, browser gets
`Internal Server Error`, Apache access log shows 500 with a ~525-byte body, Apache error log has
nothing, and `/QOpenSys/var/log/php_error.log` has nothing either - because that file is mode 644
owned by BILL and QTMHHTTP cannot write it. The way through was `curl` from the box itself with
`ini_set('display_errors','1')` in the page, which put the real fatal in the response body.

See [[ibmi-operational-lessons]] and [[buyer-pattern-project]].
