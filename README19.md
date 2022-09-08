## 75. API通信の設定確認(Sanctum, axios)

### APIの動作確認

+ `Inertia::render`で返却するとページ毎 再描画される<br>

+ 顧客の箇所だけ更新したいのでAPIで対応 Sanctumの機能を使う<br>

+ [Laravel Sanctum](https://readouble.com/laravel/9.x/ja/sanctum.html) <br>

+ [`.env`の関連記事](https://qiita.com/ksrnnb/items/d2b73a6bf7dccde90446) <br>

+ `app/Http/Kernel.php`を編集<br>

```php:Kernal.php
// 省略
 protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // コメントアウト解除
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
// 省略
```

+ `.env`を編集<br>

```:.env
APP_NAME=uCRM
APP_ENV=local
APP_KEY=base64:BQ8RM6HfFzPbQl3QFh5cE/UkKVLW0z54VywpnuhkV+Y=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_vue3_udemy
DB_USERNAME=sail
DB_PASSWORD=password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=memcached

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700

# 追加
SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DOMAIN=localhost
```

+ `resources/js/Components/MicroModal.vue`を編集<br>

```vue:MicroModal.vue
<script setup>
import axios from 'axios'; // 追加
import { onMounted, ref } from 'vue'; // 編集

// 追加
onMounted(() => {
  axios.get('/api/user')
    .then((res) => {
      console.log(res.data)
    })
})
// ここまで

const isShow = ref(false)
const toggleStatus = () => {
  isShow.value = !isShow.value
}
</script>

<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            Micromodal
          </h2>
          <button type="button" @click="toggleStatus" class="modal__close" aria-label="Close modal"
            data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <p>
            Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also,
            <code>esc</code> to close modal.
          </p>
        </main>
        <footer class="modal__footer">
          <button type="button" @click="toggleStatus" class="modal__btn modal__btn-primary">Continue</button>
          <button type="button" @click="toggleStatus" class="modal__btn" data-micromodal-close
            aria-label="Close this dialog window">Close</button>
        </footer>
      </div>
    </div>
  </div>
  <button type="button" @click="toggleStatus" data-micromodal-trigger="modal-1" href='javascript:;'>Open Modal
    Dialog</button>
</template>
```

## 76. async-awaitも使って顧客情報取得

+ `routes/api.php`を編集<br>

```php:api.php
<?php

use App\Models\Customer; // 追加
use Illuminate\Http\Request; // 追加
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 追加
Route::middleware('auth:sanctum')->get('/searchCustomers', function (Request $request) {
    return Customer::searchCustomers($request->search)
        ->select('id', 'name', 'kana', 'tel')->paginate(50);
});
// ここまで

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

+ `resources/js/Components/MicroModal.vue`を編集<br>

```vue:MicroModal.vue
<script setup>
import axios from 'axios';
import { reactive, ref } from 'vue'; // 編集

const search = ref('') // 追加
const customers = reactive({}) // 追加

const isShow = ref(false)
const toggleStatus = () => {
  isShow.value = !isShow.value
}

// 追加
const searchCustomers = async () => {
  try {
    await axios.get(`/api/searchCustomers/?search=${search.value}`)
      .then((res) => {
        console.log(res.data)
        customers.value = res.data
      })
    toggleStatus()
  } catch (e) {
    console.log(e)
  }
}
// ここまで
</script>

<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            Micromodal
          </h2>
          <button type="button" @click="toggleStatus" class="modal__close" aria-label="Close modal"
            data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <p>
            Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also,
            <code>esc</code> to close modal.
          </p>
        </main>
        <footer class="modal__footer">
          <button type="button" @click="toggleStatus" class="modal__btn modal__btn-primary">Continue</button>
          <button type="button" @click="toggleStatus" class="modal__btn" data-micromodal-close
            aria-label="Close this dialog window">Close</button>
        </footer>
      </div>
    </div>
  </div>
  <!-- 編集 -->
  <input name="customer" v-model="search">
  <button type="button" @click="searchCustomers" data-micromodal-trigger="modal-1">検索する</button>
  <!-- ここまで -->
</template>
```

## 77. 顧客情報をモーダルウィンドウに組み込む

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { computed, onMounted, reactive, ref } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { getToday } from '@/common';
import MicroModal from '../../Components/MicroModal.vue'

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
                          <MicroModal />
                          <label for="customer" class="leading-7 text-sm text-gray-600">会員名</label>
                          <select id="customer" name="customer" v-model="form.customer_id"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            <option v-for="customer in customers" :value="customer.id" :key="customer.id">
                              {{ customer.id }} : {{ customer.name }}
                            </option>
                          </select>
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
                        <div class=""> <!-- class="relative"をカット -->
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

+ `resources/js/Components/MicroModal.vue`を編集<br>

```vue:MicroModal.vue
<script setup>
import axios from 'axios';
import { reactive, ref } from 'vue';

const search = ref('')
const customers = reactive({})

const isShow = ref(false)
const toggleStatus = () => {
  isShow.value = !isShow.value
}

const searchCustomers = async () => {
  try {
    await axios.get(`/api/searchCustomers/?search=${search.value}`)
      .then((res) => {
        console.log(res.data)
        customers.value = res.data
      })
    toggleStatus()
  } catch (e) {
    console.log(e)
  }
}
</script>

<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <!-- 編集 classに w-2/3を追加 -->
      <div class="modal__container w-2/3" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            顧客検索
          </h2>
          <button type="button" @click="toggleStatus" class="modal__close" aria-label="Close modal"
            data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <!-- 編集 -->
          <div v-if="customers.value" class="lg:w-2/3 w-full mx-auto overflow-auto">
            <table class="table-auto w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th
                    class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                    ID</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                    氏名</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                    カナ</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                    電話番号</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in customers.value.data" :key="customer.id">
                  <td class="px-4 py-3">{{ customer.id }}</td>
                  <td class="px-4 py-3">{{ customer.name }}</td>
                  <td class="px-4 py-3">{{ customer.kana }}</td>
                  <td class="px-4 py-3">{{ customer.tel }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
        <footer class="modal__footer">
          <button type="button" @click="toggleStatus" class="modal__btn" data-micromodal-close
            aria-label="Close this dialog window">閉じる</button>
        </footer>
        <!-- ここまで -->
      </div>
    </div>
  </div>
  <input name="customer" v-model="search"
    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
  <button type="button" @click="searchCustomers"
    class="flex mx-auto text-white bg-teal-500 border-0 py-2 px-8 focus:outline-none hover:bg-teal-600 rounded text-lg"
    data-micromodal-trigger="modal-1">検索する</button>
</template>
```

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { computed, onMounted, reactive, ref } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { getToday } from '@/common';
import MicroModal from '../../Components/MicroModal.vue'

// customersをカット
const props = defineProps({
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
                          <!-- 編集 -->
                          <label for="customer" class="leading-7 text-sm text-gray-600">会員名</label>
                          <MicroModal />
                          <!-- ここまで -->
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

+ `app/Http/Controllers/PurchaseController.php`を編集<br>

```php:PurchaseController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Item;
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // 編集
    public function create()
    {
        $items = Item::select('id', 'name', 'price')
            ->where('is_selling', true)->get();

        return Inertia::render('Purchases/Create', [
            'items' => $items
        ]);
    }
    // ここまで

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        DB::beginTransaction();

        try {
            // dd($request);
            $purchase = Purchase::create([
                'customer_id' => $request->customer_id,
                'status' => $request->status,
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
            DB::rollBack();
        }
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
