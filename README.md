# 勤怠管理アプリ案件

## 環境構築

## Dockerビルド

```
git clone https://github.com/sekirubeg/am-mogi.git
docker-compose up -d --build
```

※MySQL は OS によって起動しない場合があります。必要に応じて docker-compose.yml を各自の環境に合わせて編集してください。

## Laravel 環境構築

```
docker-compose exec php bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```
権限取得のため以下のコマンドも入力してください
```
cd /var/www/storage/
chmod -R 777 .
```
コピーした.envファイルに以下の情報を入力してください
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

## mail認証のためのmailtrapの設定

### Mailtrap のアカウント作成
https://mailtrap.io/ にアクセスし、無料アカウントを作成してください。<br>
その後ダッシュボードにログインし、Inbox を作成してください<br>
そしてInbox の「SMTP Settings」を開き、Laravel 用の接続情報を確認してください。

### .env にSMTP情報を設定
Mailtrapの設定にある「Laravel 7.x and 8.x」用の情報を、.env に貼り付けます。
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
```
*MAIL_USERNAME と MAIL_PASSWORD は Mailtrap のダッシュボードから取得してください。


## test 環境構築
```
docker-compose exec mysql bash
mysql -u root -p
> CREATE DATABASE test;
> SHOW DATABASES;
```
### PHP.Unitによるテスト

### PHP.Unitによるテストをを行うため以下のコマンドを実行してください
```
php artisan key:generate --env=testing
php artisan config:clear
php artisan migrate --env=testing
php artisan test
```

## 使用技術
```
PHP 7.4.9
Laravel 8.83.8
MySQL 8.0.40
```
## URL
```
開発環境
  管理者ログイン画面：http://localhost/admin/login
    ・ 管理者のメールアドレス：admin@example.com
    ・ 管理者のパスワード：sekirubeg
  一般ログイン画面：http://localhost/login
phpMyAdmin: http://localhost:8080/
```
## ER図
![ER図](ER.png)
