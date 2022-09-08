# セクション6: CRM 購買履歴画面

## 79. ダミーデータ 3万件投入

+ `database/factries/PurchaseFactory.php`を編集<br>

```php:PurchaseFactory.php
<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // 追加
        $decade = $this->faker->dateTimeThisDecade;
        $created_at = $decade->modify('+2 years');
        // ここまで

        return [
            'customer_id' => rand(1, Customer::count()),
            'status' => $this->faker->boolean,
            'created_at' => $created_at, // 追加
        ];
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
        ]);

        Customer::factory(1000)->create(); // 編集

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

## 80. joinで4つのテーブルを紐付けて合計金額を表示(mysql版)

### 購買履歴一覧

+ 欲しい情報<br>
+ 購買id、顧客名、合計金額、ステータス、購入日<br>

+ サブクエリ<br>

```
4つのテーブルをjoin
金額 * 数量 = 小計 を表示
```

+ サブクエリで生成したテーブルをもとに group by で 売買毎の合計金額を表示<br>

## 81. グローバルスコープ

```
先々、サブクエリを元にクエリスコープを何度か実施する事を想定
(ex 期間指定、日別、月別、商品別・・)

今回のサブクエリ・・4つのテーブルをjoinし小計を出している状態

新たにモデルを作成し、グローバルスコープ(モデル全体に適用)を使い
サブクエリ実施後の状態にする
```

+ [クエリスコープ (グローバルスコープ)](https://readouble.com/laravel/9.x/ja/eloquent.html) <br>

+ `$ php artisan make:scop Subtotal`を実行<br>

+ `$ php artisan make:model Order`を実行<br>

+ `app/Models/Order.php`を編集<br>

```php:Order.php
<?php

namespace App\Models;

use App\Models\Scopes\Subtotal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new Subtotal);
    }
}
```

+ `app/Models/Scopes/Subtotal.php`を編集<br>

```php:Subtotal.php
<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Subtotal implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $sql = 'select purchases.id as id
        , item_purchase.id as pivot_id
        , items.price * item_purchase.quantity as subtotal
        , customers.name as customer_name
        , items.name as item_name
        , items.price as item_price
        , item_purchase.quantity
        , purchases.status
        , purchases.created_at
        , purchases.updated_at
        from purchases
        left join item_purchase on purchases.id = item_purchase.purchase_id
        left join items on item_purchase.item_id = items.id
        left join customers on purchases.customer_id = customers.id
        ';

        $builder->fromSub($sql, 'order_subtotals');
    }
}
```

+ `app/Http/Controllers/PurChaseController.php`を編集<br>

```php:PurChaseController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Item;
use App\Models\Order;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        dd(Order::paginate(50)); // 大量データの場合paginateを使わないとメモリエラーになる
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $items = Item::select('id', 'name', 'price')
            ->where('is_selling', true)->get();

        return Inertia::render('Purchases/Create', [
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        // dd($request);

        DB::beginTransaction();

        try {
            $purchase = Purchase::create([
                'customer_id' => $request->customer_id,
                'status' => $request->status
            ]);

            foreach ($request->items as $item) {
                $purchase->items()->attach($purchase->id, [
                    'item_id' => $item['id'],
                    'quantity' => $item['quantity']
                ]);
            }

            DB::commit();

            return to_route('dashboard');
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
```

+ http://localhost/purchases にアクセスしてみる<br>
