## 43. Items バリデーション

+ `app/Http/Requests/StoreItemRequest.php`を編集<br>

```php:StoreItemRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'max:255'],
            'memo' => ['required', 'max:255'],
            'price' => ['required', 'numeric'],
        ];
    }
}
```

### コンポーネントを使ってみる

```
Pages/Auth/Login.vueを参考にコンポーネントを使ってみる

Components/ValidationErrors.vue

computed() ・・ Vueの機能
リアルタイムで検知、計算する

usePage()・・inertiaの機能
マニュアルはShared data内
頭にuseとつくのは合成関数（関わる機能をまとめてカプセル化した関数）
```

+ [Shared data](https://inertiajs.com/shared-data) <br>

+ `$ touch resources/js/Components/ValidationErrors.vue`を実行<br>

+ `resources/js/Components/ValidationErrors.vue`を編集<br>

```vue:ValidationErrors.vue
<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
const errors = computed(() => usePage().props.value.errors);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);
</script>

<template>
  <div v-if="hasErrors">
    <div class="font-medium text-red-600">問題が発生しました。</div>

    <ul class="mt-3 list-disc list-inside text-sm text-red-600">
      <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
    </ul>
  </div>
</template>
```

+ `resources/js/Pages/Items/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { reactive } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue' // 追加


const form = reactive({
    name: null,
    memo: null,
    price: null
})

const storeItem = () => {
    Inertia.post("/items", form)
}
</script>

<template>

    <Head title="商品登録" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                商品登録
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- 追加 -->
                        <BreezeValidationErrors class="mb-4" />
                        <section class="text-gray-600 body-font relative">
                            <form @submit.prevent="storeItem">
                                <div class="container px-5 py-8 mx-auto">
                                    <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                        <div class="flex flex-wrap -m-2">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="name"
                                                        class="leading-7 text-sm text-gray-600">商品名</label>
                                                    <input type="text" id="name" name="name" v-model="form.name"
                                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                </div>
                                            </div>
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="memo" class="leading-7 text-sm text-gray-600">メモ</label>
                                                    <textarea id="memo" name="memo" v-model="form.memo"
                                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                                </div>
                                            </div>
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="price"
                                                        class="leading-7 text-sm text-gray-600">商品価格</label>
                                                    <input type="number" id="price" name="price" v-model="form.price"
                                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                </div>
                                            </div>
                                            <div class="p-2 w-full">
                                                <button
                                                    class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">商品登録</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```

## 43. Items フラッシュメッセージ

+ `app/Http/Middleware/HandleInertiaRequests.php`を編集<br>

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

            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'status' => fn () => $request->session()->get('status'), // 追加
            ]
        ]);
    }
}
```

+ `app/Http/Controllers/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Items/Index', ['items' => Item::select('id', 'name', 'price', 'is_selling')->get()]);
    }

    public function create()
    {
        return Inertia::render('Items/Create');
    }

    public function store(StoreItemRequest $request)
    {
        Item::create([
            'name' => $request->name,
            'memo' => $request->memo,
            'price' => $request->price,
        ]);

        // 編集
        return to_route('items.index')
            ->with([
                'message' => '登録しました。',
                'status' => 'success',
            ]);
    }
}
```

+ `$ touch resources/js/Components/FlashMessage.vue`を実行<br>

+ `resources/js/Components/FlashMessage.vue`を編集<br>

```vue:FlashMessage.vue
<script setup>
</script>

<template>
  <div v-if="$page.props.flash.status === 'success'" class="bg-blue-300 text-white p-4">
    {{ $page.props.flash.message }}
  </div>
</template>
```

+ `resources/js/Pages/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue'; // 追加

defineProps({
  items: Array,
})
</script>

  <template>

  <Head title="商品一覧" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        商品一覧
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <section class="text-gray-600 body-font">
              <div class="container px-5 py-8 mx-auto">
                <!-- 追加 -->
                <FlashMessage />
                <div class="flex pl-4 my-4 lg:w-2/3 w-full mx-auto">
                  <Link as="button" :href="route('items.create')" class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">商品登録</Link>
                </div>
                <div class="lg:w-2/3 w-full mx-auto overflow-auto">
                  <table class="table-auto w-full text-left whitespace-no-wrap">
                    <thead>
                      <tr>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                          ID</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          商品名</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          価格</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          スタータス</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in items" :key="item.id">
                        <td class="px-4 py-3">{{ item.id }}</td>
                        <td class="px-4 py-3">{{ item.name }}</td>
                        <td class="px-4 py-3">{{ item.price }}</td>
                        <td class="px-4 py-3 text-lg">{{ item.is_selling }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>
```
