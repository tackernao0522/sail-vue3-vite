## 112. RFMランク用のテーブルをView側に追加

+ `app/Http/Controllers/AnalysisController.php`の`dd();`をコメントアウトしておく<br>

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
    type: 'perDay', // 仮で直入力
    // 追加
    rfmPrms: [
        14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000,
        30000],
    // ここまで
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
                            <input type="radio" v-model="form.type" value="decile"><span class="mr-2">デシル分析</span>
                            <!-- 追加 -->
                            <input type="radio" v-model="form.type" value="rfm"><span class="mr-2">RFM分析</span>
                            <!-- ここまで -->
                            <br>

                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>

                            <!-- 追加 -->
                            <div v-if="form.type === 'rfm'">
                                RFM表示
                            </div>
                            <!-- ここまで -->
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

### Analysis.vue RFMランク用のテーブルをView側に追加

+ 配列の順番がRの5->4->3->2 次が Fの5->4->3->2 の順になるので注意<br>

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
    type: 'perDay', // 仮で直入力
    rfmPrms: [
        14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000,
        30000],
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
                            <input type="radio" v-model="form.type" value="decile"><span class="mr-2">デシル分析</span>
                            <input type="radio" v-model="form.type" value="rfm"><span class="mr-2">RFM分析</span>
                            <br>

                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>

                            <!-- 編集 -->
                            <div v-if="form.type === 'rfm'" class="my-8">
                                <table class="mx-auto">
                                    <thead>
                                        <tr>
                                            <th>ランク</th>
                                            <th>R (○日以内)</th>
                                            <th>F (○回以上)</th>
                                            <th>M (○円以上)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>5</td>
                                            <td><input type="number" v-model="form.rfmPrms[0]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[4]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[8]"></td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td><input type="number" v-model="form.rfmPrms[1]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[5]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[9]"></td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td><input type="number" v-model="form.rfmPrms[2]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[6]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[10]"></td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td><input type="number" v-model="form.rfmPrms[3]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[7]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[11]"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- ここまで -->
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

## 113. RFMServiceを作成し非同期で受け取れるところまで対応

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
    type: 'perDay', // 仮で直入力
    rfmPrms: [
        14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000,
        30000],
})

const data = reactive({})

