## 88. 購買履歴の編集画面(Vue側)

+ `app/Models/Scopes/Subtotal.php`を編集<br>

```php:Subtotal.php
<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Subtotal implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $sql = 'select purchases.id as id
        , items.id as item_id
        , item_purchase.id as pivot_id
        , items.price * item_purchase.quantity as subtotal
        , customers.id as customer_id // 追加
        , customers.name as customer_name
        , items.name as item_name
        , items.price as item_price
        , item_purchase.quantity
        , purchases.status
        , purchases.created_at
        , purchases.updated_at
        from purchases
        left join item_purchase on purchases.id = item_purchase.purchase_id
        left join items on item_purchase.item_id = items.id
        left join customers on purchases.customer_id = customers.id
        ';

        $builder->fromSub($sql, 'order_subtotals');
    }
}
```

+ `app/Http/Controllers/PurchaseController.php`を編集<br>

```php:PurchaseController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
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
    // 編集
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
    // ここまで

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

+ `$ cp resources/js/Pages/Purchases/Create.vue resources/js/Pages/Purchases/Edit.vue`を実行<br>

+ `resources/js/Pages/Purchases/Show.vue`を編集<br>

```vue:Show.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { onMounted } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import dayjs from 'dayjs'


const props = defineProps({
  'items': Array,
  'order': Array
})

onMounted(() => {
  console.log(props.items[0])
  console.log(props.order[0].customer_name)
})
</script>

<template>

  <Head title="購買履歴 詳細画面" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        購買履歴 詳細画面
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <BreezeValidationErrors class="mb-4" />
            <section class="text-gray-600 body-font relative">
              <form @submit.prevent="storePurchase">
                <div class="container px-5 py-8 mx-auto">
                  <div class="lg:w-1/2 md:w-2/3 mx-auto">
                    <div class="flex flex-wrap -m-2">
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="date" class="leading-7 text-sm text-gray-600">日付</label>
                          <div id="date" name="date"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            {{ dayjs(props.order[0].created_at).format('YYYY/MM/DD') }}
                          </div>
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="customer_name" class="leading-7 text-sm text-gray-600">会員名</label>
                          <div id="customer_name" name="customer_name"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            {{ props.order[0].customer_name }}
                          </div>
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
                            <tr v-for="item in props.items" :key="item.id">
                              <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.item_id }}</td>
                              <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.item_name }}</td>
                              <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.item_price }}</td>
                              <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.quantity }}</td>
                              <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.subtotal }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div class="p-2 w-full">
                        <div class="">
                          <label for="price" class="leading-7 text-sm text-gray-600">合計金額</label><br>
                          <div
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            {{ props.order[0].total }} 円
                          </div>
                        </div>
                      </div>

                      <div class="p-2 w-full">
                        <div class="">
                          <label for="status" class="leading-7 text-sm text-gray-600">ステータス</label><br>
                          <div
                            v-if="props.order[0].status == true"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            未キャンセル
                          </div>
                          <div
                            v-if="props.order[0].status == false"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            キャンセル済
                          </div>
                        </div>
                      </div>

                      <div v-if="props.order[0].status == false" class="p-2 w-full">
                        <div class="">
                          <label v-if="props.order[0].status == false" for="cancel" class="leading-7 text-sm text-gray-600">キャンセル日</label><br>
                          <div
                            v-if="props.order[0].status == false"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            {{ dayjs(props.order[0].updated_at).format('YYYY/MM/DD') }}
                          </div>
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <Link
                          v-if="props.order[0].status == true" // 追加
                          as="button"
                          :href="route('purchases.edit', {purchase: props.order[0].id})"
                          class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">編集する</Link>
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
