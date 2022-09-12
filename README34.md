## 114. RFM分析結果を表で表示

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
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ item.decile }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ item.average }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ item.totalPerGroup }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ item.totalRatio }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- 追加 -->
  <div v-if="data.type === 'rfm'" class="lg:w-2/3 w-full mx-auto overflow-auto">
    <h2 class="text-center text-2xl my-4">RFM 分析結果</h2>
    <div class="my-4 text-center">合計: {{ data.totals }} 人</div>

    <h3 class="text-center text-2xl my-4">RFMランク毎の人数</h3>

    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">Rank</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">R</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">F</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">M</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rfm in data.eachCount" :key="rfm.rank">
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rfm.rank }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rfm.r }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rfm.f }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rfm.m }}</td>
        </tr>
      </tbody>
    </table>

    <h3 class="text-center text-2xl my-4">RとFの集計表</h3>
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">rRank</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_5</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_4</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_3</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_2</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_1</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rf in data.data" :key="rf.rRank">
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rf.rRank }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rf.f_5 }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rf.f_4 }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rf.f_3 }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rf.f_2 }}</td>
          <td class="px-4 py-3 border-b-2 border-gray-200">{{ rf.f_1 }}</td>
        </tr>
      </tbody>
    </table>
  </div>
  <!-- ここまで -->
</template>
```

## 115. デバッグ(Log), 外部結合, ranksテーブル・ダミー作成

### デバッグ

```
RFM分析の期間が短いとエラーが発生
API通信の場合はdd()で表示できないのでLogを使って原因調査
```

+ [ログメッセージの書き込み](https://readouble.com/laravel/9.x/ja/logging.html) <br>

+ `app/Services/RFMService.php`を編集<br>

```php:RFMService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // 追加

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
    Log::debug($subQuery->get()); // 追加

    // 5. ランク毎の数を計算する
    $totals = DB::table($subQuery)->count();

    $rCount = DB::table($subQuery)
      ->groupBy('r')
      ->selectRaw('r, count(r)')
      ->orderBy('r', 'desc')
      ->pluck('count(r)');

    Log::debug($rCount); // 追加

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

### 表示されない原因・対策

```
原因
クエリ取得次にRFMのランクが欠けていること
$eachCountの数がずれてうまく表示されない

対策
RFMのランクがない場合も0として設定が必要
外部結合(right outer join)を使いランクがなければnullと表示させる
```

+ `$ php artisan make:migration create_ranks_table --create=ranks`を実行<br>

+ `database/migration/create_ranks_table.php`を編集<br>

```php:create_ranks_table.php
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
        Schema::create('ranks', function (Blueprint $table) {
            $table->integer('rank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ranks');
    }
};
```

+ `$ php artisan make:seeder RanksTableSeeder`を実行<br>

+ `database/seeders/RanksTableSeeder.php`を編集<br>

```php:RanksTableSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ranks')->insert([
            [
                'rank' => 1
            ],
            [
                'rank' => 2
            ],
            [
                'rank' => 3
            ],
            [
                'rank' => 4
            ],
            [
                'rank' => 5
            ],
        ]);
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
            RanksTableSeeder::class, // 追加
        ]);

        Customer::factory(1000)->create();

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
