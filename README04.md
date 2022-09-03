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

## 21. フォーム（create）その2

+ `resources/js/Pages/Inertia/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { Inertia } from "@inertiajs/inertia";
import { reactive } from "vue";

const form = reactive({
  title: null,
  content: null,
});

const submitFunction = () => {
  Inertia.post("/inertia", form);
};
</script>

<template>
  <form @submit.prevent="submitFunction">
    <input type="text" name="title" v-model="form.title" /><br />
    <input type="text" name="content" v-model="form.content" /><br />
    <button>送信</button>
  </form>
</template>
```

## 22. バリデーション

+ [Inertia Validation](https://inertiajs.com/validation) <br>

```
クライアントサイド(ブラウザ側)
  リアルタイムで検知できる
  しっかり対応するならvee-validateなどのライブラリも要検討
  開発ツールなどで無効化できる

サーバーサイド
  Laravelのバリデーションがそのまま使える
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
        $request->validate(([
            'title' => ['required', 'max:20'],
            'content' => ['required']
        ]));

        $inertiaTest = new InertiaTest;
        $inertiaTest->title = $request->title;
        $inertiaTest->content = $request->content;
        $inertiaTest->save();

        return to_route('inertia.index');
    }
}
```

+ `resources/js/Pages/Inertia/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { Inertia } from "@inertiajs/inertia";
import { reactive } from "vue";

defineProps({
  errors: Object,
});

const form = reactive({
  title: null,
  content: null,
});

const submitFunction = () => {
  Inertia.post("/inertia", form);
};
</script>

<template>
  <form @submit.prevent="submitFunction">
    <input type="text" name="title" v-model="form.title" /><br />
    <div v-if="errors.title">{{ errors.title }}</div>
    <input type="text" name="content" v-model="form.content" /><br />
    <div v-if="errors.content">{{ errors.content }}</div>
    <button>送信</button>
  </form>
</template>
```

## 24. フラッシュメッセージ

+ [Shared data](https://inertiajs.com/shared-data) <br>

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
        $request->validate(([
            'title' => ['required', 'max:20'],
            'content' => ['required']
        ]));

        $inertiaTest = new InertiaTest;
        $inertiaTest->title = $request->title;
        $inertiaTest->content = $request->content;
        $inertiaTest->save();

        return to_route('inertia.index')
            ->with([
                'message' => '登録しました。'
            ]);
    }
}
```

+ `app/Http/middleware/HandleInertiaRequests.php`を編集<br>

```php:HandleInertiaRequests.php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            // 追加
            'flash' => [
                'message' => fn () => $request->session()->get('message')
            ],
        ]);
    }
}
```

+ `resources/js/Pages/Inertia/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
</script>

<template>
  <div v-if="$page.props.flash.message" class="bg-blue-300">
    {{ $page.props.flash.message }}
  </div>
  ああああああ
</template>
```

## 25. Indexにv-forを追加してみる

+ `app/Http/Controlers/InertiaTestController.php`を編集<br>

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
        return Inertia::render('Inertia/Index', ['blogs' => InertiaTest::all()]);
    }

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
        $request->validate(([
            'title' => ['required', 'max:20'],
            'content' => ['required']
        ]));

        $inertiaTest = new InertiaTest;
        $inertiaTest->title = $request->title;
        $inertiaTest->content = $request->content;
        $inertiaTest->save();

        return to_route('inertia.index')
            ->with([
                'message' => '登録しました。'
            ]);
    }
}
```

+ `resources/js/Pages/Inertia/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
defineProps({
  blogs: Array
})
</script>

<template>
  <div v-if="$page.props.flash.message" class="bg-blue-300">
    {{ $page.props.flash.message }}
  </div>
  <ul>
    <li v-for="blog in blogs" :key="blog.id">
      件名: {{ blog.title }}
      本文: {{ blog.content }}
    </li>
  </ul>
  ああああああ
</template>
```

+ `resources/js/Pages/Inertia/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import { Link } from '@inertiajs/inertia-vue3'
defineProps({
  blogs: Array
})
</script>

<template>
  <div v-if="$page.props.flash.message" class="bg-blue-300">
    {{ $page.props.flash.message }}
  </div>
  <ul>
    <li v-for="blog in blogs" :key="blog.id">
      件名:
      <Link class="text-blue-400" :href="route('inertia.show', { id: blog.id })">{{ blog.title }}</Link>
      本文: {{ blog.content }}
    </li>
  </ul>
  ああああああ
</template>
```
