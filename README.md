# php2shell-lab

[English](README.md) | [日本語](README.ja.md) | [中文](README.zh.md)

A black-box capture-the-flag target built around **PHP2Shell** — the 2026 PHP engine
memory-corruption family (CVE-2026-109618 / CVE-2026-109619, CVSS 9.8/9.2, fixed in
PHP 8.4.27). The vulnerability is in the **engine**, not the app: the tiny PHP
application layer is intentionally clean of injections. You must fingerprint the
runtime, correlate the advisory, and pick the right ballistics.
Stuck? Full walkthrough in [docs/SOLUTION.md](docs/SOLUTION.md).

## Quick start

**Prerequisites:** Docker Engine (or Docker Desktop on Windows/macOS) with
Compose v2 — that's it. No PHP toolchain needed on the host. Works on
Windows 10/11 (WSL2), Linux, and macOS; see the platform note below for
which exploit chain your CPU supports.

```bash
git clone https://github.com/tani2shell/php2shell-lab.git
cd php2shell-lab
docker compose up -d --build          # first build pulls the pinned base image
```

Open `http://localhost:18901` and sign in as user `demo`
(password `change-me-demo`) to reach the app. From here the box is
black-box: figure out the rest.

Want your own flag values and account passwords? Edit `private.ini`, then:

```bash
docker compose build \
  --build-arg FLAG_LOCAL=flag{...} \
  --build-arg FLAG_PROOF=flag{...}
```

Housekeeping:

```bash
docker compose logs -f                # watch the target
docker compose down -v && docker compose up -d --build   # full reset (re-arms the leak window)
docker compose down                   # stop and free resources
```

Flags are OverTheWire-style:

| flag | path | readable by |
|------|------|-------------|
| `local.txt` | `/var/www/local.txt` | `www-data` |
| `proof.txt` | `/root/proof.txt` | `root` only |

## What makes it non-trivial

- **Engine-level, not app-level.** No SQLi, no upload bypass puzzles. The box runs
  a PHP version inside the PHP2Shell affected range; everything else is your job.
- **Leaky by design, but only at first.** The heap-disclosure primitive that
  matters has a narrow lifetime window after boot — most clones of this class of
  lab leave the door open forever; this one closes it (on purpose). Play it
  straight: hit the leak first, recon second — window time spent browsing is
  gone for good until the next `up`.
- **Two privilege paths to `proof.txt`** with different elegance grades. At
  least one is live-fire verified; only one scales to a real fleet.
- **Calibrated by live fire.** The target was developed against an autonomous
  pentest agent over multiple attack rounds (transcripts in docs/SOLUTION.md).
  Fastest recorded agent run on our calibration rig (Apple M5 Ultra, local
  Qwen3.8-Flash-Next): ~15 minutes to both flags — other models or hosts may
  land faster or slower, so treat this as one data point, not a leaderboard.
  Realistic human expectation: 2–4 hours.

## Platform note (read this before filing an issue)

The public **RCE-landing** exploits for PHP2Shell assume an x86_64 kernel heap
layout. On **Apple Silicon** (and other aarch64 hosts such as Windows-on-ARM)
the leak primitives land but the RCE landing does not — we verified this across
amd64-under-emulation, arm64-native, and the advisory authors' reference
configuration ([calif.io/php2shell](https://calif.io/php2shell), x86_64 Linux).
On ARM hosts the intended solution is the **disclosure chain**, not the RCE
chain — it works everywhere, including x86_64. Details:
[docs/PLATFORM-MATRIX.md](docs/PLATFORM-MATRIX.md).

## Resources

- 2 CPU / 1 GB RAM limit — safe to run next to other lab boxes.
- No secrets from any real environment are baked in; all credentials are fake.

## Solutions

Full kill chain, offsets, and per-round attack transcripts:
[docs/SOLUTION.md](docs/SOLUTION.md) — spoiler-protected by this section
being at the bottom, promise.

## License

MIT — build on it, fork it, use it in trainings (attribution appreciated).
