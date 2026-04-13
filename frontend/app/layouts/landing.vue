<template>
  <div class="landing-layout">
    <header class="landing-curved-nav" aria-label="Site header">
      <v-container fluid class="nav-container px-4 px-sm-6" style="max-width: 1200px">
        <div class="nav-row d-flex flex-column flex-md-row align-stretch align-md-center ga-4 py-3 py-md-4">
          <div class="d-flex align-center justify-space-between justify-md-start ga-6 flex-wrap">
            <NuxtLink to="/" class="nav-brand text-h6 font-weight-bold d-flex align-center ga-2" aria-label="LoanHub home">
              <v-icon icon="mdi-finance" color="on-primary" size="26" aria-hidden="true" />
              LoanHub
            </NuxtLink>
          </div>
          <v-spacer class="d-none d-md-block" />
          <div class="d-flex align-center justify-space-between justify-md-end ga-3 flex-wrap">
            <nav class="nav-links d-flex d-md-none align-center flex-wrap ga-4 text-caption" aria-label="Main navigation mobile">
              <NuxtLink to="/">Home</NuxtLink>
              <a href="#about">About</a>
              <a href="#services">Services</a>
              <a href="#contact">Contact</a>
            </nav>
            <template v-if="auth.isAuthenticated">
              <v-btn v-if="auth.isAdmin" to="/admin" variant="text" size="small" class="nav-auth-btn" prepend-icon="mdi-shield-account">
                Admin
              </v-btn>
              <v-btn v-else to="/client/dashboard" variant="text" size="small" class="nav-auth-btn" prepend-icon="mdi-bank-outline">
                My loans
              </v-btn>
              <v-btn variant="flat" size="small" rounded="lg" class="nav-pill-solid px-3" prepend-icon="mdi-logout" @click="onLogout">
                Sign out
              </v-btn>
            </template>
            <div v-else class="auth-pill d-inline-flex align-center rounded-xl flex-shrink-0">
              <v-btn to="/register" variant="text" size="small" class="auth-pill-btn text-capitalize" rounded="lg"> Sign up </v-btn>
              <span class="auth-pill-divider" aria-hidden="true">|</span>
              <v-btn to="/login" variant="text" size="small" class="auth-pill-btn text-capitalize" rounded="lg"> Log in </v-btn>
            </div>
          </div>
        </div>
      </v-container>
    </header>
    <v-main class="pa-0 landing-main">
      <slot />
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
.landing-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: rgb(var(--v-theme-background));
}

.landing-curved-nav {
  /* Vuetify exposes primary as comma-separated RGB: use rgb() wrapper */
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
  border-radius: 0 0 2.75rem 0;
  box-shadow: 0 8px 32px rgba(var(--v-theme-primary), 0.35);
  position: relative;
  z-index: 4;
}

.nav-container {
  max-width: 1200px;
}

.nav-brand {
  text-decoration: none;
  color: rgb(var(--v-theme-on-primary)) !important;
}

.nav-links :deep(a),
.nav-links a {
  color: rgb(var(--v-theme-on-primary)) !important;
  text-decoration: none;
  transition: opacity 0.15s ease;
}

.nav-links a:hover {
  opacity: 0.88;
}

.nav-auth-btn {
  color: rgb(var(--v-theme-on-primary)) !important;
}

.auth-pill {
  background: white;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
}

.auth-pill-btn {
  color: rgb(var(--v-theme-primary)) !important;
  font-weight: 600;
  letter-spacing: 0.02em;
  min-width: 0;
}

.auth-pill-divider {
  color: #94a3b8;
  font-size: 0.875rem;
  user-select: none;
}

.nav-pill-solid {
  background: rgb(var(--v-theme-on-primary)) !important;
  color: rgb(var(--v-theme-primary)) !important;
  font-weight: 600;
}

.landing-main {
  flex: 1;
}
</style>
