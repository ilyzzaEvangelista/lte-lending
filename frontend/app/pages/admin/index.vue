<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-2">Admin Dashboard</h1>
    <v-row>
    </v-row>
  </div>
</template>

<script setup>
  definePageMeta({
    layout: 'admin',
    middleware: 'admin',
  })
</script>