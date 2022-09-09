## 89. 購入履歴の更新 その1

+ `resources/js/Pages/Purchases/Edit.vue`を編集<br>

```vue:Edit.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { computed, onMounted, reactive, ref } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import dayjs from 'dayjs'

const props = defineProps({
  'order': Array,
  'items': Array,
})

const itemList = ref([])

onMounted(() => {
  props.items.forEach(item => {
    itemList.value.push({
      id: item.id,
      name: item.name,
      price: item.price,
      quantity: item.quantity
    })
  })
})

const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9",]

// コントローラに渡す
const form = reactive({
  id: props.order[0].id, // 追加
  date: dayjs(props.order[0].created_at).format("YYYY-MM-DD"),
  customer_id: props.order[0].customer_id,
  status: props.order[0].status,
  items: []
})

const totalPrice = computed(() => {
  let total = 0
  itemList.value.forEach((item) => {
    total += item.price * item.quantity
  })
  return total
})

// 編集
const updatePurchase = (id) => {
  itemList.value.forEach((item) => {
    if (item.quantity > 0) { // 0より大きいものだけ追加
      form.items.push({
        id: item.id,
        quantity: item.quantity
      })
    }
  })
  Inertia.put(route('purchases.update', { purchase: id }), form)
}
// ここまで
</script>

<template>

  <Head title="購買履歴 編集画面" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        購買履歴 編集画面
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <BreezeValidationErrors class="mb-4" />
            <section class="text-gray-600 body-font relative">
              <form @submit.prevent="updatePurchase(form.id)"> <!-- 編集 -->
                <div class="container px-5 py-8 mx-auto">
                  <div class="lg:w-1/2 md:w-2/3 mx-auto">
                    <div class="flex flex-wrap -m-2">
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="date" class="leading-7 text-sm text-gray-600">日付</label>
                          <input disabled type="date" id="date" name="date" :value="form.date"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="customer" class="leading-7 text-sm text-gray-600">会員名</label>
                          <input disabled type="text" id="customer" name="customer" :value="props.order[0].customer_name"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>

                      <div class="w-full mt-8 mx-auto overflow-auto">
                        <table class="table-auto w-full text-left whitespace-no-wrap">
                          <thead>
                            <tr>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                                ID</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                商品名</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                金額</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                数量</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                小計</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="item in itemList" :key="item.id">
                              <td class="px-4 py-3">{{ item.id }}</td>
                              <td class="px-4 py-3">{{ item.name }}</td>
                              <td class="px-4 py-3">{{ item.price }}</td>
                              <td class="px-4 py-3 text-lg">
                                <select name="quantity" v-model="item.quantity">
                                  <option v-for="q in quantity" :value="q">{{ q }}</option>
                                </select>
                              </td>
                              <td class="px-4 py-3">{{ item.price * item.quantity }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div class="p-2 w-full">
                        <div class="">
                          <label for="price" class="leading-7 text-sm text-gray-600">合計金額</label><br>
                          <div
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            {{ totalPrice }} 円
                          </div>
                        </div>
                      </div>

                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="status" class="leading-7 text-sm text-gray-600">ステータス</label>
                          <input type="radio" id="status" v-model="form.status" name="status" value="1">未キャンセル
                          <input type="radio" id="status" v-model="form.status" name="status" value="0">キャンセルする
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

+ `$ php artisan make:request UpdatePurchaseRequest`を実行<br>

+ `app/Http/Requests/UpdatePurchaseRequest.php`を編集<br>

```php:UpdatePurchaseRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // 編集
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
```

+ `app/Http/Controllers/PurchaseController.php`を編集<br>

```php:PurchaseController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest; // 追加
use App\Models\Item;
use App\Models\Order;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(Order::paginate(50));

        // 合計
        $orders = Order::groupBy('id')
            ->selectRaw(
                'id,
                customer_name,
                sum(subtotal) as total, status, created_at'
            )->paginate(50);

        // dd($orders);

        return Inertia::render('Purchases/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $items = Item::select('id', 'name', 'price')
            ->where('is_selling', true)->get();

        return Inertia::render('Purchases/Create', [
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        // dd($request);

        DB::beginTransaction();

        try {
            $purchase = Purchase::create([
                'customer_id' => $request->customer_id,
                'status' => $request->status
            ]);

            foreach ($request->items as $item) {
                $purchase->items()->attach($purchase->id, [
                    'item_id' => $item['id'],
                    'quantity' => $item['quantity']
                ]);
            }

            DB::commit();

            return to_route('dashboard');
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        // 小計
        $items = Order::where('id', $purchase->id)->get();

        // 合計
        $order = Order::groupBy('id')
            ->where('id', $purchase->id)
            ->selectRaw('id, customer_name, sum(subtotal) as total, status, created_at')
            ->get();

        // dd($items, $order);

        return Inertia::render('Purchases/Show', [
            'items' => $items,
            'order' => $order
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $purchase = Purchase::find($purchase->id);

        $allItems = Item::select('id', 'name', 'price')->get();

        $items = [];

        foreach ($allItems as $allItem) {
            $quantity = 0;
            foreach ($purchase->items as $item) {
                if ($allItem->id === $item->id) {
                    $quantity = $item->pivot->quantity;
                }
            }
            array_push($items, [
                'id' => $allItem->id,
                'name' => $allItem->name,
                'price' => $allItem->price,
                'quantity' => $quantity,
            ]);
        }

        // dd($items);
        $order = Order::groupBy('id')
            ->where('id', $purchase->id)
            ->selectRaw('id, customer_id,
        customer_name, status, created_at')
            ->get();

        return Inertia::render('Purchases/Edit', [
            'items' => $items,
            'order' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // 編集
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        dd($request, $purchase);
    }
    // ここまで

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
```

## 90. 購入履歴の更新 その2(sync)

+ 中間テーブルの情報を更新するにはsync()が便利<br>
+ 引数に配列が必要なので事前に作成しておく<br>

+ `app/Http/Controllers/PurchaseController.php`を編集<br>

```php:PurchaseController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Item;
use App\Models\Order;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(Order::paginate(50));

        // 合計
        $orders = Order::groupBy('id')
            ->selectRaw(
                'id,
                customer_name,
                sum(subtotal) as total, status, created_at'
            )->paginate(50);

        // dd($orders);

        return Inertia::render('Purchases/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $items = Item::select('id', 'name', 'price')
            ->where('is_selling', true)->get();

        return Inertia::render('Purchases/Create', [
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        // dd($request);

        DB::beginTransaction();

        try {
            $purchase = Purchase::create([
                'customer_id' => $request->customer_id,
                'status' => $request->status
            ]);

            foreach ($request->items as $item) {
                $purchase->items()->attach($purchase->id, [
                    'item_id' => $item['id'],
                    'quantity' => $item['quantity']
                ]);
            }

            DB::commit();

            return to_route('dashboard');
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        // 小計
        $items = Order::where('id', $purchase->id)->get();

        // 合計
        $order = Order::groupBy('id')
            ->where('id', $purchase->id)
            ->selectRaw('id, customer_name, sum(subtotal) as total, status, created_at')
            ->get();

        // dd($items, $order);

        return Inertia::render('Purchases/Show', [
            'items' => $items,
            'order' => $order
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $purchase = Purchase::find($purchase->id);

        $allItems = Item::select('id', 'name', 'price')->get();

        $items = [];

        foreach ($allItems as $allItem) {
            $quantity = 0;
            foreach ($purchase->items as $item) {
                if ($allItem->id === $item->id) {
                    $quantity = $item->pivot->quantity;
                }
            }
            array_push($items, [
                'id' => $allItem->id,
                'name' => $allItem->name,
                'price' => $allItem->price,
                'quantity' => $quantity,
            ]);
        }

        // dd($items);
        $order = Order::groupBy('id')
            ->where('id', $purchase->id)
            ->selectRaw('id, customer_id,
        customer_name, status, created_at')
            ->get();

        return Inertia::render('Purchases/Edit', [
            'items' => $items,
            'order' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        DB::beginTransaction();

        try {
            // dd($request, $purchase);
            $purchase->status = $request->status;
            $purchase->save();

            $items = [];

            foreach ($request->items as $item) {
                $items = $items + [
                    // item_id => [ 中間テーブルの列名 => 値 ]
                    $item['id'] => [
                        'quantity' => $item['quantity']
                    ]
                ];
            }

            // dd($items);
            $purchase->items()->sync($items);

            DB::commit();
            return to_route('dashboard');
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
```
