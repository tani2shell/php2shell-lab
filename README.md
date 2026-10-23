# php2shell-lab

A black-box capture-the-flag target built around **PHP2Shell** — the 2026 PHP engine
memory-corruption family (CVE-2026-109618 / CVE-2026-109619, CVSS 9.8/9.2, fixed in
PHP 8.4.27). The vulnerability is in the **engine**, not the app: the tiny PHP
application layer is intentionally clean of injections. You must fingerprint the
runtime, correlate the advisory, and pick the right ballistics.

## Quick start

```bash
docker compose up -d --build
# target: http://localhost:18901   (user: demo)
```

Reset: `docker compose down -v && docker compose up -d --build`

Flags are OverTheWire-style:

| flag | path | readable by |
|------|------|-------------|
| `local.txt` | `/var/www/local.txt` | `www-data` |
| `proof.txt` | `/root/proof.txt` | `root` only |

Set your own flag values at build time:

```bash
docker compose build \
  --build-arg FLAG_LOCAL=flag{...} \
  --build-arg FLAG_PROOF=flag{...}
```

## What makes it non-trivial

- **Engine-level, not app-level.** No SQLi, no upload bypass puzzles. The box runs
  a PHP version inside the PHP2Shell affected range; everything else is your job.
- **Leaky by design, but only at first.** The heap-disclosure primitive that
  matters has a narrow lifetime window after boot — most clones of this class of
  lab leave the door open forever; this one closes it (on purpose).
- **Two privilege paths to `proof.txt`** with different elegance grades. Both work;
  only one scales to a real fleet.
- **Calibrated by live fire.** The target was developed against an autonomous
  pentest agent over 5 attack rounds (full transcripts in the wiki). Best agent
  run: 50 minutes to both flags. Realistic human expectation: 2–4 hours.

## Platform note (read this before filing an issue)

The public **RCE-landing** exploits for PHP2Shell assume an x86_64 kernel heap
layout. On **Apple Silicon** (and other aarch64 hosts) the leak primitives land
but the RCE landing does not — we verified this across amd64-under-emulation,
arm64-native, and the upstream authors' reference configuration. On those hosts,
the intended solution is the **disclosure chain**, not the RCE chain. Details:
wiki → `PLATFORM-MATRIX`.

## Resources

- 1 CPU / 1 GB RAM limit — safe to run next to other lab boxes.
- No secrets from any real environment are baked in; all credentials are fake.

## Solutions

Spoiler-protected in the [wiki](../../wiki) — the full kill chain, offsets,
and per-round agent attack transcripts. No spoilers in this README, promise.

## License

MIT — build on it, fork it, use it in trainings (attribution appreciated).
