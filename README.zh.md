# php2shell-lab

[English](README.md) | [日本語](README.ja.md) | 中文

围绕 **PHP2Shell**——2026 年 PHP 引擎级内存破坏漏洞族（CVE-2026-109618 / CVE-2026-109619，CVSS 9.8/9.2，PHP 8.4.27 修复）——构建的黑盒 CTF 靶机。漏洞在**引擎**，不在应用：小巧的 PHP 应用层刻意做到零注入。你需要指纹识别运行时、关联漏洞通告，然后选择正确的弹道。
卡住了？完整题解见 [docs/SOLUTION.md](docs/SOLUTION.md)。

## 快速开始

**前提：** 只需 Docker Engine（Windows/macOS 用 Docker Desktop）+ Compose v2——宿主机不需要 PHP 工具链。支持 Windows 10/11（WSL2）、Linux、macOS；你的 CPU 能打哪条利用链见下方平台说明。

```bash
git clone https://github.com/taniungou/php2shell-lab.git
cd php2shell-lab
docker compose up -d --build          # 首次构建会拉取钉版的 base 镜像
```

打开 `http://localhost:18901`，用用户 `demo`（密码 `change-me-demo`）登录即可进入应用。从这里开始就是黑盒：剩下的自己搞定。

想自定义 flag 值和账号密码？编辑 `private.ini` 后执行：

```bash
docker compose build \
  --build-arg FLAG_LOCAL=flag{...} \
  --build-arg FLAG_PROOF=flag{...}
```

日常运维：

```bash
docker compose logs -f                # 看日志
docker compose down -v && docker compose up -d --build   # 完全重置（泄露窗口重新打开）
docker compose down                   # 停机释放资源
```

Flag 采用 OverTheWire 风格：

| flag | 路径 | 可读权限 |
|------|------|-------------|
| `local.txt` | `/var/www/local.txt` | `www-data` |
| `proof.txt` | `/root/proof.txt` | 仅 `root` |

## 难点所在

- **引擎级，不是应用级。** 没有 SQLi，没有上传绕过谜题。靶机运行在 PHP2Shell 影响范围内的 PHP 版本上，其余全凭你自己。
- **设计上可泄露，但只在开机首打。** 关键的堆泄露原语有一个开机后的窄生存窗口——同类靶机大多把门永远敞开，这台会关上（故意的）。按正路打：先抢泄露、后做侦察——窗口里用来闲逛的时间，到下次 `up` 之前都不会回来。
- **通往 `proof.txt` 的两条提权路径**，优雅程度不同。至少一条经过实弹验证；只有一条能扩展到真实机群。
- **实弹标定。** 本靶机在与自主渗透 agent 的多轮攻防中打磨成型（完整对战记录见 docs/SOLUTION.md）。标定环境（Apple M5 Ultra，本地跑 Qwen3.8-Flash-Next）agent 最快纪录：约 15 分钟收齐双旗；换其他模型/环境可能更快或更慢，仅供参考。人类玩家现实预期：2–4 小时。

## 平台说明（提 issue 前必读）

PHP2Shell 公开的 **RCE 落点**利用假定 x86_64 内核堆布局。在 **Apple Silicon**（以及 Windows-on-ARM 等 aarch64 主机）上，泄露原语可以命中，但 RCE 落点无法复现——我们在 amd64 转译、arm64 原生以及漏洞通告作者的参考配置（[calif.io/php2shell](https://calif.io/php2shell)，x86_64 Linux）下都验证过这一点。在 ARM 主机上，预期解是**泄露链**，不是 RCE 链——泄露链在包括 x86_64 在内的所有环境都能打。详见 [docs/PLATFORM-MATRIX.md](docs/PLATFORM-MATRIX.md)。

## 资源

- 2 CPU / 1 GB 内存上限——与其他靶机并跑安全。
- 未烧录任何真实环境机密；全部凭证为假值。

## 题解

完整击杀链、偏移量、逐轮攻击记录：[docs/SOLUTION.md](docs/SOLUTION.md)。

## 许可

MIT——可修改、可 fork、可用于培训（欢迎署名）。
