## 106. ResultTableコンポーネントに分離する

+ `resources/js/Pages/Analysis.vue`を編集<br>

```vue:Analysis.vue
<script setup>
import { getToday } from '@/common';
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { onMounted, reactive } from 'vue';
import Chart from '../Components/Chart.vue'
import ResultTable from '@/Components/ResultTable.vue'; // 追加


onMounted(() => {
    form.startDate = getToday()
    form.endDate = getToday()
})

const form = reactive({
    startDate: null,
    endDate: null,
    type: 'perDay' // 仮で直入力
})

const data = reactive({})

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
                // それぞれ const data = reactive({}) に入っていく
                data.data = res.data.data
                data.labels = res.data.labels
                data.totals = res.data.totals
                data.type = res.data.type // 追加
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
                            分析方法<br>
                            <input type="radio" v-model="form.type" value="perDay" checked><span class="mr-2">日別</span>
                            <input type="radio" v-model="form.type" value="perMonth"><span class="mr-2">月別</span>
                            <input type="radio" v-model="form.type" value="perYear"><span class="mr-2">年別</span>
                            <br>

                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>
                            <button
                                class="mt-4 flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">分析する</button>
                        </form>

                        <div v-show="data.data">
                            <Chart :data="data" />
                            <ResultTable :data="data" /> <!-- 編集 -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```

+ `$ touch resources/js/Components/ResultTable.vue`を実行<br>

+ `resources/js/Components/ResultTable.vue`を編集<br>

```vue:ResultTable.vue
<script setup>
const props = defineProps(
  {
    'data': Object
  }
)
</script>

<template>
  <div v-if="data.type === 'perDay' || data.type === 'perMonth' || data.type === 'perYear'"
    class="lg:w-2/3 w-full mx-auto overflow-auto">
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th
            class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
            年月日</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
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
</template>
```

+ `resources/js/Pages/Analysis.vue`を編集<br>

```vue:Analysis.vue
<script setup>
import { getToday } from '@/common';
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import { onMounted, reactive } from 'vue';
import Chart from '../Components/Chart.vue'
import ResultTable from '@/Components/ResultTable.vue';


onMounted(() => {
    form.startDate = getToday()
    form.endDate = getToday()
})

const form = reactive({
    startDate: null,
    endDate: null,
    type: 'perDay' // 仮で直入力
})

const data = reactive({})

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
                // それぞれ const data = reactive({}) に入っていく
                data.data = res.data.data
                data.labels = res.data.labels
                data.totals = res.data.totals
                data.type = res.data.type
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
                            分析方法<br>
                            <input type="radio" v-model="form.type" value="perDay" checked><span class="mr-2">日別</span>
                            <input type="radio" v-model="form.type" value="perMonth"><span class="mr-2">月別</span>
                            <input type="radio" v-model="form.type" value="perYear"><span class="mr-2">年別</span>
                            <input type="radio" v-model="form.type" value="decile"><span class="mr-2">デシル分析</span> <!-- 追加 -->
                            <br>

                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>
                            <button
                                class="mt-4 flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">分析する</button>
                        </form>

                        <div v-show="data.data">
                            <Chart :data="data" />
                            <ResultTable :data="data" />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```

## 107. API通信でデシル分析結果を取得

+ `app/Http/Controllers/Api/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AnalysisService;
use App\Services\DecileService; // 追加
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if ($request->type === 'perDay') {
            // 配列を受け取り変数に格納するため list() を使う
            list($data, $labels, $totals) = AnalysisService::perDay($subQuery);
        }

        if ($request->type === 'perMonth') {
            list($data, $labels, $totals) = AnalysisService::perMonth($subQuery);
        }

        if ($request->type === 'perYear') {
            list($data, $labels, $totals) = AnalysisService::perYear($subQuery);
        }

        // 追加
        if ($request->type === 'decile') {
            list($data, $labels, $totals) = DecileService::decile($subQuery);
        }

        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals
        ], Response::HTTP_OK);
    }
}
```

+ `$ touch app/Services/DecileService.php`を実行<br>

+ `app/Services/DecileService.php`を実行<br>

```php:DecileService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DecileService
{
  public static function decile($subQuery)
  {
    // 1. 購買ID毎にまとめる
    $subQuery = $subQuery->groupBy('id')
      ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase');

    // 2. 会員毎にまとめて購入金額順にソートする
    $subQuery = DB::table($subQuery)
      ->groupBy('customer_id')
      ->selectRaw('customer_id, customer_name, sum(totalPerPurchase) as total')
      ->orderBy('total', 'desc');

    // dd($subQuery);

    // 3. 購入順に連番を振る
    DB::statement('set @row_num = 0;');
    $subQuery = DB::table($subQuery)
      ->selectRaw('
    @row_num:= @row_num+1 as row_num,
    customer_id,
    customer_name,
    total
    ');

    // dd($subQuery);

    // 4. 全体の件数を数え、1/10の値や合計金額を取得
    $count = DB::table($subQuery)->count();
    $total = DB::table($subQuery)->selectRaw('sum(total) as total')->get();
    $total = $total[0]->total; // 構成比用

    $decile = ceil($count / 10); // 10分の1の件数を変数に入れる

    $bindValues = [];
    $tempValue = 0;
    for ($i = 1; $i <= 10; $i++) {
      array_push($bindValues, 1 + $tempValue);
      $tempValue += $decile;
      array_push($bindValues, 1 + $tempValue);
    }

    // dd($count, $decile, $bindValues);

    // 5 10分割しグループ毎に数字を振る
    DB::statement('set @row_num = 0;');
    $subQuery = DB::table($subQuery)
      ->selectRaw("
    row_num,
    customer_id,
    customer_name,
    total,
    case
        when ? <= row_num and row_num < ? then 1
        when ? <= row_num and row_num < ? then 2
        when ? <= row_num and row_num < ? then 3
        when ? <= row_num and row_num < ? then 4
        when ? <= row_num and row_num < ? then 5
        when ? <= row_num and row_num < ? then 6
        when ? <= row_num and row_num < ? then 7
        when ? <= row_num and row_num < ? then 8
        when ? <= row_num and row_num < ? then 9
        when ? <= row_num and row_num < ? then 10
        end as decile
    ", $bindValues); // SelectRaw第二引数にバインドしたい数値(配列)を入れる

    // dd($subQuery);

    // 6. グループ毎の合計・平均
    $subQuery = DB::table($subQuery)
      ->groupBy('decile')
      ->selectRaw('decile,
    round(avg(total)) as average, sum(total) as totalPerGroup');

    // dd($subQuery);

    // 7 構成比
    DB::statement("set @total = ${total};");
    $data = DB::table($subQuery)
      ->selectRaw('
    decile,
    average,
    totalPerGroup,
    round(100 * totalPerGroup / @total, 1) as totalRatio
    ')->get();

    $labels = $data->pluck('decile');
    $totals = $data->pluck('totalPerGroup');

    return [$data, $labels, $totals];
  }
}
```

