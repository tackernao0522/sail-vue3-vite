## 94. Api通信(Ajax通信)の通信確認

### 期間指定 修正

+ `app/Models/Order.php`を編集<br>

```php:Order.php
<?php

namespace App\Models;

use App\Models\Scopes\Subtotal;
use Carbon\Carbon; // 追加
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new Subtotal);
    }

    public function scopeBetweenDate($query, $startDate = null, $endDate = null)
    {
        if (is_null($startDate) && is_null($endDate)) {
            return $query;
        }

        if (!is_null($startDate) && is_null($endDate)) {
            return $query->where('created_at', ">=", $startDate);
        }

        if (is_null($startDate) && !is_null($endDate)) {
            $endDate1 = Carbon::parse($endDate)->addDays(1); // 修正
            return $query->where('created_at', '<=', $endDate1); // 修正
        }

        if (!is_null($startDate) && !is_null($endDate)) {
            $endDate1 = Carbon::parse($endDate)->addDays(1); // 修正
            return $query->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate1); // 修正
        }
    }
}
```

+ `$ php artisan make:controller Api/AnalysisController`を実行<br>

+ `routes/api.php`を編集<br>

```php:api.php
<?php

use App\Http\Controllers\Api\AnalysisController; // 追加
use App\Models\Customer;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/searchCustomers', function (Request $request) {
    return Customer::searchCustomers($request->search)
        ->select('id', 'name', 'kana', 'tel')->paginate(50);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:sanctum')->get('/analysis', [AnalysisController::class, 'index'])
    ->name('api.analysis'); // 追加
```

+ `app/Http/Controllers/Api/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        // Ajax通信なのでJsonで返却する必要がある
        return response()->json([
            'data' => $request->startDate // 仮設定
        ], Response::HTTP_OK);
    }
}
```

+ `resources/js/Pages/Analysis.vue`を編集<br>

```vue:Analysis.vue
<script setup>
import { getToday } from '@/common';
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios'; // 追加
import { onMounted, reactive } from 'vue';


onMounted(() => {
    form.startDate = getToday()
    form.endDate = getToday()
})

const form = reactive({
    startDate: null,
    endDate: null,
    type: 'perDay' // 追加 仮で直入力
})

// 追加
const getData = async () => {
    try {
        await axios.get('/api/analysis/', {
            params: {
                startDate: form.startDate,
                endDate: form.endDate,
                type: form.type
            }
        })
            .then((res) => {
                // data.value = res.data
                console.log(res.data)
            })
    } catch (e) {
        console.log(e.message)
    }
}
// ここまで
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
                        <form @submit.prevent="getData"> <!-- 編集 -->
                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>
                            <button
                                class="mt-4 flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">分析する</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```

## 95. 日別売上のクエリ作成・取得確認

### 日別売上のクエリ(練習)

1. 購買id毎の売り上げをまとめ、dateをフォーマットした状態のサブクエリをつくる<br>
2. サブクエリをgroupByで日毎まとまる<br>

+ `app/Http/Controllers/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // 追加
use Inertia\Inertia;

use function Termwind\render;

class AnalysisController extends Controller
{
    public function index()
    {
        // 期間指定
        $startDate = '2022-08-01';
        $endDate = '2022-8-31';

        // $period = Order::betweenDate($startDate, $endDate)
        //     ->groupBy('id')
        //     ->selectRaw('id, sum(subtotal) as total,
        // customer_name, status, created_at')
        //     ->orderBy('created_at')
        //     ->paginate(50);

        // dd($period);

        // 追加
        // 日別
        $subQuery = Order::betweenDate($startDate, $endDate)->where('status', true)->groupBy('id')
            ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');
        $data = DB::table($subQuery)
            ->groupBy('date')
            ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        dd($data);
        // ここまで


        return Inertia::render('Analysis');
    }
}
```

### 本番用

+ `app/Http/Controllers/Api/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order; // 追加
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB; // 追加

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if ($request->type === 'perDay') {
            $subQuery->where('status', true)
                ->groupBy('id')->selectRaw('SUM(subtotal) AS totalPerPerchase, DATE_FORMAT(created_at, "%Y%m%d") AS date')
                ->groupBy('date');

            $data = DB::table($subQuery)
                ->groupBy('date')
                ->selectRaw('date, sum(totalPerPerchase) as total')
                ->get();
        }

        return response()->json([
            'data' => $data,
            'type' => $request->type
        ], Response::HTTP_OK);
    }
}
```

## 97. 日別売上のテーブル表示

+ `resources/js/Pages/Analysis.vue`を編集<br>

```vue:Analysis.vue
<script setup>
import { getToday } from '@/common';
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { onMounted, reactive } from 'vue';


onMounted(() => {
    form.startDate = getToday()
    form.endDate = getToday()
})

const form = reactive({
    startDate: null,
    endDate: null,
    type: 'perDay' // 仮で直入力
})

const data = reactive({}) // 追加

const getData = async () => {
    try {
        await axios.get('/api/analysis/', {
            params: {
                startDate: form.startDate,
                endDate: form.endDate,
                type: form.type
            }
        })
            .then((res) => {
                data.data = res.data.data // 編集
                console.log(res.data)
            })
    } catch (e) {
        console.log(e.message)
    }
}
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
                        <form @submit.prevent="getData">
                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>
                            <button
                                class="mt-4 flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">分析する</button>
                        </form>

                        <!-- 追加 -->
                        <div v-show="data.data" class="lg:w-2/3 w-full mx-auto overflow-auto">
                            <table class="table-auto w-full text-left whitespace-no-wrap">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                                            年月日</th>
                                        <th
                                            class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                            金額</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in data.data" :key="item.date">
                                        <td class="px-4 py-3">{{ item.date }}</td>
                                        <td class="px-4 py-3">{{ item.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- ここまで -->

                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```
