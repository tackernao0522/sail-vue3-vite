<script setup>
import { getToday } from '@/common';
import { onMounted, reactive } from 'vue';

onMounted(() => {
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
