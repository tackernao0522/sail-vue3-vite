# セクション5: CRM 購入画面

## 62. Purchase モデル・マイグレーション・リレーション

+ `$ php artisan make:model Purchase`を実行<br>

+ `$ php artisan make:migration create_purchases_table --create=purchases`を実行<br>

+ `database/migrations/create_purchases_table.php`を編集<br>

```php:create_purchases_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()
                ->onUpdate('cascade');
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
```

+ `app/Models/Purchase.php`を編集<br>

```php:Purchase.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];
}
```

+ `app/Models/Customer.php`を編集<br>

```php:Customer.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase; // 追加

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeSearchCustomers($query, $input = null)
    {
        if (!empty($input)) {
            if (Customer::where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%')->exists()) {
                return $query->where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%');
            }
        }
    }

    // 追加
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
```

+ `app/Models/Purchase.php`を編集<br>

```php:Purchase.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer; // 追加

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];

    // 追加
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
```

+ `$ php artisan make:factory PurchaseFactory`を実行<br>

+ `database/factories/PurchaseFactory.php`を編集<br>

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
        return [
            'customer_id' => rand(1, Customer::count()),
            'status' => $this->faker->boolean,
        ];
    }
}
```

+ `database/seeders/DatabaseSeeder.php`を編集<br>

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        \App\Models\Customer::factory(1000)->create();
        \App\Models\Purchase::factory(100)->create();

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            UsersTableSeeder::class,
            ItemsTableSeeder::class,
        ]);
    }
}
```

+ `$ php artisan migrate:fresh --seed`を実行<br>

## 63. ItemPurchase マイグレーション・リレーション

+ `$ php artisan make:migration create_item_purchase_table --create=item_purchase`を実行<br>

+ `database/migration/create_item_purchase_table.php`を編集<br>

```php:create_item_purchase_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_purchase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->constrained()
                ->onUpdate('cascade');
            $table->foreignId('purchase_id')
                ->constrained()
                ->onUpdate('cascade');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_purchase');
    }
};
```

+ `app/Models/Purchase.php`を編集<br>

```php:Purchase.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // 追加
    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }
}
```

+ `app/Models/Item.php`を編集<br>

```php:Item.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'memo',
        'price',
        'is_selling'
    ];

    public function purchases()
    {
        return $this->belongsToMany(Purchase::class)->withPivot('quatity');
    }
}
```

## 64. 中間テーブルのダミーデータ作成

+ [eachメソッド](https://readouble.com/laravel/9.x/ja/collections.html#method-each) <br>

+ [many to many の attach](https://readouble.com/laravel/9.x/ja/eloquent-relationships.html#many-to-many) <br>

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

        Customer::factory(1000)->create();

        $items = Item::all();

        Purchase::factory(100)->create()
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

## 65. 購入画面の準備

### Purchase/Create.vue

フォームの項目<br>

1. 購入日・・当日を初期値<br>
2. 会員名・・Customerから取得<br>
3. 商品情報・・(Id、 名前、金額、数量、小計) (数量を変更すると小計・合計が計算される) <br>
4. 合計金額<br>

|Id|名前|金額|数量|小計|
|:---:|:---:|:---:|:---:|:---:|
|1|カット|6000|1|6000|
|2|カラー|8000|1|8000|
3|パーマ|13000|0|0|

+ `php artisan make:controller PurchaseController --resource`を実行<br>

+ `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InertiaTestController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController; // 追加
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::resource('items', ItemController::class)->middleware(['auth', 'verified']);

Route::resource('customers', CustomerController::class)->middleware(['auth', 'verified']);

Route::resource('purchases', PurchaseController::class)->middleware(['auth', 'verified']); // 追加

Route::get('/inertia-test', function () {
    return Inertia::render('InertiaTest');
});

Route::get('/component-test', function () {
    return Inertia::render('ComponentTest');
});

Route::get('/inertia/index', [InertiaTestController::class, 'index'])->name('inertia.index');
Route::get('/inertia/create', [InertiaTestController::class, 'create'])->name('inertia.create');
Route::post('/inertia', [InertiaTestController::class, 'store'])->name('inertia.store');
Route::get('/inertia/show/{id}', [InertiaTestController::class, 'show'])->name('inertia.show');
Route::delete('/inertia/{id}', [InertiaTestController::class, 'delete'])->name('inertia.delete');


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/auth.php';
```

+ `app/Http/Controller/PurchaseController.php`を編集<br>

```php:PurchaseController.php
<?php

namespace App\Http\Controllers;

use App\Models\Customer; // 追加
use App\Models\Item; // 追加
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
    // 編集
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
    // ここまで

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

+ `$ mkdir resources/js/Pages/Purchases && $_/Create.vue`を実行<br>

+ `resources/js/pages/Purchases/Create.vue`を編集<br>

```php:Create.vue
<script setup>

</script>

<template>
  purchases/create
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
                                <!-- 追加 -->
                                <BreezeNavLink :href="route('purchases.create')" :active="route().current('purchases.create')">
                                    購入画面
                                </BreezeNavLink>
                                <!-- ここまで -->
                                <BreezeNavLink :href="route('items.index')" :active="route().current('items.index')">
                                    商品管理
                                </BreezeNavLink>
                                <BreezeNavLink :href="route('customers.index')" :active="route().current('customers.index')">
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
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
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
                            <button @click="showingNavigationDropdown = ! showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <BreezeResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </BreezeResponsiveNavLink>
                        <!-- 追加 -->
                        <BreezeResponsiveNavLink :href="route('purchases.create')" :active="route().current('purchases.create')">
                            購入画面
                        </BreezeResponsiveNavLink>
                        <!-- ここまで -->
                        <BreezeResponsiveNavLink :href="route('items.index')" :active="route().current('items.index')">
                            商品管理
                        </BreezeResponsiveNavLink>
                        <BreezeResponsiveNavLink :href="route('customers.index')" :active="route().current('customers.index')">
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
