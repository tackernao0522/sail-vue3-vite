## 60. Customer 保存処理

+ `app/Models/Customer.php`を編集<br>

```php:Customer.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = []; // 追加

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

+ `$ php artisan make:request StoreCustomerRequest`を実行<br>

+ `app/Http/Requests/StoreCustomerRequest.php`を編集<br>

```php:StoreCustomerRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'max:50'],
            'kana' => ['required', 'regex:/^[ァ-ヾ]+$/u', 'max:50'],
            'tel' => ['required', 'max:20', 'unique:customers,tel'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'postcode' => ['required', 'max7'],
            'address' => ['required', 'max:100'],
            'birthday' => ['date'],
            'gender' => ['required'],
            'memo' => ['max:1000'],
        ];
    }
}
```

+ `lang/ja/validation.php`を編集<br>

```php:validation.php
<?php

return [
  /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はバリデタークラスにより使用されるデフォルトのエラー
    | メッセージです。サイズルールのようにいくつかのバリデーションを
    | 持っているものもあります。メッセージはご自由に調整してください。
    |
    */

  'accepted' => ':attributeを承認してください。',
  'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
  'active_url' => ':attributeが有効なURLではありません。',
  'after' => ':attributeには、:dateより後の日付を指定してください。',
  'after_or_equal' => ':attributeには、:date以降の日付を指定してください。',
  'alpha' => ':attributeはアルファベットのみがご利用できます。',
  'alpha_dash' =>
  ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
  'alpha_num' => ':attributeはアルファベット数字がご利用できます。',
  'array' => ':attributeは配列でなくてはなりません。',
  'before' => ':attributeには、:dateより前の日付をご利用ください。',
  'before_or_equal' => ':attributeには、:date以前の日付をご利用ください。',
  'between' => [
    'numeric' => ':attributeは、:minから:maxの間で指定してください。',
    'file' => ':attributeは、:min kBから、:max kBの間で指定してください。',
    'string' => ':attributeは、:min文字から、:max文字の間で指定してください。',
    'array' => ':attributeは、:min個から:max個の間で指定してください。',
  ],
  'boolean' => ':attributeは、trueかfalseを指定してください。',
  'confirmed' => ':attributeと、確認フィールドとが、一致していません。',
  'current_password' => 'パスワードが正しくありません。',
  'date' => ':attributeには有効な日付を指定してください。',
  'date_equals' => ':attributeには、:dateと同じ日付けを指定してください。',
  'date_format' => ':attributeは:format形式で指定してください。',
  'different' => ':attributeと:otherには、異なった内容を指定してください。',
  'digits' => ':attributeは:digits桁で指定してください。',
  'digits_between' => ':attributeは:min桁から:max桁の間で指定してください。',
  'dimensions' => ':attributeの図形サイズが正しくありません。',
  'distinct' => ':attributeには異なった値を指定してください。',
  'email' => ':attributeには、有効なメールアドレスを指定してください。',
  'ends_with' =>
  ':attributeには、:valuesのどれかで終わる値を指定してください。',
  'exists' => '選択された:attributeは正しくありません。',
  'file' => ':attributeにはファイルを指定してください。',
  'filled' => ':attributeに値を指定してください。',
  'gt' => [
    'numeric' => ':attributeには、:valueより大きな値を指定してください。',
    'file' => ':attributeには、:value kBより大きなファイルを指定してください。',
    'string' => ':attributeは、:value文字より長く指定してください。',
    'array' => ':attributeには、:value個より多くのアイテムを指定してください。',
  ],
  'gte' => [
    'numeric' => ':attributeには、:value以上の値を指定してください。',
    'file' => ':attributeには、:value kB以上のファイルを指定してください。',
    'string' => ':attributeは、:value文字以上で指定してください。',
    'array' => ':attributeには、:value個以上のアイテムを指定してください。',
  ],
  'image' => ':attributeには画像ファイルを指定してください。',
  'in' => '選択された:attributeは正しくありません。',
  'in_array' => ':attributeには:otherの値を指定してください。',
  'integer' => ':attributeは整数で指定してください。',
  'ip' => ':attributeには、有効なIPアドレスを指定してください。',
  'ipv4' => ':attributeには、有効なIPv4アドレスを指定してください。',
  'ipv6' => ':attributeには、有効なIPv6アドレスを指定してください。',
  'json' => ':attributeには、有効なJSON文字列を指定してください。',
  'lt' => [
    'numeric' => ':attributeには、:valueより小さな値を指定してください。',
    'file' => ':attributeには、:value kBより小さなファイルを指定してください。',
    'string' => ':attributeは、:value文字より短く指定してください。',
    'array' => ':attributeには、:value個より少ないアイテムを指定してください。',
  ],
  'lte' => [
    'numeric' => ':attributeには、:value以下の値を指定してください。',
    'file' => ':attributeには、:value kB以下のファイルを指定してください。',
    'string' => ':attributeは、:value文字以下で指定してください。',
    'array' => ':attributeには、:value個以下のアイテムを指定してください。',
  ],
  'max' => [
    'numeric' => ':attributeには、:max以下の数字を指定してください。',
    'file' => ':attributeには、:max kB以下のファイルを指定してください。',
    'string' => ':attributeは、:max文字以下で指定してください。',
    'array' => ':attributeは:max個以下指定してください。',
  ],
  'mimes' => ':attributeには:valuesタイプのファイルを指定してください。',
  'mimetypes' => ':attributeには:valuesタイプのファイルを指定してください。',
  'min' => [
    'numeric' => ':attributeには、:min以上の数字を指定してください。',
    'file' => ':attributeには、:min kB以上のファイルを指定してください。',
    'string' => ':attributeは、:min文字以上で指定してください。',
    'array' => ':attributeは:min個以上指定してください。',
  ],
  'multiple_of' => ':attributeには、:valueの倍数を指定してください。',
  'not_in' => '選択された:attributeは正しくありません。',
  'not_regex' => ':attributeの形式が正しくありません。',
  'numeric' => ':attributeには、数字を指定してください。',
  'password' => '正しいパスワードを指定してください。',
  'present' => ':attributeが存在していません。',
  'regex' => ':attributeに正しい形式を指定してください。',
  'required' => ':attributeは必ず指定してください。',
  'required_if' => ':otherが:valueの場合、:attributeも指定してください。',
  'required_unless' =>
  ':otherが:valuesでない場合、:attributeを指定してください。',
  'required_with' => ':valuesを指定する場合は、:attributeも指定してください。',
  'required_with_all' =>
  ':valuesを指定する場合は、:attributeも指定してください。',
  'required_without' =>
  ':valuesを指定しない場合は、:attributeを指定してください。',
  'required_without_all' =>
  ':valuesのどれも指定しない場合は、:attributeを指定してください。',
  'prohibited' => ':attributeは入力禁止です。',
  'prohibited_if' => ':otherが:valueの場合、:attributeは入力禁止です。',
  'prohibited_unless' => ':otherが:valueでない場合、:attributeは入力禁止です。',
  'prohibits' => 'attributeは:otherの入力を禁じています。',
  'same' => ':attributeと:otherには同じ値を指定してください。',
  'size' => [
    'numeric' => ':attributeは:sizeを指定してください。',
    'file' => ':attributeのファイルは、:sizeキロバイトでなくてはなりません。',
    'string' => ':attributeは:size文字で指定してください。',
    'array' => ':attributeは:size個指定してください。',
  ],
  'starts_with' =>
  ':attributeには、:valuesのどれかで始まる値を指定してください。',
  'string' => ':attributeは文字列を指定してください。',
  'timezone' => ':attributeには、有効なゾーンを指定してください。',
  'unique' => ':attributeの値は既に存在しています。',
  'uploaded' => ':attributeのアップロードに失敗しました。',
  'url' => ':attributeに正しい形式を指定してください。',
  'uuid' => ':attributeに有効なUUIDを指定してください。',

  /*
    |--------------------------------------------------------------------------
    | Custom バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | "属性.ルール"の規約でキーを指定することでカスタムバリデーション
    | メッセージを定義できます。指定した属性ルールに対する特定の
    | カスタム言語行を手早く指定できます。
    |
    */

  'custom' => [
    '属性名' => [
      'ルール名' => 'カスタムメッセージ',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性名
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は、例えば"email"の代わりに「メールアドレス」のように、
    | 読み手にフレンドリーな表現でプレースホルダーを置き換えるために指定する
    | 言語行です。これはメッセージをよりきれいに表示するために役に立ちます。
    |
    */

  'attributes' => [
    'title' => '件名',
    'content' => '本文',
    'name' => '名',
    'memo' => 'メモ',
    'price' => '料金',
    // 追加
    'kana' => 'カナ',
    'tel' => '電話番号',
    'email' => 'メールアドレス',
    'postcode' => '郵便番号',
    'address' => '住所',
    'birthday' => '誕生日',
    'gender' => '性別',
    // ここまで
  ],
];
```

+ `app/Http/Controllers/CustomerController.php`を編集<br>

```php:CustomerController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
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
    public function index(Request $request)
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

        $customers = Customer::searchCustomers($request->search)
            ->select(
                'id',
                'name',
                'kana',
                'tel'
            )->paginate(50);

        // dd($customers);

        return Inertia::render('Customers/Index', [
            'customers' => $customers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create([
            'name' => $request->name,
            'kana' => $request->kana,
            'tel' => $request->tel,
            'email' => $request->email,
            'postcode' => $request->postcode,
            'address' => $request->address,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'memo' => $request->memo,
        ]);

        return to_route('customers.index')
            ->with([
                'message' => '登録しました。',
                'satatus' => 'success',
            ]);
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

+ `resources/js/Pages/Customers/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { reactive } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'


const form = reactive({
  name: null,
  kana: null,
  tel: null,
  email: null,
  postcode: null,
  address: null,
  birthday: null,
  gender: null,
  memo: null,
})

// 編集
const storeCustomer = () => {
  Inertia.post("/customers", form)
}
</script>

<template>

  <Head title="顧客登録" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        顧客登録
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <BreezeValidationErrors class="mb-4" />
            <section class="text-gray-600 body-font relative">
              <!-- 編集 -->
              <form @submit.prevent="storeCustomer">
                <div class="container px-5 py-8 mx-auto">
                  <div class="lg:w-1/2 md:w-2/3 mx-auto">
                    <div class="flex flex-wrap -m-2">
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="name" class="leading-7 text-sm text-gray-600">顧客名</label>
                          <input type="text" id="name" name="name" v-model="form.name"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="kana" class="leading-7 text-sm text-gray-600">顧客名カナ</label>
                          <input type="text" id="kana" name="kana" v-model="form.kana"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="tel" class="leading-7 text-sm text-gray-600">電話番号</label>
                          <input type="tel" id="tel" name="tel" v-model="form.tel"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="email" class="leading-7 text-sm text-gray-600">メールアドレス</label>
                          <input type="email" id="email" name="email" v-model="form.email"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="postcode" class="leading-7 text-sm text-gray-600">郵便番号</label>
                          <input type="number" id="postcode" name="postcode" v-model="form.postcode"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="address" class="leading-7 text-sm text-gray-600">住所</label>
                          <input type="text" id="address" name="address" v-model="form.address"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="birthday" class="leading-7 text-sm text-gray-600">誕生日</label>
                          <input type="date" id="birthday" name="birthday" v-model="form.birthday"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label class="leading-7 text-sm text-gray-600">性別</label>
                          <input type="radio" id="gender0" name="gender" v-model="form.gender" value="0">
                          <label for="gender0" class="ml-2 mr-4">男性</label>
                          <input type="radio" id="gender1" name="gender" v-model="form.gender" value="1">
                          <label for="gender1" class="ml-2 mr-4">女性</label>
                          <input type="radio" id="gender2" name="gender" v-model="form.gender" value="2">
                          <label for="gender2" class="ml-2 mr-4">その他</label>
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="memo" class="leading-7 text-sm text-gray-600">メモ</label>
                          <textarea id="memo" name="memo" v-model="form.memo"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <button
                          class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">顧客登録</button>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </section>
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>
```
