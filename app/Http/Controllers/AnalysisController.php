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

        dd($count, $decile, $bindValues);

        return Inertia::render('Analysis');
    }
}
