<template>
  <div>
    <p class="text-body-2 text-medium-emphasis text-center mb-6">
      <NuxtLink to="/">← Back to home</NuxtLink>
    </p>
    <v-row justify="center">
      <v-col cols="12" sm="11" md="8" lg="6">
        <v-card class="auth-card rounded-xl" border elevation="0">
          <v-card-text class="pa-6 pa-sm-8">
            <h1 class="text-h5 font-weight-bold mb-1">Create your account</h1>
            <p class="text-body-2 text-medium-emphasis mb-6">
              For <strong>borrowers</strong> only. If you work for the lender, your manager creates staff logins separately.
            </p>

            <v-form @submit.prevent="submit">
              <p class="text-subtitle-2 font-weight-bold mb-3">About you</p>
              <v-row dense>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="firstName"
                    label="First name"
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    autocomplete="given-name"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="lastName"
                    label="Last name"
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    autocomplete="family-name"
                  />
                </v-col>
              </v-row>

              <p class="text-subtitle-2 font-weight-bold mb-3 mt-4">How you'll sign in</p>
              <v-text-field
                v-model="username"
                label="Username"
                hint="Pick a name you'll remember (letters and numbers are fine)."
                variant="outlined"
                density="comfortable"
                class="mb-1"
                hide-details="auto"
                persistent-hint
                autocomplete="username"
              />
              <v-text-field
                v-model="email"
                label="Email address"
                type="email"
                variant="outlined"
                density="comfortable"
                class="mb-1"
                hide-details="auto"
                autocomplete="email"
              />

              <p class="text-subtitle-2 font-weight-bold mb-3 mt-4">Contact (optional)</p>
              <v-text-field
                v-model="contact"
                label="Phone or other contact"
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                autocomplete="tel"
              />
              <v-text-field
                v-model="address"
                label="Address"
                variant="outlined"
                density="comfortable"
                class="mb-1"
                hide-details="auto"
                autocomplete="street-address"
              />

              <p class="text-subtitle-2 font-weight-bold mb-3 mt-4">Password</p>
              <v-text-field
                v-model="password"
                label="Password"
                type="password"
                hint="Use at least 8 characters."
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                persistent-hint
                autocomplete="new-password"
              />
              <v-text-field
                v-model="passwordConfirmation"
                label="Confirm password"
                type="password"
                variant="outlined"
                density="comfortable"
                class="mb-2"
                hide-details="auto"
                autocomplete="new-password"
              />

              <v-alert v-if="fieldErrors" type="error" variant="tonal" density="comfortable" class="mt-4 rounded-lg">
                <div class="text-body-2 font-weight-medium mb-2">Please fix the following:</div>
                <ul class="pl-4 mb-0 text-body-2">
                  <li v-for="line in fieldErrorLines" :key="line">{{ line }}</li>
                </ul>
              </v-alert>
              <v-alert v-else-if="error" type="error" variant="tonal" density="comfortable" class="mt-4 text-body-2 rounded-lg">
                {{ error }}
              </v-alert>

              <v-btn type="submit" color="primary" block size="large" min-height="48" rounded="lg" class="mt-6" :loading="loading">
                Create my account
              </v-btn>
            </v-form>
          </v-card-text>
          <v-divider />
          <v-card-actions class="px-6 py-4 flex-wrap justify-center">
            <span class="text-body-2 text-medium-emphasis me-1">Already registered?</span>
            <v-btn to="/login" variant="text" color="primary" class="text-none px-1"> Sign in </v-btn>
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

const FIELD_LABELS = {
  first_name: 'First name',
  last_name: 'Last name',
  username: 'Username',
  email: 'Email',
  contact: 'Contact',
  address: 'Address',
  password: 'Password',
  password_confirmation: 'Confirm password',
}

const router = useRouter()
const auth = useAuthStore()
const { api } = useApi()

const firstName = ref('')
const lastName = ref('')
const username = ref('')
const email = ref('')
const contact = ref('')
const address = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const error = ref('')
const fieldErrors = ref(null)

const fieldErrorLines = computed(() => {
  const err = fieldErrors.value
  if (!err || typeof err !== 'object') return []
  const lines = []
  for (const [key, msgs] of Object.entries(err)) {
    const label = FIELD_LABELS[key] || key.replace(/_/g, ' ')
    const list = Array.isArray(msgs) ? msgs : [msgs]
    for (const m of list) {
      lines.push(`${label}: ${m}`)
    }
  }
  return lines
})

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
  fieldErrors.value = null
  loading.value = true
  try {
    const res = await api('/register', {
      method: 'POST',
      body: {
        first_name: firstName.value,
        last_name: lastName.value,
        username: username.value,
        email: email.value,
        contact: contact.value || null,
        address: address.value || null,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      },
    })
    auth.setSession({ token: res.token, user: res.user })
    await router.push('/client/dashboard')
  } catch (e) {
    if (e?.status === 422 && e?.data?.errors) {
      fieldErrors.value = e.data.errors
    } else {
      error.value = e?.data?.message || 'Something went wrong. Please try again in a moment.'
    }
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
