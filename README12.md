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
  21
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
