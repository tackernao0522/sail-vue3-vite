# セクション7: データ分析

```
CRM(顧客管理システム)の機能の一つ
沢山のデータを分析して視覚化する

分析する方法は多数あるが今回は、
日別、月別、年別、デシル分析、RFM分析を主に対応していく。
```

## 91. Analysisページ追加

+ `$ php artisan make:controller AnalysisController`を実行<br>

+ `routes/web.php

```php:web.php
<?php

use App\Http\Controllers\AnalysisController; // 追加
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InertiaTestController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::resource('items', ItemController::class)->middleware(['auth', 'verified']);

Route::resource('customers', CustomerController::class)->middleware(['auth', 'verified']);

Route::resource('purchases', PurchaseController::class)->middleware(['auth', 'verified']);

Route::get('analysis', [AnalysisController::class, 'index'])->name('analysis'); // 追加

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

+ `app/Http/Controllers/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use function Termwind\render;

class AnalysisController extends Controller
{
    public function index()
    {
        return Inertia::render('Analysis');
    }
}
```

+ `$ cp resources/js/Pages/Dashboard.vue resources/js/Pages/Analysis.vue`を実行<br>

+ `resources/js/Pages/Analysis.vue`を編集<br>

```vue:Analysis.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
</script>

<template>
    <Head title="データ分析" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                データ分析
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        You're logged in!
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
                                <BreezeNavLink :href="route('purchases.index')"
                                    :active="route().current('purchases.index')">
                                    購買履歴
                                </BreezeNavLink>
                                <BreezeNavLink :href="route('items.index')" :active="route().current('items.index')">
                                    商品管理
                                </BreezeNavLink>
                                <BreezeNavLink :href="route('customers.index')"
                                    :active="route().current('customers.index')">
                                    顧客管理
                                </BreezeNavLink>
                                <!-- 追加 -->
                                <BreezeNavLink :href="route('analysis')"
                                    :active="route().current('analysis')">
                                    データ分析
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
                        <BreezeResponsiveNavLink :href="route('purchases.index')"
                            :active="route().current('purchases.index')">
                            購買履歴
                        </BreezeResponsiveNavLink>
                        <BreezeResponsiveNavLink :href="route('items.index')" :active="route().current('items.index')">
                            商品管理
                        </BreezeResponsiveNavLink>
                        <BreezeResponsiveNavLink :href="route('customers.index')"
                            :active="route().current('customers.index')">
                            顧客管理
                        </BreezeResponsiveNavLink>
                        <!-- 追加 -->
                        <BreezeResponsiveNavLink :href="route('analysis')"
                            :active="route().current('analysis')">
                            データ分析
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

## 92. 期間を指定する

```
どの分析においても、何年何月何日から　何年何月何日 までという情報は必要
```

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

    // グローバルスコープ
    protected static function booted()
    {
        static::addGlobalScope(new Subtotal);
    }

    // 追加
    // ローカルスコープ
    public function scopeBetweenDate($query, $startDate = null, $endDate = null)
    {
        if (is_null($startDate) && is_null($endDate)) {
            return $query;
        }

        if (!is_null($startDate) && is_null($endDate)) {
            return $query->where('created_at', ">=", $startDate);
        }

        if (is_null($startDate) && !is_null($endDate)) {
            return $query->where('created_at', '<=', $endDate);
        }

        if (!is_null($startDate) && !is_null($endDate)) {
            return $query->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate);
        }
    }
}
```

+ `app/Http/Controllers/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

use function Termwind\render;

class AnalysisController extends Controller
{
    public function index()
    {
        $startDate = '2022-08-01';
        $endDate = '2022-8-31';

        $period = Order::betweenDate($startDate, $endDate)
            ->groupBy('id')
            ->selectRaw('id, sum(subtotal) as total,
        customer_name, status, created_at')
            ->orderBy('created_at')
            ->paginate(50);

        dd($period);

        return Inertia::render('Analysis');
    }
}
```

## 93. Analysis.vueのフォームを追加

+ `resources/js/Pages/Analysis.vue`を編集<br>

```vue:Analysis.vue
<script setup>
import { getToday } from '@/common'; // 追加
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { onMounted, reactive } from 'vue'; // 追加


onMounted(() => {
    form.startDate = getToday()
    form.endDate = getToday()
})

const form = reactive({
    startDate: null,
    endDate: null
})
</script>

<template>

    <Head title="データ分析" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                データ分析
            </h2>
        </template>
        <!-- 編集 -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="">
                            From: <input
                                type="date"
                                name="startDate"
                                v-model="form.startDate"
                            >
                            To: <input
                                type="date"
                                name="endDate"
                                v-model="form.endDate"
                            ><br>
                            <button class="mt-4 flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">分析する</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- ここまで -->
    </BreezeAuthenticatedLayout>
</template>
```
