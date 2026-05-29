<template>
  <div class="ma-0 pa-0">
    <div class="d-flex flex-wrap align-center justify-space-between mb-6 ga-3">
      <h1 class="text-h4 font-weight-bold">My loan applications</h1>
      <v-tooltip v-if="!canSubmitLoanApplication" location="bottom" text="You already have an active loan. Close it before starting a new application.">
        <template #activator="{ props: tip }">
          <span v-bind="tip">
            <v-btn size="small" color="primary" variant="outlined" prepend-icon="mdi-plus" class="text-none rounded-lg" disabled>
              Apply for a loan
            </v-btn>
          </span>
        </template>
      </v-tooltip>
      <v-btn
        v-else
        to="/client/loans/apply"
        size="small"
        color="primary"
        variant="outlined"
        prepend-icon="mdi-plus"
        class="text-none rounded-lg"
      >
        Apply for a loan
      </v-btn>
    </div>
    <ClientLoanApplicationsTable />
  </div>
</template>

<script setup>
  import { useApi } from "~~/composables/useApi";
  import { useAuthStore } from "~~/stores/auth";

  definePageMeta({
      layout: "client",
      middleware: "auth",
  });

  const auth = useAuthStore();
  const { api } = useApi();
  const canSubmitLoanApplication = computed(() => auth.user?.can_submit_loan_application !== false);

  onMounted(async () => {
      if (auth.token) {
          try {
              await auth.fetchMe(api);
          } catch {
              /* ignore */
          }
      }
  });
</script>
