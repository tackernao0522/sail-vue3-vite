# セクション5: CRM 購入画面

## 62. Purchase モデル・マイグレーション・リレーション

+ `$ php artisan make:model Purchase`を実行<br>

+ `$ php artisan make:migration create_purchases_table --create=purchases`を実行<br>

+ `database/migrations/create_purchases_table.php`を編集<br>

```php:create_purchases_table.php
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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()
                ->onUpdate('cascade');
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
```

+ `app/Models/Purchase.php`を編集<br>

```php:Purchase.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];
}
```

+ `app/Models/Customer.php`を編集<br>

```php:Customer.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase; // 追加

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeSearchCustomers($query, $input = null)
    {
        if (!empty($input)) {
            if (Customer::where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%')->exists()) {
                return $query->where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%');
            }
        }
    }

    // 追加
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
```

+ `app/Models/Purchase.php`を編集<br>

```php:Purchase.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer; // 追加

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];

    // 追加
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
```

+ `$ php artisan make:factory PurchaseFactory`を実行<br>

+ `database/factories/PurchaseFactory.php`を編集<br>

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
        return [
            'customer_id' => rand(1, Customer::count()),
            'status' => $this->faker->boolean,
        ];
    }
}
```

+ `database/seeders/DatabaseSeeder.php`を編集<br>

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        \App\Models\Customer::factory(1000)->create();
        \App\Models\Purchase::factory(100)->create();

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            UsersTableSeeder::class,
            ItemsTableSeeder::class,
        ]);
    }
}
```

+ `$ php artisan migrate:fresh --seed`を実行<br>

## 63. ItemPurchase マイグレーション・リレーション

+ `$ php artisan make:migration create_item_purchase_table --create=item_purchase`を実行<br>

+ `database/migration/create_item_purchase_table.php`を編集<br>

```php:create_item_purchase_table.php
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
        Schema::create('item_purchase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->constrained()
                ->onUpdate('cascade');
            $table->foreignId('purchase_id')
                ->constrained()
                ->onUpdate('cascade');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_purchase');
    }
};
```

+ `app/Models/Purchase.php`を編集<br>

```php:Purchase.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // 追加
    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }
}
```

+ `app/Models/Item.php`を編集<br>

```php:Item.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'memo',
        'price',
        'is_selling'
    ];

    public function purchases()
    {
        return $this->belongsToMany(Purchase::class)->withPivot('quatity');
    }
}
```

## 64. 中間テーブルのダミーデータ作成

+ [eachメソッド](https://readouble.com/laravel/9.x/ja/collections.html#method-each) <br>

+ [many to many の attach](https://readouble.com/laravel/9.x/ja/eloquent-relationships.html#many-to-many) <br>

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

        Customer::factory(1000)->create();

        $items = Item::all();

        Purchase::factory(100)->create()
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
