<template>
  <div class="default-layout">
    <v-app-bar flat density="comfortable" color="primary" class="default-app-bar" elevation="1">
      <v-toolbar-title class="text-h6 font-weight-bold ps-2">
        <NuxtLink to="/" class="brand-link d-flex align-center ga-2" aria-label="LoanHub home">
          <v-icon icon="mdi-finance" color="on-primary" size="26" aria-hidden="true" />
          LoanHub
        </NuxtLink>
      </v-toolbar-title>
      <v-spacer />
      <template v-if="auth.isAuthenticated">
        <v-btn v-if="auth.isAdmin" to="/admin" variant="text" color="on-primary" prepend-icon="mdi-shield-account">
          Admin
        </v-btn>
        <v-btn v-else to="/client/dashboard" variant="text" color="on-primary" prepend-icon="mdi-bank-outline">
          My loans
        </v-btn>
        <v-btn
          variant="flat"
          color="surface"
          class="ml-2 text-primary register-pill"
          prepend-icon="mdi-logout"
          @click="onLogout"
        >
          Sign out
        </v-btn>
      </template>
      <template v-else>
        <v-btn to="/login" variant="text" color="on-primary"> Sign in </v-btn>
        <v-btn
          to="/register"
          variant="flat"
          color="surface"
          class="ml-2 text-primary register-pill"
          prepend-icon="mdi-account-plus"
        >
          Register
        </v-btn>
      </template>
    </v-app-bar>
    <v-main class="default-main bg-background">
      <v-container class="py-8 py-md-10 px-4" style="max-width: 1100px">
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
.brand-link {
  color: rgb(var(--v-theme-on-primary));
  text-decoration: none;
  transition: opacity 0.15s ease;
}

.brand-link:hover {
  opacity: 0.92;
}

/* Light CTA on primary bar — uses theme surface + primary text */
.register-pill {
  font-weight: 600;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
}

.default-main {
  min-height: calc(100vh - 64px);
}
</style>
