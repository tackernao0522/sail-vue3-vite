## 110. RFM分析3 RとFの2次元でデータ取得

### 分析結果イメージ

|R|F5|F4|F3|F2|F1|
|:---:|:---:|:---:|:---:|:---:|:---:|
|5|10|||||
|4|||20|||
|3||||||
|2||||||
|1||||||

+ `app/Http/Controllers/AnalysisController.php`を編集<br>

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
        $subQuery = DB::table($subQuery)
            ->selectRaw('customer_id, customer_name,
        recentDate, recency, frequency, monetary,
        case
            when recency < 14 then 5
            when recency < 28 then 4
            when recency < 60 then 3
            when recency < 90 then 2
            else 1 end as r,
        case
            when 7 <= frequency then 5
            when 5 <= frequency then 4
            when 3 <= frequency then 3
            when 2 <= frequency then 2
            else 1 end as f,
        case
            when 300000 <= monetary then 5
            when 200000 <= monetary then 4
            when 100000 <= monetary then 3
            when 30000 <= monetary then 2
            else 1 end as m
        ');

        // dd($subQuery);

        // 5. ランク毎の数を計算する
        $total = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
            ->groupBy('r')
            ->selectRaw('r, count(r)')
            ->orderBy('r', 'desc')
            ->get();

        $fCount = DB::table($subQuery)
            ->groupBy('f')
            ->selectRaw('f, count(f)')
            ->orderBy('f', 'desc')
            ->get();

        $mCount = DB::table($subQuery)
            ->groupBy('m')
            ->selectRaw('m, count(m)')
            ->orderBy('m', 'desc')
            ->get();

        // dd($total, $rCount, $fCount, $mCount);

        // 追加
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

        dd($data);
        // ここまで

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

## 111. RFM分析 描画前の準備

```
３つの指標を５つのグループに分ける
2にorderBy()をつけてあたりをつけ、仮で設定しておく
(今回は2021-09-01〜2022-08-31の1年間と想定)
orderBy('recency'), orderBy('frequency', 'desc'),
orderBy('monetary', 'desc')
後程inputタグでユーザー側から変更できるようにする
```

|ランク|R 最新購入日|F 累計購入回数|M 累計購入金額|
|:---:|:---:|:---:|:---:|
|5|14日以内|7回以上|30万円以上|
|4|28日以内|5回以上|20万円以上|
|3|60日以内|3回以上|10万円以上|
|2|90日以内|2回以上|3万円以上|
|1|91日以上|1回のみ|3万円未満|

### クエリの調整(プレースホルダ `?`のこと)

```
View側から調整できるように対応
動作確認用の仮配列を作る
```

+ `app/Http/Controllers/AnalysisController.php`を編集<br>

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
        // 追加
        $rfmPrms = [
            14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
        ];
        // ここまで

        // 編集
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
        ', $rfmPrms)->get();

        dd($subQuery);
        // ここまで

        // 5. ランク毎の数を計算する
        $total = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
            ->groupBy('r')
            ->selectRaw('r, count(r)')
            ->orderBy('r', 'desc')
            ->get();

        $fCount = DB::table($subQuery)
            ->groupBy('f')
            ->selectRaw('f, count(f)')
            ->orderBy('f', 'desc')
            ->get();

        $mCount = DB::table($subQuery)
            ->groupBy('m')
            ->selectRaw('m, count(m)')
            ->orderBy('m', 'desc')
            ->get();

        // dd($total, $rCount, $fCount, $mCount);

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

        dd($data);

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

### RFMランク毎の人数 配列作成

```
View側に渡す情報 RFMランク毎の人数
１つの配列で用意するとVue側でv-forで簡単に表示できる
```

+ `app/Http/Controllers/AnalysisController.php`を編集<br>

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
        ', $rfmPrms); // 編集 get()を外す

        // dd($subQuery);

        // 5. ランク毎の数を計算する
        $total = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
            ->groupBy('r')
            ->selectRaw('r, count(r)')
            ->orderBy('r', 'desc')
            ->pluck('count(r)'); // 編集

        $fCount = DB::table($subQuery)
            ->groupBy('f')
            ->selectRaw('f, count(f)')
            ->orderBy('f', 'desc')
            ->pluck('count(f)'); // 編集

        $mCount = DB::table($subQuery)
            ->groupBy('m')
            ->selectRaw('m, count(m)')
            ->orderBy('m', 'desc')
            ->pluck('count(m)'); // 編集

        $eachCount = []; // 追加 Vue側に渡す用の空の入れる
        $rank = 5; // 追加 初期値5

        // 追加
        for ($i = 0; $i < 5; $i++) {
            array_push($eachCount, [
                'rank' => $rank,
                'r' => $rCount[$i],
                'f' => $fCount[$i],
                'm' => $mCount[$i],
            ]);

            $rank--; // rankを1ずつ減らす
        }

        dd($total, $eachCount, $rCount, $fCount, $mCount);
        // ここまで

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

        dd($data);

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
