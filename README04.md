## 19. Vue.js3の概要

### Vue.js SingleFileComponent

```
HTML + JS + CSS を１つのファイル（*.vue)
<templete> よく使うディレクティブ
v-if, v-else, v-else-if
v-for (v-for in), v-show
v-text, v-html
v-on (省略形は@) イベント
v-bind (省略形は:) 紐付け
v-model フォーム用
v-cloak
v-slot
トランジション関連
```

### Vue.js CompositionAPI(setup)

```
<script setup>
必要な機能はimportする
import { Link } from '@inertia/inertia-vue3' //
inertia側
import { ref } from 'vue' // vue側

コントローラからの受け渡し
defineProps({ id: String })

リアクティブな変数は refかreactiveで囲む
メソッドはJSを同じように書ける
```

### 関連マニュアル・講座

+ [Vue.js3 ドキュメント](https://v3.ja.vuejs.org/guide/introduction.html) <br>

+ [Vue.js3 APIリファレンス](https://v3.ja.vuejs.org/api/)<br>

## 20. フォーム（create）その1

+ [Inertia Forms](https://inertiajs.com/forms) <br>

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
Route::get('/inertia/create', [InertiaTestController::class, 'create'])->name('inertia.create'); // 追加
Route::post('/inertia', [InertiaTestController::class, 'store'])->name('inertia.store');
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

    // 追加
    public function create()
    {
        return Inertia::render('Inertia/Create');
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

+ `$ touch resources/js/Pages/Inertia/Create.vue`を実行<br>