<script setup>
import { getToday } from '@/common';
import { onMounted, reactive } from 'vue';

const props = defineProps({
  'customers': Array
})

onMounted(() => {
  form.date = getToday()
})

const form = reactive({
  date: null,
  customer_id: null
})
</script>

<template>
  日付<br>
  <input type="date" name="date" v-model="form.date"><br>
  会員名<br>
  <select name="customer" v-model="form.customer_id">
    <option v-for="customer in customers" :value="customer.id" :key="customer.id">
      {{ customer.id }} : {{ customer.name }}
    </option>
  </select>
</template>
