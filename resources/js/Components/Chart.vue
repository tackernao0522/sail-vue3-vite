<script setup>
import { Chart, registerables } from "chart.js";
import { BarChart } from "vue-chart-3";
import { reactive } from "vue"

Chart.register(...registerables);

const barData = reactive({
  labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"], datasets: [
    {
      label: '売上',
      data: [65, 59, 80, 81, 56, 55, 40], backgroundColor: "rgb(75, 192, 192)",
      tension: 0.1,
    }]
})
</script>

<template>
  <div>
    <BarChart :chartData="barData" />
  </div>
</template>
