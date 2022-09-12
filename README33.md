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