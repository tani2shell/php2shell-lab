# php2shell-lab

[English](README.md) | 日本語 | [中文](README.zh.md)

**PHP2Shell** — 2026年のPHPエンジン級メモリ破損脆弱性群（CVE-2026-109618 / CVE-2026-109619、CVSS 9.8/9.2、PHP 8.4.27で修正済み）を軸に構築した、ブラックボックスCTFターゲットです。脆弱性はアプリではなく**エンジン**にあります。ごく小規模なPHPアプリ層にはインジェクション系の問題が意図的に存在しません。実行時ランタイムを指紋で特定し、アドバイザリと照合し、正しい弾道を選定してください。
詰まった場合は[docs/SOLUTION.md](docs/SOLUTION.md)の全解法手順を参照。

## クイックスタート

**前提条件：** Docker Engine（Windows/macOSではDocker Desktop）+ Compose v2 のみ。ホストにPHPツールチェーンは不要です。Windows 10/11（WSL2）、Linux、macOSで動作。CPUに対応可能な攻撃チェーンは下記のプラットフォーム注意を参照。

```bash
git clone https://github.com/tani2shell/php2shell-lab.git
cd php2shell-lab
docker compose up -d --build          # 初回ビルドでpin済みbase imageを取得
```

`http://localhost:18901` を開き、ユーザー `demo`（パスワード `change-me-demo`）でログインするとアプリに到達します。ここから先はブラックボックスです。

独自のフラグ値とアカウントパスワードを設定する場合：`private.ini` を編集後、

```bash
docker compose build \
  --build-arg FLAG_LOCAL=flag{...} \
  --build-arg FLAG_PROOF=flag{...}
```

運用コマンド：

```bash
docker compose logs -f                # ログ監視
docker compose down -v && docker compose up -d --build   # 完全リセット（リークウィンドウ再開放）
docker compose down                   # 停止・リソース解放
```

フラグはOverTheWire方式です：

| フラグ | パス | 読取可能ユーザー |
|------|------|-------------|
| `local.txt` | `/var/www/local.txt` | `www-data` |
| `proof.txt` | `/root/proof.txt` | `root` のみ |

## 難所

- **アプリ級ではなくエンジン級。** SQLiなし、アップロード回避パズルなし。本機はPHP2Shell影響範囲内のPHPバージョンで動作しており、残りはすべて攻撃者の実力次第です。
- **設計上リークするが、起動直後だけ。** 重要度上位のヒープ開示プリミティブには、起動直後の狭い生存ウィンドウがあります。同種のラボの多くは扉を開けたままですが、本機は意図的に閉じます。定石通り：まずリーク、その後に偵察。ウィンドウ内で費やした閲覧時間は、次回の `up` まで戻りません。
- **`proof.txt` への2つの権限昇格経路**（洗練度に差あり）。少なくとも1つは実射検証済み。本番機群にスケールするのは1つだけです。
- **実射による較正済み。** 本ターゲットは自律型pentestエージェントとの多ラウンド攻防で開発(全トランスクリプトは docs/SOLUTION.md)。較正環境(Apple M5 Ultra・ローカルQwen3.8-Flash-Next)でのエージェント最速記録は両フラグ約15分。他のモデル/環境では速くも遅くもあり得る点にご注意。人間の現実的な想定は2〜4時間です。

## プラットフォーム注意（issueを立てる前に一読）

PHP2Shellの公開済み**RCE着弾**エクスプロイトはx86_64カーネルヒープ配置を前提とします。**Apple Silicon**（およびWindows-on-ARM等のaarch64ホスト）ではリーク系プリミティブは着弾するものの、RCE着弾は再現しません。これはamd64エミュレーション下・arm64ネイティブ・アドバイザリ著者の参照構成（[calif.io/php2shell](https://calif.io/php2shell)、x86_64 Linux）のいずれでも検証済みです。ARMホストでは想定解は**開示チェーン**でありRCEチェーンではありません（開示チェーンはx86_64を含む全環境で動作）。詳細：[docs/PLATFORM-MATRIX.md](docs/PLATFORM-MATRIX.md)。

## リソース

- CPU 2 / RAM 1 GB上限 — 他のラボ機と並列起動しても安全です。
- 実環境のシークレットは焼き込みなし。全認証情報は偽物です。

## 解答

全キルチェーン・オフセット・ラウンド別攻撃トランスクリプト：[docs/SOLUTION.md](docs/SOLUTION.md)。

## ライセンス

MIT — 改変・フォーク・研修利用歓迎（帰属表記は歓迎、必須ではありません）。
