## 39. Items（商品・サービス）index

+ [TAILBLOCKS](https://tailblocks.cc/) <br>

+ `resources/js/Pages/Items/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
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
                <div class="flex pl-4 my-4 lg:w-2/3 w-full mx-auto">
                  <button
                    class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">Button</button>
                </div>
                <div class="lg:w-2/3 w-full mx-auto overflow-auto">
                  <table class="table-auto w-full text-left whitespace-no-wrap">
                    <thead>
                      <tr>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                          Plan</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          Speed</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          Storage</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          Price</th>
                        <th
                          class="w-10 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br">
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="px-4 py-3">Start</td>
                        <td class="px-4 py-3">5 Mb/s</td>
                        <td class="px-4 py-3">15 GB</td>
                        <td class="px-4 py-3 text-lg text-gray-900">Free</td>
                        <td class="w-10 text-center">
                          <input name="plan" type="radio">
                        </td>
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

+ `app/Http/Controllers/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        dd(Item::select('id', 'name', 'price', 'is_selling')->get());
        return Inertia::render('Items/Index', ['items' => Item::all()]);
    }
}
```

+ `app/Http/Controllers/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Items/Index', ['items' => Item::select('id', 'name', 'price', 'is_selling')->get()]);
    }
}
```

+ `resources/js/Pages/Items/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';

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
                <div class="flex pl-4 my-4 lg:w-2/3 w-full mx-auto">
                  <button
                    class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">Button</button>
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

## 40. Items createへのリンク

+ `$ cp resources/js/Pages/Dashboard.vue resources/js/Pages/Items/Create.vue`を実行<br>

+ `resources/js/Pages/Items/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
</script>

<template>
    <!-- 編集 -->
    <Head title="商品登録" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <!-- 編集 -->
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                商品登録
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        You're logged in!
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```

+ `resources/js/Pages/Items/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3'; // 編集

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
                <div class="flex pl-4 my-4 lg:w-2/3 w-full mx-auto">
                  <!-- 編集 -->
                  <Link as="button" :href="route('items.create')" class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">商品登録</Link>
                  <!-- ここまで -->
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

+ `app/Http/Controllers/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers;

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
}
```