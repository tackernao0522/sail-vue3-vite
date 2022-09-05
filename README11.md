# セクション4: CRM 顧客情報・利用登録

## 51. Customers 1000件ダミー登録

+ [Faker チートシート](https://qiita.com/tosite0345/items/1d47961947a6770053af) <br>

+ `$ php artisan make:model Customer`を実行<br>

+ `$ php artisan make:migration create_customers_table --create=customers`を実行<br>

+ `database/migraions/create_customers_table.php`を編集<br>

```php:create_customers_table.php
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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('kana');
            $table->string('tel')->unique();
            $table->string('email');
            $table->string('postcode');
            $table->string('address');
            $table->date('birthday')->nullable();
            $table->tinyInteger('gender'); // 0男性, 1女性, 2その他
            $table->text('memo')->nullable();
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
        Schema::dropIfExists('customers');
    }
};
```

+ `config/app.php`を編集<br>

```php:app.php
    // 略
    'fallback_locale' => 'ja',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'ja_JP', // 編集
    // 略
```

+ `$ php artisan make:factory CustomerFactory`を実行<br>

+ `database/factories/CustomerFactory.php`を編集<br>

```php:CustomerFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'kana' => $this->faker->kanaName,
            'tel' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'postcode' => $this->faker->postcode,
            'address' => $this->faker->address,
            'birthday' => $this->faker->dateTime,
            'gender' => $this->faker->numberBetween(0, 2),
            'memo' => $this->faker->realText(50),
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