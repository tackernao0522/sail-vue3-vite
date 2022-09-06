## 57. Cusotmer 検索機能(Laravel側)

+ [クエリスコープ](https://readouble.com/laravel/9.x/ja/eloquent.html) <br>

+ `app/Models/Customer.php`を編集<br>

```php:Customer.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    public function scopeSearchCustomers($query, $input = null)
    {
        if (!empty($input)) {
            if (Customer::where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%')->exists()) {
                return $query->where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%');
            }
        }
    }
}
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

        $customers = Customer::searchCustomers('エツイ')
            ->select(
                'id',
                'name',
                'kana',
                'tel'
            )->paginate(50);

        dd($customers);

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

## 58. Customer 検索機能(Vue.js側)

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
    public function index(Request $request)
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

        $customers = Customer::searchCustomers($request->search)
            ->select(
                'id',
                'name',
                'kana',
                'tel'
            )->paginate(50);

        // dd($customers);

        return Inertia::render('Customers/Index', [
            'customers' => $customers
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

+ `resources/js/Pages/Cusotmers/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '../../Components/Pagination.vue';
import { ref } from 'vue'; // 追加
import { Inertia } from '@inertiajs/inertia'; // 追加

defineProps({
  customers: Object,
})

const search = ref('') // 追加

// ref の値を取得するには .valueが必要
// 追加
const searchCustomers = () => {
  Inertia.get(route('customers.index', { search: search.value }))
}
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
                  <!-- 追加 -->
                  <div>
                    <input type="text" name="search" v-model="search">
                    <button
                      class="bg-blue-300 text-white py-2 px-2"
                      @click="searchCustomers"
                    >検索</button>
                  </div>
                  <!-- ここまで -->
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
