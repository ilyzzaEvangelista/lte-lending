<template>
  <div>
    <div class="d-flex flex-column flex-sm-row flex-wrap align-start justify-space-between gap-4 mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold mb-2">Client dashboard</h1>
        <p v-if="auth.user" class="text-body-1 text-medium-emphasis mb-0">
          Signed in as {{ auth.user.name }} ({{ auth.user.email }})
        </p>
      </div>
      <v-btn to="/client/loans/apply" color="primary" variant="flat" prepend-icon="mdi-plus-circle-outline" class="text-none">
        New application
      </v-btn>
    </div>

    <h2 class="text-h6 font-weight-bold mb-3">Your applications</h2>
    <ClientLoanApplicationsTable />
  </div>
</template>

<script setup>
import { useAuthStore } from '~~/stores/auth'

definePageMeta({
  layout: 'client',
  middleware: 'auth',
})

const auth = useAuthStore()
</script>
