## 83. 購入日の表示変更（dayjs)

+ `$ npm i dayjs@1.11.5 --save`を実行<br>

+ `resources/js/Pages/Purchases/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import { onMounted } from 'vue'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'
import dayjs from 'dayjs' // 追加

const props = defineProps({
  orders: Object
})

onMounted(() => {
  console.log(props.orders.data)
})

</script>

<template>

  <Head title="購買履歴" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        購買履歴
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <section class="text-gray-600 body-font">
              <div class="container px-5 py-8 mx-auto">
                <FlashMessage />
                <div class="flex pl-4 my-4 lg:w-2/3 w-full mx-auto">
                  <div>
                    <input type="text" name="search" v-model="search">
                    <button class="bg-blue-300 text-white py-2 px-2" @click="searchCustomers">検索</button>
                  </div>
                </div>
                <div class="lg:w-2/3 w-full mx-auto overflow-auto">
                  <table class="table-auto w-full text-left whitespace-no-wrap">
                    <thead>
                      <tr>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                          Id</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">氏名
                        </th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          合計金額</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          ステータス</th>
                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                          購入日</th>

                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="order in props.orders.data" :key="order.id">
                        <td class="border-b-2 border-gray-200 px-4 py-3">
                          <Link class="text-blue-400" :href="route('purchases.show', { purchase: order.id })">
                          {{ order.id }}
                          </Link>
                        </td>
                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ order.customer_name }}</td>
                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ order.total }}</td>
                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ order.status }}</td>
                        <!-- 編集 -->
                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ dayjs(order.created_at).format('YYYY-MM-DD HH:mm:ss') }}</td>
                        <!-- ここまで -->
                      </tr>
                    </tbody>
                  </table>
                </div>

              </div>
              <Pagination class="mt-6" :links="props.orders.links"></Pagination>
            </section>
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>
```

## 84. 購買履歴の詳細画面(show) (小計・合計)

+ 表示したい内容<br>

```
購買ID
顧客名
商品名・数・小計
合計金額
ステータス
購入日の表示変更
(キャンセルした日 updated_at)
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
    // 追加
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
    // ここまで

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

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

+ `$ touch resources/js/Pages/Purchases/Show.vue`を実行<br>

+ `resources/js/Pages/Purcahses/Show.vue`を編集<br>

```vue:Show.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { computed, onMounted, reactive, ref } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { getToday } from '@/common';
import MicroModal from '../../Components/MicroModal.vue'

const props = defineProps({
  'items': Array,
  'order': Array
})

const itemList = ref([])

onMounted(() => {
  console.log(props.items)
  console.log(props.order[0].customer_name)
  form.date = getToday()
  props.items.forEach(item => {
    itemList.value.push({
      id: item.id,
      name: item.name,
      price: item.price,
      quantity: 0
    })
  })
})

const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9",]

// コントローラに渡す
const form = reactive({
  date: null,
  customer_id: null,
  status: true,
  items: []
})

const totalPrice = computed(() => {
  let total = 0
  itemList.value.forEach((item) => {
    total += item.price * item.quantity
  })
  return total
})

const storePurchase = () => {
  itemList.value.forEach((item) => {
    if (item.quantity > 0) { // 0より大きいものだけ追加
      form.items.push({
        id: item.id,
        quantity: item.quantity
      })
    }
  })
  Inertia.post(route('purchases.store'), form)
}

const setCustomerId = (id) => {
  form.customer_id = id
}
</script>

<template>

  <Head title="購入画面" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        購入画面
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
                          <input type="date" id="date" name="date" v-model="form.date"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="customer" class="leading-7 text-sm text-gray-600">会員名</label>
                          <MicroModal @update:customerId="setCustomerId" />
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
                        <button
                          class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
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