+ `app/Http/Controllers/AnalysisController.php`の方を備忘録ように編集しておく<br>

```php:AnalysisController
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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

        // 日別
        // $subQuery = Order::betweenDate($startDate, $endDate)->where('status', true)->groupBy('id')
        //     ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');
        // $data = DB::table($subQuery)
        //     ->groupBy('date')
        //     ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        // dd($data);

        return Inertia::render('Analysis');
    }

    public function decile()
    {
        // 期間指定
        $startDate = '2022-08-01';
        $endDate = '2022-8-31';

        // 1. 購買ID毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
            ->groupBy('id')
            ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase');

        // 2. 会員毎にまとめて購入金額順にソートする
        $subQuery = DB::table($subQuery)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, customer_name, sum(totalPerPurchase) as total')
            ->orderBy('total', 'desc');

        // dd($subQuery);

        // 3. 購入順に連番を振る
        DB::statement('set @row_num = 0;');
        $subQuery = DB::table($subQuery)
            ->selectRaw('
            @row_num:= @row_num+1 as row_num,
            customer_id,
            customer_name,
            total
            ');

        // dd($subQuery);

        // 4. 全体の件数を数え、1/10の値や合計金額を取得
        $count = DB::table($subQuery)->count();
        $total = DB::table($subQuery)->selectRaw('sum(total) as total')->get();
        $total = $total[0]->total; // 構成比用

        $decile = ceil($count / 10); // 10分の1の件数を変数に入れる

        $bindValues = [];
        $tempValue = 0;
        for ($i = 1; $i <= 10; $i++) {
            array_push($bindValues, 1 + $tempValue);
            $tempValue += $decile;
            array_push($bindValues, 1 + $tempValue);
        }

        // dd($count, $decile, $bindValues);

        // 5 10分割しグループ毎に数字を振る
        DB::statement('set @row_num = 0;');
        $subQuery = DB::table($subQuery)
            ->selectRaw("
            row_num,
            customer_id,
            customer_name,
            total,
            case
                when ? <= row_num and row_num < ? then 1
                when ? <= row_num and row_num < ? then 2
                when ? <= row_num and row_num < ? then 3
                when ? <= row_num and row_num < ? then 4
                when ? <= row_num and row_num < ? then 5
                when ? <= row_num and row_num < ? then 6
                when ? <= row_num and row_num < ? then 7
                when ? <= row_num and row_num < ? then 8
                when ? <= row_num and row_num < ? then 9
                when ? <= row_num and row_num < ? then 10
                end as decile
            ", $bindValues); // SelectRaw第二引数にバインドしたい数値(配列)を入れる

        // dd($subQuery);

        // 6. グループ毎の合計・平均
        $subQuery = DB::table($subQuery)
            ->groupBy('decile')
            ->selectRaw('decile,
            round(avg(total)) as average, sum(total) as totalPerGroup');

        // dd($subQuery);
        // 7 構成比
        DB::statement("set @total = ${total};");
        $data = DB::table($subQuery)
            ->selectRaw('
            decile,
            average,
            totalPerGroup,
            round(100 * totalPerGroup / @total, 1) as totalRatio
            ')->get();

        // dd($data);
    }
}
```

+ `resources/js/Components/ResultTable.vue`を編集<br>

```vue:ResultTable.vue
<script setup>
const props = defineProps(
  {
    'data': Object
  }
)
</script>

<template>
  <div v-if="data.type === 'perDay' || data.type === 'perMonth' || data.type === 'perYear'"
    class="lg:w-2/3 w-full mx-auto overflow-auto">
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th
            class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
            年月日</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
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

  <!-- 追加 -->
  <div v-if="data.type === 'decile'" class="lg:w-2/3 w-full mx-auto overflow-auto">
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th
            class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
            グループ</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
            平均</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
            合計金額</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
            構成比</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data.data" :key="item.date">
          <td class="px-4 py-3">{{ item.decile }}</td>
          <td class="px-4 py-3">{{ item.average }}</td>
          <td class="px-4 py-3">{{ item.totalPerGroup }}</td>
          <td class="px-4 py-3">{{ item.totalRatio }}</td>
        </tr>
      </tbody>
    </table>
  </div>
  <!-- ここまで -->
</template>
```
