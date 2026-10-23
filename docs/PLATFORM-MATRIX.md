# PLATFORM-MATRIX

Verified behavior of the PHP2Shell exploit chains on this target
(`php:8.4.26-apache`, digest-pinned) per host architecture.

| Host configuration | Leak chain (`stage1` heap base → `stage2` libphp base) | RCE landing (`stage3`) | Intended solution |
|---|---|---|---|
| x86_64 Linux native | ✅ lands | ✅ lands | RCE chain or disclosure chain |
| amd64 under emulation (Apple Silicon, Rosetta/QEMU) | ✅ lands | ❌ does not land | **disclosure chain** |
| arm64 native | ✅ lands | ❌ does not land | **disclosure chain** |
| Upstream reference config | ✅ lands | ✅ lands | either |

## Why

The public RCE-landing payloads assume an x86_64 kernel heap layout for the
final landing zone. On Apple Silicon / aarch64 the heap geometry differs and
the landing address is never mapped — verified across emulation, native arm64,
and dual independent attack paths (agent + manual). The leak primitives
themselves are architecture-insensitive and land everywhere.

## Practical rule

If `uname -m` on the host is not `x86_64`, play the **disclosure chain**:
use the leak to pull the audit-console credential, then escalate via the
backup audit surface. The RCE chain is a bonus that only x86_64 players get.

## Window lifetime

The disclosure window is deterministic but **first-shot only**: it is open at
container boot and consumed by the first successful leak sequence. To re-arm,
restart the container (`docker compose down -v && docker compose up -d --build`).
