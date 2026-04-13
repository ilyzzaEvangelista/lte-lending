<template>
  <div class="d-flex admin-shell">
    <v-navigation-drawer permanent width="260" color="surface" class="border-e">
      <div class="d-flex flex-column fill-height">
        <div class="pa-4 text-h6 font-weight-bold">LoanHub Admin</div>
        <v-list nav density="comfortable" class="flex-grow-1">
          <v-list-item to="/admin" title="Overview" prepend-icon="mdi-view-dashboard-outline" rounded="lg" />
          <v-list-item to="/admin/loans" title="Loan applications" prepend-icon="mdi-file-document-outline" rounded="lg" />
          <v-list-item to="/admin/payments" title="Payments" prepend-icon="mdi-cash-multiple" rounded="lg" />
          <v-list-item to="/admin/logs" title="Activity logs" prepend-icon="mdi-history" rounded="lg" />
        </v-list>
        <div class="pa-3 border-t">
          <v-btn to="/" variant="text" prepend-icon="mdi-web" block class="mb-2"> Public site </v-btn>
          <v-btn variant="outlined" prepend-icon="mdi-logout" block @click="onLogout"> Sign out </v-btn>
        </div>
      </div>
    </v-navigation-drawer>
    <v-main class="flex-grow-1 bg-background">
      <v-container fluid class="pa-6">
        <slot />
      </v-container>
    </v-main>
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'
import { useAuthStore } from '~~/stores/auth'

const auth = useAuthStore()
const { api } = useApi()
const router = useRouter()

async function onLogout() {
  await auth.logout(api)
  await router.push('/')
}
</script>

<style scoped>
.admin-shell {
  min-height: 100vh;
}
.border-t {
  border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
}
</style>
