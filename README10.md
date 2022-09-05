## 49. Item Update 更新対応

+ `resources/js/Pages/Items/Edit.vue`を編集<br>

```vue:Edit.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { reactive } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'

const props = defineProps({
    item: Object
})

const form = reactive({
    id: props.item.id,
    name: props.item.name,
    memo: props.item.memo,
    price: props.item.price,
    is_selling: props.item.is_selling,
})

// 編集
const updateItem = id => {
    Inertia.put(route('items.update', { item: id }), form)
}
// ここまで
</script>

<template>

    <Head title="商品編集" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                商品編集
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <BreezeValidationErrors class="mb-4" />
                        <section class="text-gray-600 body-font relative">
                            <!-- 編集 -->
                            <form @submit.prevent="updateItem(form.id)">
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
                                                <div class="relative">
                                                    <label for="is_sellign"
                                                        class="leading-7 text-sm text-gray-600">ステータス</label>
                                                    <input type="radio" id="is_selling" name="is_selling"
                                                        v-model="form.is_selling" value="1">
                                                    <label class="ml-2 mr-4">販売中</label>
                                                    <input type="radio" id="is_selling" name="is_selling"
                                                        v-model="form.is_selling" value="0">
                                                    <label class="ml-2 mr-4">停止中</label>
                                                </div>
                                            </div>
                                            <div class="p-2 w-full">
                                                <button
                                                    class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">更新する</button>
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

+ `$ php artisan make:request UpdateItemRequest`を実行<br>

+ `app/Http/Requests/UpdateItemRequest.php`を編集<br>

```php:UpdateItemRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
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
            'name' => ['required', 'max:50'],
            'memo' => ['required', 'max:255'],
            'price' => ['required', 'numeric'],
            'is_selling' => ['required', 'boolean']
        ];
    }
}
```

+ `app/Http/Controllers/ItemController.php`を編集<br>

```php:ItemController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
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

        return to_route('items.index')
            ->with([
                'message' => '登録しました。',
                'status' => 'success',
            ]);
    }

    public function show(Item $item)
    {
        // dd($item);

        return Inertia::render('Items/Show', ['item' => $item]);
    }

    public function edit(Item $item)
    {
        // dd($item);

        return Inertia::render('Items/Edit', ['item' => $item]);
    }

    // 追加
    public function update(UpdateItemRequest $request, Item $item)
    {
        // dd($item->name, $request->name);
        $item->name = $request->name;
        $item->memo = $request->memo;
        $item->price = $request->price;
        $item->is_selling = $request->is_selling;
        $item->save();

        return to_route('items.index')
            ->with([
                'message' => '更新しました。',
                'status' => 'success',
            ]);
    }
}
```

## 50. Item Delete 削除処理

+ `resources/js/Pages/Items/Show.vue`を編集<br>

```vue:Show.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import nl2br from '@/common'
import { Inertia } from '@inertiajs/inertia';

defineProps({
  item: Object
})

const deleteItem = id => {
  Inertia.delete(route('items.destroy', { item: id }), {
    onBefore: () => confirm('本当に削除しますか？')
  })
  // Inertial.delete(`/items/${id}`, {
  //   onBefore: () => confirm('本当に削除しますか？')
  // })
}
</script>

<template>

  <Head title="商品詳細" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        商品詳細
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <section class="text-gray-600 body-font relative">
              <div class="container px-5 py-8 mx-auto">
                <div class="lg:w-1/2 md:w-2/3 mx-auto">
                  <div class="flex flex-wrap -m-2">
                    <div class="p-2 w-full">
                      <div class="relative">
                        <label for="name" class="leading-7 text-sm text-gray-600">商品名</label>
                        <div id="name"
                          class="w-ful bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                          {{ item.name }}
                        </div>
                      </div>
                    </div>
                    <div class="p-2 w-full">
                      <div class="relative">
                        <label for="memo" class="leading-7 text-sm text-gray-600">メモ</label>
                        <div id="memo" v-html="nl2br(item.memo)"
                          class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                    </div>
                    <div class="p-2 w-full">
                      <div class="relative">
                        <label for="price" class="leading-7 text-sm text-gray-600">商品価格</label>
                        <div id="price"
                          class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                          {{ item.price }}
                        </div>
                      </div>
                    </div>
                    <div class="p-2 w-full">
                      <div class="relative">
                        <label for="status" class="leading-7 text-sm text-gray-600">ステータス</label>
                        <div id="status"
                          class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                          <span v-if="item.is_selling === 1">販売中</span>
                          <span v-if="item.is_selling === 0">停止中</span>
                        </div>
                      </div>
                    </div>
                    <div class="p-2 w-full">
                      <Link as="button" :href="route('items.edit', { item: item.id })"
                        class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">
                      編集する</Link>
                    </div>
                    <div class="mt-20 p-2 w-full">
                      <button @click="deleteItem(item.id)"
                        class="flex mx-auto text-white bg-red-500 border-0 py-2 px-8 focus:outline-none hover:bg-red-600 rounded text-lg">
                        削除する</button>
                    </div>
                  </div>
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

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
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

        return to_route('items.index')
            ->with([
                'message' => '登録しました。',
                'status' => 'success',
            ]);
    }

    public function show(Item $item)
    {
        // dd($item);

        return Inertia::render('Items/Show', ['item' => $item]);
    }

    public function edit(Item $item)
    {
        // dd($item);

        return Inertia::render('Items/Edit', ['item' => $item]);
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        // dd($item->name, $request->name);
        $item->name = $request->name;
        $item->memo = $request->memo;
        $item->price = $request->price;
        $item->is_selling = $request->is_selling;
        $item->save();

        return to_route('items.index')
            ->with([
                'message' => '更新しました。',
                'status' => 'success',
            ]);
    }

    // 追加
    public function destroy(Item $item)
    {
        $item->delete();

        return to_route('items.index')
            ->with([
                'message' => '削除しました。',
                'status' => 'danger',
            ]);
    }
}
```

+ `resources/js/Components/FlashMessage.vue`を編集<br>

```vue:FlashMessage.vue
<script setup>
</script>

<template>
  <div v-if="$page.props.flash.status === 'success'" class="bg-blue-300 text-white p-4">
    {{ $page.props.flash.message }}
  </div>
  <div v-if="$page.props.flash.status === 'danger'" class="bg-red-300 text-white p-4">
    {{ $page.props.flash.message }}
  </div>
</template>
```
