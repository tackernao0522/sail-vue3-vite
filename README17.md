## 66. 購入日の日付を取得(JS Dateオブジェクト)

+ `resources/js/common.js`を編集<br>

```js:common.js
const nl2br = (str) => {
  var res = str.replace(/\r\n/g, "<br>");
  res = res.replace(/(\n|\r)/g, "<br>");
  return res;
}

// 追加
export const getToday = () => {
  const today = new Date()
  const yyyy = today.getFullYear()
  const mm = ("0" + (today.getMonth() + 1)).slice(-2)
  const dd = ("0" + today.getDate()).slice(-2)
  return yyyy + '-' + mm + '-' + dd
}
// ここまで

export default nl2br;
```

+ `resources/js/Pages/Purchases/Create.vue`を編集<br>

```vue:Create.vue
<script setup>
import { getToday } from '@/common';
import { onMounted, reactive } from 'vue';

onMounted(() => { // ページ読み込み後 即座に実行
  form.date = getToday()
})

const form = reactive({
  date: null
})
</script>

<template>
  日付<br>
  <input type="date" name="date" v-model="form.date">
</template>
```
