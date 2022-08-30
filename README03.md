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

## 15. Link 名前付きルート


### 名前付きルート　その1

ziggyラブラリにより名前付きルートが使える<br>

+ `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\InertiaTestController;; // 追加
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

Route::get('/inertia-test', function () {
    return Inertia::render('InertiaTest');
});

Route::get('/inertia/index', [InertiaTestController::class, 'index'])->name('inertia.index'); // 追加

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
+ `$ php artisan make:controller InertiaTestController`を実行<br>

+ `app/Http/Controllers/InertiaTestController.php`を編集<br>

```php:InertiaTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InertiaTestController extends Controller
{
    public function index ()
    {
        return Inertia::render('Inertia/Index');
    }
}
```

+ `mkdir resources/js/Pages/Inertia && touch $_/Index.vue`を実行<br>

+ `resources/js/Pages/Inertia/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
</script>

<template>ああああああ</template>
```

+ `resources/js/Pages/InertiaTest.vue`を編集<br>

```vue:InertiaTest.vue
<script setup>
import { Link } from "@inertiajs/inertia-vue3";
</script>

<template>
  Inertiaテストです。<br />
  <a href="/">aタグ経由です</a> <br />
  <Link href="/">Link経由です</Link> <br />
  <Link :href="route('inertia.index')">名前付きルートの確認です</Link>
</template>
```

## 16. Link ルートパラメータ

+ `resources/js/Pages/InertiaTest.vue`を編集<br>

```vue:InertiaTest.vue
<script setup>
import { Link } from "@inertiajs/inertia-vue3";
</script>

<template>
  Inertiaテストです。<br />
  <a href="/">aタグ経由です</a> <br />
  <Link href="/">Link経由です</Link> <br />
  <Link :href="route('inertia.index')">名前付きルートの確認です</Link> <br />
  <Link :href="route('inertia.show', { id: 1 })"
    >ルートパラメータのテストです</Link
  > // 追加
</template>
```

+ `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\InertiaTestController;
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

Route::get('/inertia-test', function () {
    return Inertia::render('InertiaTest');
});

Route::get('/inertia/index', [InertiaTestController::class, 'index'])->name('inertia.index');
Route::get('/inertia/show/{id}', [InertiaTestController::class, 'show'])->name('inertia.show'); // 追加

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

+ `app/Http/Controllers/InertiaTestController.php`を編集<br>

```php:InertiaTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InertiaTestController extends Controller
{
    public function index()
    {
        return Inertia::render('Inertia/Index');
    }

    public function show($id)
    {
        // dd($id);
        return Inertia::render('Inertia/Show', ['id' => $id]);
    }
}
```

+ `$ touch resources/js/Pages/Inertia/Show.vue`を実行<br>

+ `resources/js/Pages/Inertia/Show.vue`を編集<br>

```vue:Show.vue
<script setup>
defineProps({
  id: String,
});
</script>

<template>
  {{ id }}
</template>
```

## 17. Linkコンポーネントでstore保存 その1

### Linkコンポーネントの属性

```
Vue-routerのrouter-linkのようにいくつかの属性を設定できる

type="button" // buttonリンク

as="button" // button

method="post" // メソッド変更

:data="{}" // 送信リクエストにデータ追加

オブジェクトかFormDataインスタンス

:headers="{}" 追加するHTTPヘッダー指定

replace History 履歴書書き換え

preserve-state フォーム入力値を維持

preserve-scroll 移動前と同じスクロール位置を保持
```
### 部分リロード

```
:only="{}" // 特定のデータだけ読み込みたい場合
(通信量が節約される。)
Laravel側でデータは取得しているので、サーバー側の負荷は変わらない。
Laravel側も必要な時だけ取得するなら fn()を挟むとok
return Inertia::render('コンポーネント', [key => fn() => value]);
```

+ `$ php artisan make:model InertiaTest -m`を実行<br>

+ `$ php artisan migrate`を実行<br>

## 18. Linkコンポーネントでstore保存 その2

+ `resources/js/Pages/InertiaTest.vue`を編集<br>

```vue:InertiaTest.vue
<script setup>
import { Link } from "@inertiajs/inertia-vue3";
import { ref } from 'vue'

const newTitle = ref('')
const newContent = ref('')
</script>

<template>
  Inertiaテストです。<br />
  <a href="/">aタグ経由です</a> <br />
  <Link href="/">Link経由です</Link> <br />
  <Link :href="route('inertia.index')">名前付きルートの確認です</Link> <br />
  <Link :href="route('inertia.show', { id: 50 })"
    >ルートパラメータのテストです</Link
  >

  <div class="mb-8"></div>
  <input type="text" name="newTitle" v-model="newTitle" />{{ newTitle }}<br />
  <input type="text" name="newContent" v-model="newContent" />{{ newContent }}<br />
  <Link
    as="button"
    method="post"
    :href="route('inertia.store')"
    :data="{ title: newTitle, content: newContent }"
    >DB保存テスト</Link
  >
</template>
```

+ `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\InertiaTestController;
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

Route::get('/inertia-test', function () {
    return Inertia::render('InertiaTest');
});

Route::get('/inertia/index', [InertiaTestController::class, 'index'])->name('inertia.index');
Route::post('/inertia', [InertiaTestController::class, 'store'])->name('inertia.store'); // 追加
Route::get('/inertia/show/{id}', [InertiaTestController::class, 'show'])->name('inertia.show');

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

+ `app/Http/Controllers/InertiaTestController.php`を編集<br>

```php:InertiaTestController.php
<?php

namespace App\Http\Controllers;

use App\Models\InertiaTest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InertiaTestController extends Controller
{
    public function index()
    {
        return Inertia::render('Inertia/Index');
    }


    public function show($id)
    {
        // dd($id);
        return Inertia::render('Inertia/Show', ['id' => $id]);
    }

    public function store(Request $request)
    {
        $inertiaTest = new InertiaTest;
        $inertiaTest->title = $request->title;
        $inertiaTest->content = $request->content;
        $inertiaTest->save();

        return to_route('inertia.index');
    }
}
```
