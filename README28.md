## 99. 日別売上のグラフ表示

+ `app/Http/Controllers/Api/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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

            $labels = $data->pluck('date'); // 追加
            $totals = $data->pluck('total'); // 追加
        }

        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels, // 追加
            'totals' => $totals // 追加
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
                data.labels = res.data.labels // 追加
                data.totals = res.data.totals // 追加
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

                        <!-- 編集 -->
                        <div v-show="data.data">
                            <Chart :data="data" />
                        </div>
                        <!-- ここまで -->

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

                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>
```

+ `resources/js/Components/Chart.vue`を編集<br>

```vue:Chart.vue
<script setup>
import { Chart, registerables } from "chart.js";
import { BarChart } from "vue-chart-3";
import { computed, reactive } from "vue" // 編集

// 追加
const props = defineProps({
  'data': Object
})

const labels = computed(() => props.data.labels) // 追加
const totals = computed(() => props.data.totals) // 追加

Chart.register(...registerables);

const barData = reactive({
  labels: labels, // 編集
  datasets: [
    {
      label: '売上',
      data: totals, // 編集
      backgroundColor: "rgb(75, 192, 192)",
      tension: 0.1,
    }]
})
</script>

<template>
  <div v-show="props.data"> <!-- 編集 -->
    <BarChart :chartData="barData" />
  </div>
</template>
```

## 100. サービスへの切り離し

+ `$ mkdir app/Services && touch $_/AnalysisService.php`を実行<br>

+ `app/Services/AnalysisService.php`を編集<br>

```php:AnalysisService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnalysisService
{
  public static function perDay($subQuery)
  {
    $query = $subQuery->where('status', true)
      ->groupBy('id')->selectRaw('SUM(subtotal) AS totalPerPerchase, DATE_FORMAT(created_at, "%Y%m%d") AS date')
      ->groupBy('date')->orderBy('date');

    $data = DB::table($query)
      ->groupBy('date')
      ->selectRaw('date, sum(totalPerPerchase) as total')
      ->get();

    $labels = $data->pluck('date');
    $totals = $data->pluck('total');

    return [$data, $labels, $totals]; // 複数の変数を渡すので一旦配列に入れる
  }
}
```

+ `app/Http/Controllers/Api/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AnalysisService; // 追加
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if ($request->type === 'perDay') {
            // 配列を受け取り変数に格納するため list() を使う
            list($data, $labels, $totals) = AnalysisService::perDay($subQuery); // 編集
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
