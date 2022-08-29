# セクション2: Inertia、Vue3、script setup（compostionAPI）

## 10. Laravel Breeze（Inertia）のインストール(認証系)<br>

+ [Laravel Breeze](https://readouble.com/laravel/9.x/ja/starter-kits.html#laravel-breeze) <br>

### Inertiaの概要

Laravelなどサーバ側フレームワークの機能を活かしながら、SPAをつくるためのライブラリ(SinglePageApplication)<br>
Vue.js、React、Svelteなどにも対応。<br>

[Inertia公式ページ](https://inertiajs.com) <br>

+ `$ composer require laravel/breeze:^1 --dev`を実行<br>

+ `$ php artisan breeze:install vue`を実行<br>

+ `$ npm install && npm run dev`を実行<br>

+ 一旦 `Control + c`で落とす<br>

## 11. 簡易サーバー動作確認(npm run devなど)

+ `$ npm run dev`を実行(vite起動) ※ Dockerの場合必要ないかも？<br>

+ 本番環境にするときは `$ npm run build`を実行する<br>
