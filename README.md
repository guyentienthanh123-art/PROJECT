📌 プロジェクト概要

このアプリは、日本語の 語彙・文法・漢字 を効率的に学習・管理するための タスク管理型学習アプリ です。
ユーザーはログイン後、学習項目を追加・編集・削除し、学習状況（勉強中・覚えた・忘れた）を管理できます。

目的

自分自身の日本語学習を効率化する

新卒就職活動のポートフォリオ として技術力を示す

🎯 開発の背景

日本語学習者として、日々出会う新しい単語や文法をノートやメモアプリでバラバラに管理することに不便さを感じていました。
そこで、**「自分だけの単語帳を、いつでもどこでも直感的に使える形で作る」**ことを目標に本アプリを開発。
学習効率向上のツールであると同時に、PHP・JavaScript・SQL・セッション管理などのWeb開発技術を実践的に習得しました。

🚀 主な機能

🔑 ユーザーログイン・ログアウト（PHPセッション管理）

➕ 学習項目（語彙・文法・漢字）の追加／編集／削除

📊 学習状況の管理（勉強中／覚えた／忘れた）

🔎 検索・フィルタ機能

🎨 GlassmorphismによるモダンUI、レスポンシブ対応

🏗️ システム構成図
flowchart LR
  U[ユーザー（ブラウザ）] -->|HTTP/HTTPS| FE[フロントエンド\nHTML/CSS/JavaScript]
  FE -->|Fetch API| BE[バックエンド\nPHP（CRUD/セッション）]
  BE -->|SQL| DB[(データベース\nMySQL / SQLite)]

🗄️ ER図
erDiagram
  USERS ||--o{ ITEMS : has
  USERS {
    int id PK
    varchar username
    varchar password_hash
    datetime created_at
  }
  ITEMS {
    int id PK
    int user_id FK
    enum type "vocabulary|grammar|kanji"
    text jp_text
    text meaning
    text example
    enum level "studying|learned|forgotten"
    datetime created_at
  }

⚙️ セットアップ & 実行方法
必要環境

PHP 8+, MySQL または SQLite

XAMPP / MAMP / Replit

インストール
git clone https://github.com/guyentienthanh123-art/PROJECT.git
cd PROJECT

データベース設定

db_schema.sql をインポート

db_connect.php を環境に合わせて編集

起動

Apache/MySQL を起動

ブラウザで http://localhost/index.html を開く

🧠 学んだこと・得られたスキル

フルスタック実装：フロント～バック～DB を一気通貫で構築

非同期処理：Fetch API によるスムーズなCRUDとUX向上

UI/UX設計：学習効率を意識した情報設計・検索/フィルタ導線

DB設計：ER図によるスキーマ設計とデータ整合性

開発プロセス：要件定義 → 設計 → 実装 → テスト → デプロイ

担当範囲：本プロジェクトは個人開発として、設計・実装・テストのすべてを担当。
👉 この経験を活かし、御社の実務で価値を出しながら成長していきたいと考えています。

🙋 開発者情報

👤 グエン・ティエン・タイン – 日本電子専門学校 情報システム開発科

📧 Email: 24jy0209@jec.ac.jp

🌐 GitHub: https://github.com/guyentienthanh123-art/PROJECT

📄 ライセンス

MIT License
