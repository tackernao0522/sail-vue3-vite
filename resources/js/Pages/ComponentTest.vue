<script setup>
import GuestLayout from '@/Layouts/Guest.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'
</script>

<template>
  <GuestLayout>
    <Label value="件名">タイトル</Label>
    <Input modelValue="初期値が入ります"></Input>
  </GuestLayout>
</template>
