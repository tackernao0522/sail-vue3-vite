## 78. defineEmitで 子 -> 親に情報をアップロード

+ `resources/js/Components/MicroModal.vue`を編集<br>

```vue:MicroModal.vue
<script setup>
import axios from 'axios';
import { reactive, ref } from 'vue';

const search = ref('')
const customers = reactive({})

const isShow = ref(false)
const toggleStatus = () => {
  isShow.value = !isShow.value
}

const searchCustomers = async () => {
  try {
    await axios.get(`/api/searchCustomers/?search=${search.value}`)
      .then((res) => {
        console.log(res.data)
        customers.value = res.data
      })
    toggleStatus()
  } catch (e) {
    console.log(e)
  }
}

// 追加
const emit = defineEmits(['update:customerId'])

const setCustomer = (e) => {
  search.value = e.kana
  emit('update:customerId', e.id)
  toggleStatus()
}
// ここまで
</script>

<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container w-2/3" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            顧客検索
          </h2>
          <button type="button" @click="toggleStatus" class="modal__close" aria-label="Close modal"
            data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div v-if="customers.value" class="lg:w-2/3 w-full mx-auto overflow-auto">
            <table class="table-auto w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th
                    class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                    ID</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                    氏名</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                    カナ</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                    電話番号</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in customers.value.data" :key="customer.id">
                  <td class="px-4 py-3">
                    <!-- 編集 -->
                    <button type="button" class="text-blue-400" @click="setCustomer({
                      id: customer.id,
                      kana: customer.kana
                    })">
                      {{ customer.id }}
                    </button>
                    <!-- ここまで -->
                  </td>
                  <td class="px-4 py-3">{{ customer.name }}</td>
                  <td class="px-4 py-3">{{ customer.kana }}</td>
                  <td class="px-4 py-3">{{ customer.tel }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
        <footer class="modal__footer">
          <button type="button" @click="toggleStatus" class="modal__btn" data-micromodal-close
            aria-label="Close this dialog window">閉じる</button>
        </footer>
      </div>
    </div>
  </div>
  <input name="customer" v-model="search"
    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
  <button type="button" @click="searchCustomers"
    class="flex mx-auto text-white bg-teal-500 border-0 py-2 px-8 focus:outline-none hover:bg-teal-600 rounded text-lg"
    data-micromodal-trigger="modal-1">検索する</button>
</template>
```

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Inertia } from '@inertiajs/inertia';
import { Head } from '@inertiajs/inertia-vue3';
import { computed, onMounted, reactive, ref } from 'vue';
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { getToday } from '@/common';
import MicroModal from '../../Components/MicroModal.vue'

const props = defineProps({
  'items': Array,
})

const itemList = ref([])

onMounted(() => {
  form.date = getToday()
  props.items.forEach((item) => {
    itemList.value.push({
      id: item.id,
      name: item.name,
      price: item.price,
      quantity: 0
    })
  })
})

const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9",]

// コントローラに渡す
const form = reactive({
  date: null,
  customer_id: null,
  status: true,
  items: []
})

const totalPrice = computed(() => {
  let total = 0
  itemList.value.forEach((item) => {
    total += item.price * item.quantity
  })
  return total
})

const storePurchase = () => {
  itemList.value.forEach((item) => {
    if (item.quantity > 0) { // 0より大きいものだけ追加
      form.items.push({
        id: item.id,
        quantity: item.quantity
      })
    }
  })
  Inertia.post(route('purchases.store'), form)
}

// 追加
const setCustomerId = (id) => {
  form.customer_id = id
}
</script>

<template>

  <Head title="購入画面" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        購入画面
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <BreezeValidationErrors class="mb-4" />
            <section class="text-gray-600 body-font relative">
              <form @submit.prevent="storePurchase">
                <div class="container px-5 py-8 mx-auto">
                  <div class="lg:w-1/2 md:w-2/3 mx-auto">
                    <div class="flex flex-wrap -m-2">
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="date" class="leading-7 text-sm text-gray-600">日付</label>
                          <input type="date" id="date" name="date" v-model="form.date"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="customer" class="leading-7 text-sm text-gray-600">会員名</label>
                          <!-- 編集 -->
                          <MicroModal @update:customerId="setCustomerId" />
                        </div>
                      </div>

                      <div class="w-full mt-8 mx-auto overflow-auto">
                        <table class="table-auto w-full text-left whitespace-no-wrap">
                          <thead>
                            <tr>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">
                                ID</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                商品名</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                金額</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                数量</th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                                小計</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="item in itemList" :key="item.id">
                              <td class="px-4 py-3">{{ item.id }}</td>
                              <td class="px-4 py-3">{{ item.name }}</td>
                              <td class="px-4 py-3">{{ item.price }}</td>
                              <td class="px-4 py-3 text-lg">
                                <select name="quantity" v-model="item.quantity">
                                  <option v-for="q in quantity" :value="q">{{ q }}</option>
                                </select>
                              </td>
                              <td class="px-4 py-3">{{ item.price * item.quantity }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div class="p-2 w-full">
                        <div class="">
                          <label for="price" class="leading-7 text-sm text-gray-600">合計金額</label><br>
                          <div
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            {{ totalPrice }} 円
                          </div>
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <button
                          class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
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
