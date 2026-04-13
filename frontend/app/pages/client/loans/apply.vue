<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-6">New loan application</h1>
    <v-row>
      <v-col cols="12" md="7">
        <v-card border>
          <v-card-text>
            <v-form @submit.prevent="submit">
              <v-text-field
                v-model.number="amount"
                label="Amount (PHP)"
                type="number"
                min="100"
                step="0.01"
                variant="outlined"
                density="comfortable"
                prepend-inner-icon="mdi-currency-php"
              />
              <v-text-field
                v-model.number="tenure"
                label="Tenure (months)"
                type="number"
                min="1"
                max="360"
                variant="outlined"
                density="comfortable"
                prepend-inner-icon="mdi-calendar-range"
              />
              <v-textarea v-model="purpose" label="Purpose (optional)" variant="outlined" rows="3" maxlength="255" />
              <v-file-input
                v-model="payslipFiles"
                label="Payslip image (optional)"
                accept="image/*"
                variant="outlined"
                density="comfortable"
                prepend-icon="mdi-paperclip"
                show-size
                clearable
              />
              <v-alert v-if="fieldErrors" type="error" variant="tonal" density="compact" class="mb-2">
                <div v-for="(msgs, key) in fieldErrors" :key="key">
                  <div v-for="m in msgs" :key="m">{{ key }}: {{ m }}</div>
                </div>
              </v-alert>
              <v-alert v-else-if="error" type="error" variant="tonal" density="compact" class="mb-2">
                {{ error }}
              </v-alert>
              <div class="d-flex flex-wrap ga-3 mt-4">
                <v-btn to="/client/loans" variant="text">Cancel</v-btn>
                <v-btn type="submit" color="primary" :loading="loading">Submit application</v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'

definePageMeta({
  layout: 'default',
  middleware: 'auth',
})

const router = useRouter()
const { api } = useApi()

const amount = ref(5000)
const tenure = ref(24)
const purpose = ref('')
const payslipFiles = ref(null)
const loading = ref(false)
const error = ref('')
const fieldErrors = ref(null)

function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

async function submit() {
  error.value = ''
  fieldErrors.value = null
  loading.value = true
  try {
    const body = {
      amount: amount.value,
      tenure: tenure.value,
      purpose: purpose.value || null,
    }
    const f = payslipFiles.value
    const file = Array.isArray(f) ? f[0] : f
    if (file) {
      body.payslip_base64 = await fileToBase64(file)
    }
    await api('/client/loans', { method: 'POST', body })
    await router.push('/client/loans')
  } catch (e) {
    if (e?.status === 422 && e?.data?.errors) {
      fieldErrors.value = e.data.errors
    } else {
      error.value = e?.data?.message || 'Submission failed.'
    }
  } finally {
    loading.value = false
  }
}
</script>
