## 26. イベントコールバック（onBefore） その1

+ [Manual visits](https://inertiajs.com/manual-visits) <br>

```
処理の前後にフックさせて処理する仕組み
削除 -> 本当に削除しますか？ と表示させるなど
Inertiaマニュアル manualの後半

Inertia.post('/users', data, {
  onBefore: (visit) => {},
  onStart: (visit) => {},
  onProgress: (progress) => {},
  onSuccess: (page) => {},
  onError: (errors) => {},
  onCancel: () => {},
  onFinish: visit => {},
})
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
Route::get('/inertia/create', [InertiaTestController::class, 'create'])->name('inertia.create');
Route::post('/inertia', [InertiaTestController::class, 'store'])->name('inertia.store');
Route::get('/inertia/show/{id}', [InertiaTestController::class, 'show'])->name('inertia.show');
Route::delete('/inertia/{id}', [InertiaTestController::class, 'delete'])->name('inertia.delete');

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
        return Inertia::render('Inertia/Show', ['id' => $id, 'blog' => InertiaTest::findOrFail($id)]); // 編集
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

+ `resources/js/Pages/Inertia/Show.vue`を編集<br>

```vue:Show.vue
<script setup>
defineProps({
  id: String,
  blog: Object // 一件のみの取得の場合はObjectにする
});
</script>

<template>
  {{ id }}<br>
  {{ blog.title }} // 追加
</template>
```

## 27. イベントコールバック（onBefore） その2

+ `resources/js/Pages/Inertia/Show.vue`を編集<br>

```vue:Show.vue
<script setup>
import { Inertia } from '@inertiajs/inertia'; // 追加

defineProps({
  id: String,
  blog: Object
});

// 追加
const deleteConfirm = id => {
  // console.log(id)
  Inertia.delete(`/inertia/${id}`, {
    onBefore: () => confirm('本当に削除しますか？')
  })
}
</script>

<template>
  {{ id }}<br>
  {{ blog.title }}<br>
  <button @click="deleteConfirm(blog.id)">削除</button> // 追加
</template>
```

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
        return Inertia::render('Inertia/Show', ['id' => $id, 'blog' => InertiaTest::findOrFail($id)]);
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

    // 追加
    public function delete($id)
    {
        $book = InertiaTest::findOrFail($id);
        $book->delete();

        return to_route('inertia.index')
            ->with([
                'message' => '削除しました。'
            ]);
    }
}
```

## 28. スロット・名前付きスロット

### スロット Vue.jsの機能

```
コンポーネントの中に別のコンポーネントを差し込む

① Auth/Login.vue <= (import) ② Layouts/Guest.vue

①
<Guest>
ここは自由に書ける
</Guest>

②
<slot />

複数差し込む場合は名前付きスロットなど

③ Dashboard.vue <= (import) ④ Layouts/Authenticated.vue

③
<Authenticated>
  <template #header>
  ここは自由に書ける
  </template>
  ここは自由に書ける

</Authenticated>

④
<slot name="header" />

<slot />
```

## 29. 他のコンポーネントを使ってみる

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

Route::get('/component-test', function () {
    return Inertia::render('ComponentTest');
});

Route::get('/inertia/index', [InertiaTestController::class, 'index'])->name('inertia.index');
Route::get('/inertia/create', [InertiaTestController::class, 'create'])->name('inertia.create');
Route::post('/inertia', [InertiaTestController::class, 'store'])->name('inertia.store');
Route::get('/inertia/show/{id}', [InertiaTestController::class, 'show'])->name('inertia.show');
Route::delete('/inertia/{id}', [InertiaTestController::class, 'delete'])->name('inertia.delete');

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

+ `$ touch resources/js/Pages/ComponentTest.vue`を実行<br>

+ `resources/js/Pages/ComponentTest.vue`を編集<br>

```vue:ComponentTest.vue
<script setup>
import GuestLayout from '@/Layouts/Guest.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'
</script>

<template>
  <GuestLayout>
    何か文字を書く
  </GuestLayout>
</template>
```

+ `http://localhost/component-test` にアクセスしてみる<br>

+ `resources/js/Pages/ComponentTest.vue`を編集<br>

```vue:ComponentTest.vue
<script setup>
import GuestLayout from '@/Layouts/Guest.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'
</script>

<template>
  <GuestLayout>
    <Label value="件名">タイトル</Label> // Lavel.vueを見てみると valueがあれば"件名"になりなければ<slot />の方のタイトルが表示される
    <Input></Input>
  </GuestLayout>
</template>
```

+ `resources/js/Pages/ComponentTest.vue`を編集<br>

```vue:ComponentTest.vue
<script setup>
import GuestLayout from '@/Layouts/Guest.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'
</script>

<template>
  <GuestLayout>
    <Label value="件名">タイトル</Label>
    <Input modelValue="初期値が入ります"></Input> // 子のInput.vueへmodelValueを渡している
  </GuestLayout>
</template>
```
