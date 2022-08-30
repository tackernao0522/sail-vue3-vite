<script setup>
import { Inertia } from "@inertiajs/inertia";
import { reactive } from "vue";

defineProps({
  errors: Object,
});

const form = reactive({
  title: null,
  content: null,
});

const submitFunction = () => {
  Inertia.post("/inertia", form);
};
</script>

<template>
  <form @submit.prevent="submitFunction">
    <input type="text" name="title" v-model="form.title" /><br />
    <div v-if="errors.title">{{ errors.title }}</div>
    <input type="text" name="content" v-model="form.content" /><br />
    <div v-if="errors.content">{{ errors.content }}</div>
    <button>送信</button>
  </form>
</template>
