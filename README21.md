# セクション6: CRM 購買履歴画面

## 79. ダミーデータ 3万件投入

+ `database/factries/PurchaseFactory.php`を編集<br>

```php:PurchaseFactory.php
<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // 追加
        $decade = $this->faker->dateTimeThisDecade;
        $created_at = $decade->modify('+2 years');
        // ここまで

        return [
            'customer_id' => rand(1, Customer::count()),
            'status' => $this->faker->boolean,
            'created_at' => $created_at, // 追加
        ];
    }
}
```

+ `database/seeders/DatabaseSeeder.php`を編集<br>

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            ItemsTableSeeder::class,
        ]);

        Customer::factory(1000)->create(); // 編集

        $items = Item::all();

        Purchase::factory(30000)->create()
            ->each(function (Purchase $purchase) use ($items) {
                $purchase->items()->attach(
                    $items->random(rand(1, 3))->pluck('id')->toArray(),
                    ['quantity' => rand(1, 5)]
                );
            });

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
```

+ `$ php artisan migrate:fresh --seed`を実行<br>

## 80. joinで4つのテーブルを紐付けて合計金額を表示(mysql版)

### 購買履歴一覧

+ 欲しい情報<br>
+ 購買id、顧客名、合計金額、ステータス、購入日<br>

+ サブクエリ<br>

```
4つのテーブルをjoin
金額 * 数量 = 小計 を表示
```

+ サブクエリで生成したテーブルをもとに group by で 売買毎の合計金額を表示<br>

## 81. グローバルスコープ

```
先々、サブクエリを元にクエリスコープを何度か実施する事を想定
(ex 期間指定、日別、月別、商品別・・)

今回のサブクエリ・・4つのテーブルをjoinし小計を出している状態

新たにモデルを作成し、グローバルスコープ(モデル全体に適用)を使い
サブクエリ実施後の状態にする
```

+ [クエリスコープ (グローバルスコープ)](https://readouble.com/laravel/9.x/ja/eloquent.html) <br>

+ `$ php artisan make:scop Subtotal`を実行<br>

+ `$ php artisan make:model Order`を実行<br>

+ `app/Models/Order.php`を編集<br>

```php:Order.php
<?php

namespace App\Models;

use App\Models\Scopes\Subtotal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new Subtotal);
    }
}
```

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
        , item_purchase.id as pivot_id
        , items.price * item_purchase.quantity as subtotal
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

+ `app/Http/Controllers/PurChaseController.php`を編集<br>

```php:PurChaseController.php
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
        dd(Order::paginate(50)); // 大量データの場合paginateを使わないとメモリエラーになる
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

+ http://localhost/purchases にアクセスしてみる<br>

## 82. 購買履歴の一覧画面（index）

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

+ `$ cp resources/js/Pages/Customers/Index.vue resources/js/Pages/Purchases/Index.vue`を実行<br>

+ `resources/js/Pages/Purchases/Index.vue`を編集<br>

```vue:Index.vue
<script setup>
import { onMounted } from 'vue'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import FlashMessage from '@/Components/FlashMessage.vue'
import Pagination from '@/Components/Pagination.vue'

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
                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ order.created_at }}</td>
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

+ `resources/js/Layouts/Authenticated.vue`を編集<br>

```vue:Authenticated.vue
<script setup>
import { ref } from 'vue';
import BreezeApplicationLogo from '@/Components/ApplicationLogo.vue';
import BreezeDropdown from '@/Components/Dropdown.vue';
import BreezeDropdownLink from '@/Components/DropdownLink.vue';
import BreezeNavLink from '@/Components/NavLink.vue';
import BreezeResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/inertia-vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                <BreezeApplicationLogo class="block w-10" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <BreezeNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </BreezeNavLink>
                                <BreezeNavLink :href="route('purchases.create')"
                                    :active="route().current('purchases.create')">
                                    購入画面
                                </BreezeNavLink>
                                <!-- 追加 -->
                                <BreezeNavLink :href="route('purchases.index')"
                                    :active="route().current('purchases.index')">
                                    購買履歴
                                </BreezeNavLink>
                                <!-- ここまで -->
                                <BreezeNavLink :href="route('items.index')" :active="route().current('items.index')">
                                    商品管理
                                </BreezeNavLink>
                                <BreezeNavLink :href="route('customers.index')"
                                    :active="route().current('customers.index')">
                                    顧客管理
                                </BreezeNavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <BreezeDropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <BreezeDropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </BreezeDropdownLink>
                                    </template>
                                </BreezeDropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = ! showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <BreezeResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </BreezeResponsiveNavLink>
                        <BreezeResponsiveNavLink :href="route('purchases.create')"
                            :active="route().current('purchases.create')">
                            購入画面
                        </BreezeResponsiveNavLink>
                        <!-- 追加 -->
                        <BreezeResponsiveNavLink :href="route('purchases.index')"
                            :active="route().current('purchases.index')">
                            購買履歴
                        </BreezeResponsiveNavLink>
                        <!-- ここまで -->
                        <BreezeResponsiveNavLink :href="route('items.index')" :active="route().current('items.index')">
                            商品管理
                        </BreezeResponsiveNavLink>
                        <BreezeResponsiveNavLink :href="route('customers.index')"
                            :active="route().current('customers.index')">
                            顧客管理
                        </BreezeResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <BreezeResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </BreezeResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
```
