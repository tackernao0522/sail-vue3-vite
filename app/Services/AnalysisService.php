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

  public static function perMonth($subQuery)
  {
    $query = $subQuery->where('status', true)
      ->groupBy('id')
      ->selectRaw('id, sum(subtotal) as totalPerPurchase,
      DATE_FORMAT(created_at, "%Y%m") as date')
      ->orderBy('date');

    $data = DB::table($query)
      ->groupBy('date')
      ->selectRaw('date, sum(totalPerPurchase) as total')
      ->get();

    $labels = $data->pluck('date');
    $totals = $data->pluck('total');

    return [$data, $labels, $totals];
  }

  public static function perYear($subQuery)
  {
    $query = $subQuery->where('status', true)
      ->groupBy('id')
      ->selectRaw('id, SUM(subtotal) AS totalPerPerchase, DATE_FORMAT(created_at, "%Y") AS date')
      ->orderBy('date');

    $data = DB::table($query)
      ->groupBy('date')
      ->selectRaw('date, sum(totalPerPerchase) as total')
      ->get();

    $labels = $data->pluck('date');
    $totals = $data->pluck('total');

    return [$data, $labels, $totals];
  }
}
