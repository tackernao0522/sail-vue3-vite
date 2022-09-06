## 57. Cusotmer 検索機能(Laravel側)

+ [クエリスコープ](https://readouble.com/laravel/9.x/ja/eloquent.html) <br>

+ `app/Models/Customer.php`を編集<br>

```php:Customer.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    public function scopeSearchCustomers($query, $input = null)
    {
        if (!empty($input)) {
            if (Customer::where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%')->exists()) {
                return $query->where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%');
            }
        }
    }
}
```

+ `app/Http/Controllers/CustomerController.php`を編集<br>

```php:CustomerController.php
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $getTest = Customer::select(
        //     'id',
        //     'name',
        //     'kana',
        //     'tel'
        // )->get();

        // $getPaginate = Customer::select(
        //     'id',
        //     'name',
        //     'kana',
        //     'tel'
        // )->paginate(50);

        // dd($getTest, $getPaginate);

        $customers = Customer::searchCustomers('エツイ')
            ->select(
                'id',
                'name',
                'kana',
                'tel'
            )->paginate(50);

        dd($customers);

        return Inertia::render('Customers/Index', [
            'customers' => Customer::select(
                'id',
                'name',
                'kana',
                'tel'
            )->paginate(50)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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