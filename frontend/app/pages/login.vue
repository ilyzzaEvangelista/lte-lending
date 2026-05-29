<template>
  <div>
    <p class="text-body-2 text-medium-emphasis text-center mb-6">
      <NuxtLink to="/">← Back to home</NuxtLink>
    </p>
    <v-row justify="center">
      <v-col cols="12" sm="11" md="7" lg="5">
        <v-card class="auth-card rounded-xl" border elevation="0">
          <v-card-text class="pa-6 pa-sm-8">
            <h1 class="text-h5 font-weight-bold mb-1">Sign in</h1>
            <p class="text-body-2 text-medium-emphasis mb-6">Use the option that matches your account type.</p>

            <v-btn-toggle
              v-model="mode"
              mandatory
              divided
              "
              class="mb-2 w-100"
              color="primary"
              aria-label="Account type"
            >
              <v-btn value="customer" class="flex-grow-1 text-none"> I'm a customer </v-btn>
              <v-btn value="staff" class="flex-grow-1 text-none"> I'm staff </v-btn>
            </v-btn-toggle>
            <p class="text-caption text-medium-emphasis mb-5">
              <template v-if="mode === 'customer'">Customers sign in with <strong>email</strong> and password.</template>
              <template v-else>Staff sign in with your <strong>username</strong> (not email) and password.</template>
            </p>

            <v-form @submit.prevent="submit">
              <v-text-field
                v-if="mode === 'staff'"
                v-model="username"
                label="Username"
                prepend-inner-icon="mdi-account-outline"
                variant="outlined"
                "
                class="mb-3"
                autocomplete="username"
                hide-details="auto"
              />
              <v-text-field
                v-else
                v-model="email"
                label="Email address"
                type="email"
                prepend-inner-icon="mdi-email-outline"
                variant="outlined"
                "
                class="mb-3"
                autocomplete="email"
                hide-details="auto"
              />
              <v-text-field
                v-model="password"
                label="Password"
                type="password"
                prepend-inner-icon="mdi-lock-outline"
                variant="outlined"
                "
                class="mb-4"
                autocomplete="current-password"
                hide-details="auto"
              />
              <v-alert v-if="error" type="error" variant="tonal" " class="mb-4 text-body-2" rounded="lg">
                {{ error }}
              </v-alert>
              <v-btn type="submit" color="primary" block size="large" min-height="48" rounded="lg" :loading="loading">
                Sign in
              </v-btn>
            </v-form>
          </v-card-text>
          <v-divider v-if="mode === 'customer'" />
          <v-card-actions v-if="mode === 'customer'" class="px-6 py-4 flex-wrap justify-center">
            <span class="text-body-2 text-medium-emphasis me-1">New to LoanHub?</span>
            <v-btn to="/register" variant="text" color="primary" class="text-none px-1"> Create an account </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'
import { useAuthStore } from '~~/stores/auth'

definePageMeta({
  layout: 'default',
})

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { api } = useApi()

const mode = ref('customer')
const email = ref('')
const username = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

onMounted(async () => {
  auth.initFromStorage()
  if (auth.token && !auth.user) {
    try {
      await auth.fetchMe(api)
    } catch {
      auth.clearSession()
    }
  }
  if (auth.user) {
    await router.replace(auth.isAdmin ? '/admin' : '/client/dashboard')
  }
})

async function submit() {
  error.value = ''
  loading.value = true
  try {
    let res
    if (mode.value === 'staff') {
      res = await api('/admin/login', {
        method: 'POST',
        body: { username: username.value, password: password.value },
      })
    } else {
      res = await api('/login', {
        method: 'POST',
        body: { email: email.value, password: password.value },
      })
    }
    auth.setSession({ token: res.token, user: res.user })
    const redirect = route.query.redirect
    if (typeof redirect === 'string' && redirect.startsWith('/')) {
      await router.push(redirect)
    } else {
      await router.push(auth.isAdmin ? '/admin' : '/client/dashboard')
    }
  } catch (e) {
    error.value = e?.data?.message || "That email or password doesn't match our records. Try again."
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-card {
  box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06) !important;
}
</style>
