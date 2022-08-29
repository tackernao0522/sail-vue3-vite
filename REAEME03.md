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

+ `$ npm run dev`を実行(vite起動)<br>

+ 本番環境にするときは `$ npm run build`を実行する<br>

## 13. 生成されたファイルの確認(Inertia::render)

### ファイルを見てみる

#### LaravelBladeとInertiaの違い

サーバーサイド<br>

Laravel Balde<br>
  view('viewファイル名', compact(変数名))<br>

Inertia<br>
  Inertia::render('コンポーネント名', [変数名])

クライアントサイド<br>

Laravel Balde<br>
  `<a href="">リンク</a>` <br>
  ページ内全ての情報を再読み込みする HTML<br>

Inertia<br>
  `<Link href="">` <br>
  部分的に読み込む JSON<br>
  読み込む量が少ない = 描画速度が早い<br>
  SPA（Single Page Application）<br>

## 14. aタグとLinkコンポーネントの違い

### Linkを確認してみる

+ `routes/web.php`を編集<br>

```php:web.php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 追加
Route::get('/inertia-test', function () {
    return Inertia::render('InertiaTest');
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/auth.php';
```

+ `$ touch resources/js/pages/InertiaTest.vue`を実行<br>

+ `resources/js/Pages/InertiaTest.vue`を編集<br>

```vue:InertiaTest.vue
<script setup>
import { Link } from "@inertiajs/inertia-vue3";
</script>

<template>
  Inertiaテストです。<br />
  <a href="/">aタグ経由です</a> <br>
  <Link href="/">Link経由です</Link>
</template>
```