# Dockerで作る家計簿アプリ & LAMP環境

このプロジェクトは、Dockerを使用して構築した本格的なLAMP環境（Linux, Apache, MySQL, PHP）に、リバースプロキシ（Nginx）を追加したWebアプリケーションの学習用リポジトリです。

##  システム構成 (Infrastructure)
現在、以下のコンテナを連携させて動作させています。

- **Nginx (Reverse Proxy)**: ポート80で全てのアクセスを受け、WebアプリとDB管理画面へ振り分け（予定）。
- **Apache + PHP 8.2**: 家計簿アプリのロジックを実行。
- **MySQL 8.0**: データの永続化設定（Volumeマウント）済み。
- **phpMyAdmin**: ブラウザベースのデータベース管理。



##  使い方 (Usage)
```bash
# クローン後、以下のコマンドで環境が立ち上がります
sudo docker compose up -d
