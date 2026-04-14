<template>
  <div class="ma-0 pa-0">
    <div class="d-flex flex-wrap align-center justify-space-between mb-6 ga-3">
      <h1 class="text-h4 font-weight-bold">My loan applications</h1>
      <v-btn to="/client/loans/apply" size="small" color="primary" variant="outlined" prepend-icon="mdi-plus" class="text-none text-capitalize text-white rounded-lg"> Apply for a loan </v-btn>
    </div>
    <ClientLoanApplicationsTable />
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'client',
  middleware: 'auth',
})
</script>
