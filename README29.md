## 102. デシル分析1 解析毎にまとめて金額順にソートする

```:html
<h1>デシル・・ラテン語で10等分</h1>

データを10分割してグループに分ける分析手法

一般的に今日入金額に応じてグループ分け

ex)
上位グループの特典をアップ
下位グループにDM送付
```

### デシル分析の流れ

1. 購買ID毎にまとめる <br>
2. 会員毎にまとめて購入金額順にソートする <br>
3. 購入順に連番を振る <br>
4. 全体の件数を数え、1/10の値や合計金額を取得 <br>
5. 10分割しグループ毎に数字を振る <br>
6. 各グループの合計金額・平均金額を表示 <br>
7. 構成比を表示（おまけ） <br>

※ Mysql8.0のWindow関数(ntile())が使えると4, 5をまとめられるけど今回はcaseでやることにする<br>

+ まずはテスト的に `app/Http/Controllers/AnalysisController.php`を編集<br>

```php:AnalysisController.php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // 日別
        // $subQuery = Order::betweenDate($startDate, $endDate)->where('status', true)->groupBy('id')
        //     ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');
        // $data = DB::table($subQuery)
        //     ->groupBy('date')
        //     ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        // dd($data);

        // 追加
        // 1. 購買ID毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
            ->groupBy('id')
            ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase');

        // 2. 会員毎にまとめて購入金額順にソートする
        $subQuery = DB::table($subQuery)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, customer_name, sum(totalPerPurchase) as total')
            ->orderBy('total', 'desc')->get();

        dd($subQuery);

        return Inertia::render('Analysis');
        // ここまで
    }
}
```
