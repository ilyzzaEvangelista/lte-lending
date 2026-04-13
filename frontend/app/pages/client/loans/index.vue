<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between mb-6 ga-3">
      <h1 class="text-h4 font-weight-bold">My loan applications</h1>
      <v-btn to="/client/loans/apply" color="primary" variant="flat" prepend-icon="mdi-plus" class="text-none"> Apply </v-btn>
    </div>
    <ClientLoanApplicationsTable />
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default',
  middleware: 'auth',
})
</script>
