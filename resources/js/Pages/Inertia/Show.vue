<script setup>
defineProps({
  id: String,
  blog: Object
});
</script>

<template>
  {{ id }}<br>
  {{ blog.title }}
</template>
