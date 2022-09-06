## 54. Customer ページネーション 確認

+ [ページネーション](https://www.positronx.io/laravel-vue-inertia-pagination-integration-tutorial/) <br>

+ `app/Http/Controllers/CustomerController.php`を編集<br>

```php:CustomerController.php
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getTest = Customer::select(
            'id',
            'name',
            'kana',
            'tel'
        )->get();

        $getPaginate = Customer::select(
            'id',
            'name',
            'kana',
            'tel'
        )->paginate(50);

        dd($getTest, $getPaginate);

        return Inertia::render('Customers/Index', [
            'customers' => Customer::select(
                'id',
                'name',
                'kana',
                'tel'
            )->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

+ 一旦、`resources/js/Pages/Customers/Index.vue`をバックアップしておく<br>

+ `resources/js/Pages/Customers/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import { onMounted } from 'vue';

const props = defineProps({
  customers: Object,
})

onMounted(() => {
  console.log(props.customers)
})
</script>

<template>

</template>
```

+ `app/Http/Controllers/CustomerController.php`を編集<br>

```php:CustomerController.php
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $getTest = Customer::select(
        //     'id',
        //     'name',
        //     'kana',
        //     'tel'
        // )->get();

        // $getPaginate = Customer::select(
        //     'id',
        //     'name',
        //     'kana',
        //     'tel'
        // )->paginate(50);

        // dd($getTest, $getPaginate);

        return Inertia::render('Customers/Index', [
            'customers' => Customer::select(
                'id',
                'name',
                'kana',
                'tel'
            )->paginate(50)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

+ `http:localhost/customers`にアクセスして検証ツールで確認してみる<br>

+ `resources/js/Pages/Customers/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import { onMounted } from 'vue';

const props = defineProps({
  customers: Object,
})

onMounted(() => {
  console.log(props.customers)
  console.log(props.customers.last_page)
})
</script>

<template>

</template>
```

+ `検証ツールで確認してみる`<br>

## 55. ページネーション コンポーネントの作成

+ `$ touch resources/js/Components/pagination.vue`を実行<br>

+ `resources/js/Components/Pagination.vue`を編集<br>

```vue:Pagination.vue
<script setup>
import { Link } from '@inertiajs/inertia-vue3'; defineProps({ links: Array })
</script>

<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, index) in links" :key="index">
        <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4
  text-gray-400 border rounded" v-html="link.label" />
        <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded
  hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-blue-700 text-white': link.active }"
          :href="link.url" v-html="link.label" />
      </template>
    </div>
  </div>
</template>
```

+ `resources/js/Pages/Customers/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '../../Components/Pagination.vue';
// import { onMounted } from 'vue';

defineProps({
  customers: Object,
})

// onMounted(() => {
//   console.log(props.customers)
//   console.log(props.customers.last_page)
// })
</script>

<template>
  <ul v-for="customer in customers.data" :key="customer.id">
    <li>{{ customer.id }}</li>
    <li>{{ customer.name }}</li>
  </ul>
  <Pagination class="mt-6" :links="customers.links"></Pagination>
</template>
```

+ `localhost/customers`にアクセスしてみる<br>

## 56. ページネーションをIndexに組み込む

+ `mv resources/js/Pages/Customers/Index.vue resources/js/Pages/Customers/Index_test.vue`を実行<br>

+ `resources/js/Pages/Customers/Index copy.vue`を`Index.vue`にリネーム<br>

+ `resources/js/Pages/Customers/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '../../Components/Pagination.vue';

defineProps({
  customers: Object,
})
</script>

<template>

  <Head title="顧客一覧" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        顧客一覧
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
                  <Link as="button" :href="route('customers.create')"
                    class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">
                  顧客登録</Link>
                </div>
                <div class="lg:w-2/3 w-full mx-auto overflow-auto">
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
                      <tr v-for="customer in customers.data" :key="customer.id">
                        <td class="px-4 py-3">{{ customer.id }}</td>
                        <td class="px-4 py-3">{{ customer.name }}</td>
                        <td class="px-4 py-3">{{ customer.kana }}</td>
                        <td class="px-4 py-3">{{ customer.tel }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <Pagination class="mt-6" :links="customers.links"></Pagination>
            </section>
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>
```
