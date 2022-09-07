## 66. 購入日の日付を取得(JS Dateオブジェクト)

+ `resources/js/common.js`を編集<br>

```js:common.js
const nl2br = (str) => {
  var res = str.replace(/\r\n/g, "<br>");
  res = res.replace(/(\n|\r)/g, "<br>");
  return res;
}

// 追加
export const getToday = () => {
  const today = new Date()
  const yyyy = today.getFullYear()
  const mm = ("0" + (today.getMonth() + 1)).slice(-2)
  const dd = ("0" + today.getDate()).slice(-2)
  return yyyy + '-' + mm + '-' + dd
}
// ここまで

export default nl2br;
```

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { getToday } from '@/common';
import { onMounted, reactive } from 'vue';

onMounted(() => { // ページ読み込み後 即座に実行
  form.date = getToday()
})

const form = reactive({
  date: null
})
</script>

<template>
  日付<br>
  <input type="date" name="date" v-model="form.date">
</template>
```

## 67. 顧客情報をv-forで表示

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { getToday } from '@/common';
import { onMounted, reactive } from 'vue';

// 追加
const props = defineProps({
  'customers': Array
})
// ここまで

onMounted(() => {
  form.date = getToday()
})

const form = reactive({
  date: null,
  customer_id: null // 追加
})
</script>

<template>
  日付<br>
  <!-- 編集 -->
  <input type="date" name="date" v-model="form.date"><br>
  会員名<br>
  <select name="customer" v-model="form.customer_id">
    <option v-for="customer in customers" :value="customer.id" :key="customer.id">
      {{ customer.id }} : {{ customer.name }}
    </option>
  </select>
  <!-- ここまで -->
</template>
```

## 68. 商品情報をv-forで表示

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { getToday } from '@/common';
import { onMounted, reactive, ref } from 'vue'; // 編集

const props = defineProps({
  'customers': Array,
  'items': Array // 追加
})

const itemList = ref([]) // 追加

onMounted(() => {
  form.date = getToday()
  // 追加
  props.items.forEach((item) => {
    itemList.value.push({
      id: item.id,
      name: item.name,
      price: item.price,
      quantity: 0
    })
  })
  // ここまで
})

const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9",] // 追加

const form = reactive({
  date: null,
  customer_id: null
})
</script>

<template>
  日付<br>
  <input type="date" name="date" v-model="form.date"><br>
  会員名<br>
  <select name="customer" v-model="form.customer_id">
    <option v-for="customer in customers" :value="customer.id" :key="customer.id">
      {{ customer.id }} : {{ customer.name }}
    </option>
  </select>
  <!-- 追加 -->
  <br><br>

  商品・サービス<br>
  <table>
    <thead>
      <tr>
        <th>Id</th>
        <th>商品名</th>
        <th>金額</th>
        <th>数量</th>
        <th>小計</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in itemList">
        <td>{{ item.id }}</td>
        <td>{{ item.name }}</td>
        <td>{{ item.price }}</td>
        <td>
          <select name="quantity" v-model="item.quantity">
            <option v-for="q in quantity" :value="q">{{ q }}</option>
          </select>
        </td>
        <td>{{ item.price * item.quantity }}</td>
      </tr>
    </tbody>
  </table>
  <!-- ここまで -->
</template>
```

## 69. 合計金額をcomputedで計算

+ `Computed`は変更があり次第再計算する returnが必須<br>

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { getToday } from '@/common';
import { computed, onMounted, reactive, ref } from 'vue'; // 編集

const props = defineProps({
  'customers': Array,
  'items': Array
})

const itemList = ref([])

onMounted(() => {
  form.date = getToday()
  props.items.forEach((item) => {
    itemList.value.push({
      id: item.id,
      name: item.name,
      price: item.price,
      quantity: 0
    })
  })
})

const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9",]

const form = reactive({
  date: null,
  customer_id: null
})

// 追加
const totalPrice = computed(() => {
  let total = 0
  itemList.value.forEach((item) => {
    total += item.price * item.quantity
  })
  return total
})
// ここまで
</script>

<template>
  日付<br>
  <input type="date" name="date" v-model="form.date"><br>
  会員名<br>
  <select name="customer" v-model="form.customer_id">
    <option v-for="customer in customers" :value="customer.id" :key="customer.id">
      {{ customer.id }} : {{ customer.name }}
    </option>
  </select>
  <br><br>

  商品・サービス<br>
  <table>
    <thead>
      <tr>
        <th>Id</th>
        <th>商品名</th>
        <th>金額</th>
        <th>数量</th>
        <th>小計</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in itemList">
        <td>{{ item.id }}</td>
        <td>{{ item.name }}</td>
        <td>{{ item.price }}</td>
        <td>
          <select name="quantity" v-model="item.quantity">
            <option v-for="q in quantity" :value="q">{{ q }}</option>
          </select>
        </td>
        <td>{{ item.price * item.quantity }}</td>
      </tr>
    </tbody>
  </table>
  <!-- 追加 -->
  <br>
  合計 {{ totalPrice }}
  <!-- ここまで -->
</template>
```

## 70. 保存処理その1 POST通信

+ `$ php artisan make:request StorePurchaseRequest`を実行<br>

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { getToday } from '@/common';
import { Inertia } from '@inertiajs/inertia';
import { computed, onMounted, reactive, ref } from 'vue';

const props = defineProps({
  'customers': Array,
  'items': Array,
})

const itemList = ref([])

onMounted(() => {
  form.date = getToday()
  props.items.forEach((item) => {
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

// 追加
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
// ここまで
</script>

<template>
  <form @submit.prevent="storePurchase"> <!-- 追加 -->
    日付<br>
    <input type="date" name="date" v-model="form.date"><br>
    会員名<br>
    <select name="customer" v-model="form.customer_id">
      <option v-for="customer in customers" :value="customer.id" :key="customer.id">
        {{ customer.id }} : {{ customer.name }}
      </option>
    </select>
    <br><br>

    商品・サービス<br>
    <table>
      <thead>
        <tr>
          <th>Id</th>
          <th>商品名</th>
          <th>金額</th>
          <th>数量</th>
          <th>小計</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in itemList">
          <td>{{ item.id }}</td>
          <td>{{ item.name }}</td>
          <td>{{ item.price }}</td>
          <td>
            <select name="quantity" v-model="item.quantity">
              <option v-for="q in quantity" :value="q">{{ q }}</option>
            </select>
          </td>
          <td>{{ item.price * item.quantity }}</td>
        </tr>
      </tbody>
    </table>
    <br>
    <!-- 編集 -->
    合計 {{ totalPrice }} 円<br><br>
    <button>登録する</button>
    <!-- ここまで -->
  </form> <!-- ここまで -->
</template>
```

app/Http/Requests/StorePurchaseRequest.php`を編集<br>

```php:StorePurchaseRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'customer_id' => ['required']
        ];
    }
}
```

+ `app/Http/Controllers/PurchaseController.php`を編集<br>

```php:PurchaseController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest; // 追加
use App\Models\Customer;
use App\Models\Item;
use Illuminate\Http\Request;
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::select('id', 'name', 'kana')->get();
        $items = Item::select('id', 'name', 'price')
            ->where('is_selling', true)->get();

        return Inertia::render('Purchases/Create', [
            'customers' => $customers,
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // 編集
    public function store(StorePurchaseRequest $request)
    {
        dd($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

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