const getData = async () => {
    try {
        await axios.get('/api/analysis/', {
            params: {
                startDate: form.startDate,
                endDate: form.endDate,
                type: form.type,
                rfmPrms: form.rfmPrms // 追加
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
                            <input type="radio" v-model="form.type" value="decile"><span class="mr-2">デシル分析</span>
                            <input type="radio" v-model="form.type" value="rfm"><span class="mr-2">RFM分析</span>
                            <br>

                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>

                            <div v-if="form.type === 'rfm'" class="my-8">
                                <table class="mx-auto">
                                    <thead>
                                        <tr>
                                            <th>ランク</th>
                                            <th>R (○日以内)</th>
                                            <th>F (○回以上)</th>
                                            <th>M (○円以上)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>5</td>
                                            <td><input type="number" v-model="form.rfmPrms[0]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[4]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[8]"></td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td><input type="number" v-model="form.rfmPrms[1]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[5]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[9]"></td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td><input type="number" v-model="form.rfmPrms[2]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[6]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[10]"></td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td><input type="number" v-model="form.rfmPrms[3]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[7]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[11]"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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

+ `$ touch app/Services/RFMService.php`を実行<br>

+ `app/Http/Controllers/Api/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AnalysisService;
use App\Services\DecileService;
use App\Services\RFMSservice; // 追加
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

        if ($request->type === 'decile') {
            list($data, $labels, $totals) = DecileService::decile($subQuery);
        }

        // 追加
        if ($request->type === 'rfm') {
            list($data, $labels, $totals) = RFMSservice::rfm($subQuery, $request->rfmPrms);
        }
        // ここまで

        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals
        ], Response::HTTP_OK);
    }
}
```

+ `app/Http/Controllers/AnalysisController.php`を編集(備忘録用)<br>

```php:AnalysisController.php
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
        $startDate = '2021-09-01';
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

        // 切り取り

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

    // 追加
    public function rfm()
    {
        // 期間指定
        $startDate = '2021-09-01';
        $endDate = '2022-8-31';

        // RFM分析
        // 1. 購買ID毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
            ->groupBy('id')
            ->selectRaw('id, customer_id,
        customer_name, SUM(subtotal) as totalPerPurchase, created_at
        ');

        // datediffで日付の差分、maxで日付の最新日
        // 2. 会員毎にまとめて最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, customer_name,
        max(created_at) as recentDate,
        datediff(now(), max(created_at)) as recency,
        count(customer_id) as frequency,
        sum(totalPerPurchase) as monetary
        ');

        // dd($subQuery);

        // 4. 会員毎のRFMランクを計算
        $rfmPrms = [
            14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
        ];

        $subQuery = DB::table($subQuery)
            ->selectRaw('customer_id, customer_name,
        recentDate, recency, frequency, monetary,
        case
            when recency < ? then 5
            when recency < ? then 4
            when recency < ? then 3
            when recency < ? then 2
            else 1 end as r,
        case
            when ? <= frequency then 5
            when ? <= frequency then 4
            when ? <= frequency then 3
            when ? <= frequency then 2
            else 1 end as f,
        case
            when ? <= monetary then 5
            when ? <= monetary then 4
            when ? <= monetary then 3
            when ? <= monetary then 2
            else 1 end as m
        ', $rfmPrms);

        // dd($subQuery);

        // 5. ランク毎の数を計算する
        $total = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
            ->groupBy('r')
            ->selectRaw('r, count(r)')
            ->orderBy('r', 'desc')
            ->pluck('count(r)');

        $fCount = DB::table($subQuery)
            ->groupBy('f')
            ->selectRaw('f, count(f)')
            ->orderBy('f', 'desc')
            ->pluck('count(f)');

        $mCount = DB::table($subQuery)
            ->groupBy('m')
            ->selectRaw('m, count(m)')
            ->orderBy('m', 'desc')
            ->pluck('count(m)');

        $eachCount = []; // Vue側に渡す用の空の入れる
        $rank = 5; // 初期値5

        for ($i = 0; $i < 5; $i++) {
            array_push($eachCount, [
                'rank' => $rank,
                'r' => $rCount[$i],
                'f' => $fCount[$i],
                'm' => $mCount[$i],
            ]);

            $rank--; // rankを1ずつ減らす
        }

        // dd($total, $eachCount, $rCount, $fCount, $mCount);

        // concatで文字列結合
        // 6. RとFで2次元で表示してみる
        $data = DB::table($subQuery)
            ->groupBy('r')
            ->selectRaw('concat("r_", r) as rRank,
            count(case when f = 5 then 1 end ) as f_5,
            count(case when f = 4 then 1 end ) as f_4,
            count(case when f = 3 then 1 end ) as f_3,
            count(case when f = 2 then 1 end ) as f_2,
            count(case when f = 1 then 1 end ) as f_1')
            ->orderBy('rRank', 'desc')
            ->get();

        // dd($data);
    }
}
```

+ `app/Services/RFMService.php`を編集<br>

```php:RFMService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RFMService
{
  public static function rfm($subQuery, $rfmPrms)
  {
    // RFM分析
    // 1. 購買ID毎にまとめる
    $subQuery = $subQuery->groupBy('id')
      ->selectRaw('id, customer_id,
        customer_name, SUM(subtotal) as totalPerPurchase, created_at
        ');

    // datediffで日付の差分、maxで日付の最新日
    // 2. 会員毎にまとめて最終購入日、回数、合計金額を取得
    $subQuery = DB::table($subQuery)
      ->groupBy('customer_id')
      ->selectRaw('customer_id, customer_name,
        max(created_at) as recentDate,
        datediff(now(), max(created_at)) as recency,
        count(customer_id) as frequency,
        sum(totalPerPurchase) as monetary
        ');

    // dd($subQuery);

    // 4. 会員毎のRFMランクを計算
    // $rfmPrms = [
    //   14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
    // ];

    $subQuery = DB::table($subQuery)
      ->selectRaw('customer_id, customer_name,
        recentDate, recency, frequency, monetary,
        case
            when recency < ? then 5
            when recency < ? then 4
            when recency < ? then 3
            when recency < ? then 2
            else 1 end as r,
        case
            when ? <= frequency then 5
            when ? <= frequency then 4
            when ? <= frequency then 3
            when ? <= frequency then 2
            else 1 end as f,
        case
            when ? <= monetary then 5
            when ? <= monetary then 4
            when ? <= monetary then 3
            when ? <= monetary then 2
            else 1 end as m
        ', $rfmPrms);

    // dd($subQuery);

    // 5. ランク毎の数を計算する
    $totals = DB::table($subQuery)->count();

    $rCount = DB::table($subQuery)
      ->groupBy('r')
      ->selectRaw('r, count(r)')
      ->orderBy('r', 'desc')
      ->pluck('count(r)');

    $fCount = DB::table($subQuery)
      ->groupBy('f')
      ->selectRaw('f, count(f)')
      ->orderBy('f', 'desc')
      ->pluck('count(f)');

    $mCount = DB::table($subQuery)
      ->groupBy('m')
      ->selectRaw('m, count(m)')
      ->orderBy('m', 'desc')
      ->pluck('count(m)');

    $eachCount = []; // Vue側に渡す用の空の入れる
    $rank = 5; // 初期値5

    for ($i = 0; $i < 5; $i++) {
      array_push($eachCount, [
        'rank' => $rank,
        'r' => $rCount[$i],
        'f' => $fCount[$i],
        'm' => $mCount[$i],
      ]);

      $rank--; // rankを1ずつ減らす
    }

    // dd($total, $eachCount, $rCount, $fCount, $mCount);

    // concatで文字列結合
    // 6. RとFで2次元で表示してみる
    $data = DB::table($subQuery)
      ->groupBy('r')
      ->selectRaw('concat("r_", r) as rRank,
            count(case when f = 5 then 1 end ) as f_5,
            count(case when f = 4 then 1 end ) as f_4,
            count(case when f = 3 then 1 end ) as f_3,
            count(case when f = 2 then 1 end ) as f_2,
            count(case when f = 1 then 1 end ) as f_1')
      ->orderBy('rRank', 'desc')
      ->get();

    // dd($data);

    return [$data, $totals, $eachCount];
  }
}
```

+ `app/Http/Controllers/Api/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AnalysisService;
use App\Services\DecileService;
use App\Services\RFMService; // 追加
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

        if ($request->type === 'decile') {
            list($data, $labels, $totals) = DecileService::decile($subQuery);
        }

        // 追加
        if ($request->type === 'rfm') {
            list($totals, $data, $eachCount) = RFMService::rfm($subQuery, $request->rfmPrms);

            return response()->json([
                'data' => $data,
                'type' => $request->type,
                'eachCount' => $eachCount,
                'totals' => $totals
            ], Response::HTTP_OK);
        }
        // ここまで

        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals
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
    type: 'perDay', // 仮で直入力
    rfmPrms: [
        14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000,
        30000],
})

const data = reactive({})

const getData = async () => {
    try {
        await axios.get('/api/analysis/', {
            params: {
                startDate: form.startDate,
                endDate: form.endDate,
                type: form.type,
                rfmPrms: form.rfmPrms
            }
        })
            .then((res) => {
                // それぞれ const data = reactive({}) に入っていく
                data.data = res.data.data
                // 編集
                if (res.data.labels) {
                    data.labels = res.data.labels
                }
                if (res.data.eachCount) {
                    data.eachCount = res.data.eachCount
                }
                // ここまで
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
                            <input type="radio" v-model="form.type" value="decile"><span class="mr-2">デシル分析</span>
                            <input type="radio" v-model="form.type" value="rfm"><span class="mr-2">RFM分析</span>
                            <br>

                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>

                            <div v-if="form.type === 'rfm'" class="my-8">
                                <table class="mx-auto">
                                    <thead>
                                        <tr>
                                            <th>ランク</th>
                                            <th>R (○日以内)</th>
                                            <th>F (○回以上)</th>
                                            <th>M (○円以上)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>5</td>
                                            <td><input type="number" v-model="form.rfmPrms[0]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[4]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[8]"></td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td><input type="number" v-model="form.rfmPrms[1]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[5]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[9]"></td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td><input type="number" v-model="form.rfmPrms[2]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[6]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[10]"></td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td><input type="number" v-model="form.rfmPrms[3]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[7]"></td>
                                            <td><input type="number" v-model="form.rfmPrms[11]"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button
                                class="mt-4 flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">分析する</button>
                        </form>

                        <div v-show="data.data">
                            <!-- 編集 -->
                            <div v-if="data.type != 'rfm'">
                                <Chart :data="data" />
                            </div>
                            <!-- ここまで -->
                            <ResultTable :data="data" />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```
