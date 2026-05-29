<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-2">New loan application</h1>

    <template v-if="applicationBlocked">
      <v-alert type="warning" variant="tonal" border="start" prominent class="mb-6 rounded-lg">
        <v-alert-title class="text-subtitle-1 font-weight-bold">New applications are unavailable</v-alert-title>
        <p class="text-body-2 mt-2 mb-0">
          You already have an <strong>active</strong> loan. Close or complete that loan before submitting another
          application.
        </p>
      </v-alert>
      <div class="d-flex flex-wrap ga-3">
        <v-btn to="/client/loans" variant="text" color="primary" class="text-none">Back to applications</v-btn>
        <v-btn v-if="activeLoan?.id" :to="`/client/loans/${activeLoan.id}`" color="primary" variant="flat" class="text-none">
          View active loan
        </v-btn>
      </div>
    </template>

    <template v-else>
      <p class="text-body-2 text-medium-emphasis mb-6">
        Only loan details are needed here. Your name, age, gender, email, phone, and address are taken from your
        LoanHub profile when you submit.
      </p>

      <v-card border rounded="lg">
        <v-card-text class="pa-6 pa-md-8">
          <v-form ref="formRef" @submit.prevent="submit">
            <p class="text-subtitle-2 font-weight-bold mb-3">Loan request</p>
            <v-row dense>
              <v-col cols="12" md="4">
                <v-select
                  v-model="loanType"
                  label="What type of loan do you need?"
                  :items="loanTypeItems"
                  variant="outlined"
                  hide-details="auto"
                  required
                  :rules="[rules.required]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="monthlyIncomeOther"
                  label="Monthly income from other sources"
                  :items="incomeSourceItems"
                  variant="outlined"
                  hide-details="auto"
                  required
                  :rules="[rules.required]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="amount"
                  label="Requested loan amount (PHP)"
                  type="number"
                  min="100"
                  step="0.01"
                  variant="outlined"
                  hide-details="auto"
                  prepend-inner-icon="mdi-currency-php"
                  required
                  :rules="[rules.required, rules.amountMin]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="tenure"
                  label="Tenure of your loan (months)"
                  type="number"
                  min="1"
                  max="360"
                  variant="outlined"
                  hide-details="auto"
                  prepend-inner-icon="mdi-calendar-range"
                  required
                  :rules="[rules.required, rules.tenureRange]"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="hasExistingLoan"
                  label="Do you have any existing loan?"
                  :items="yesNoItems"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  hide-details="auto"
                  required
                  :rules="[rules.yesNo]"
                />
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="purpose"
                  label="Purpose of loan"
                  variant="outlined"
                  rows="3"
                  maxlength="255"
                  counter="255"
                  hide-details="auto"
                  required
                  :rules="[rules.required]"
                />
              </v-col>
              <v-col cols="12">
                <v-file-input
                  v-model="payslipFiles"
                  label="Payslip image"
                  accept="image/*"
                  variant="outlined"
                  prepend-icon="mdi-paperclip"
                  show-size
                  hide-details="auto"
                  required
                  :rules="[rules.payslipRequired]"
                />
              </v-col>
            </v-row>

            <v-checkbox
              v-model="acceptTerms"
              color="primary"
              hide-details="auto"
              required
              :rules="[rules.terms]"
              class="mt-2"
            >
              <template #label>
                <span class="text-body-2">
                  I confirm that the loan information above is true and correct, and that my profile details on file are
                  accurate. I understand that false information may result in rejection of my application, and I agree to
                  share my information as required by company policy.
                </span>
              </template>
            </v-checkbox>

            <v-alert v-if="fieldErrors" type="error" variant="tonal" class="mt-4 rounded-lg">
              <div v-for="(msgs, key) in fieldErrors" :key="key" class="text-body-2">
                <div v-for="m in Array.isArray(msgs) ? msgs : [msgs]" :key="m">{{ key }}: {{ m }}</div>
              </div>
            </v-alert>
            <v-alert v-else-if="error" type="error" variant="tonal" class="mt-4 text-body-2">
              {{ error }}
            </v-alert>

            <div class="d-flex flex-wrap ga-3 mt-6">
              <v-btn to="/client/loans" variant="text">Cancel</v-btn>
              <v-btn type="submit" color="primary" :disabled="!canSubmit" :loading="loading">Submit application</v-btn>
            </div>
          </v-form>
        </v-card-text>
      </v-card>
    </template>
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'
import { useAuthStore } from '~~/stores/auth'

definePageMeta({
  layout: 'client',
  middleware: 'auth',
})

const router = useRouter()
const { api } = useApi()
const auth = useAuthStore()
const formRef = ref(null)

const applicationBlocked = computed(() => auth.user?.can_submit_loan_application === false)
const activeLoan = computed(() => auth.user?.active_loan ?? null)

const loanType = ref('')
const monthlyIncomeOther = ref('')
const amount = ref(5000)
const tenure = ref(24)
const hasExistingLoan = ref(null)
const purpose = ref('')
const payslipFiles = ref(null)
const acceptTerms = ref(false)

const loading = ref(false)
const error = ref('')
const fieldErrors = ref(null)

const loanTypeItems = [
  'Personal loan',
  'Apartment / home purchase',
  'Vehicle loan',
  'Business loan',
  'Education loan',
  'Emergency / medical',
  'Other',
]

const incomeSourceItems = ['None', 'Employment / salary', 'Business income', 'Investment', 'Other']

const yesNoItems = [
  { title: 'No', value: false },
  { title: 'Yes', value: true },
]

function isEmpty(v) {
  if (v === null || v === undefined) return true
  if (typeof v === 'boolean') return false
  if (typeof v === 'number') return Number.isNaN(v)
  if (typeof v === 'string') return v.trim() === ''
  if (Array.isArray(v)) return v.length === 0 || v.every((x) => x == null)
  if (v instanceof File) return false
  return false
}

const rules = {
  required: (v) => !isEmpty(v) || 'This field is required',
  yesNo: (v) => (v === true || v === false ? true : 'Please select Yes or No'),
  amountMin: (v) => {
    if (isEmpty(v)) return true
    return Number(v) >= 100 || 'Minimum amount is ₱100'
  },
  tenureRange: (v) => {
    if (isEmpty(v)) return true
    const n = Number(v)
    if (Number.isNaN(n)) return 'Enter a valid tenure'
    if (n < 1 || n > 360) return 'Tenure must be between 1 and 360 months'
    return true
  },
  payslipRequired: () => {
    const f = payslipFiles.value
    const file = Array.isArray(f) ? f[0] : f
    return !!file || 'Please attach your payslip image'
  },
  terms: (v) => !!v || 'You must accept the declaration',
}

function rulePasses(result) {
  return result === true
}

const canSubmit = computed(() => {
  if (isEmpty(loanType.value)) return false
  if (isEmpty(monthlyIncomeOther.value)) return false
  if (!rulePasses(rules.amountMin(amount.value))) return false
  if (!rulePasses(rules.tenureRange(tenure.value))) return false
  if (!rulePasses(rules.yesNo(hasExistingLoan.value))) return false
  if (isEmpty(purpose.value)) return false
  const f = payslipFiles.value
  const file = Array.isArray(f) ? f[0] : f
  if (!file) return false
  if (!acceptTerms.value) return false
  return true
})

onMounted(async () => {
  auth.initFromStorage()
  if (auth.token) {
    try {
      await auth.fetchMe(api)
    } catch {
      /* ignore */
    }
  }
})

function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

async function submit() {
  if (!canSubmit.value) return
  error.value = ''
  fieldErrors.value = null
  const { valid } = await formRef.value.validate()
  if (!valid) return

  loading.value = true
  try {
    const f = payslipFiles.value
    const file = Array.isArray(f) ? f[0] : f
    const payslip_base64 = await fileToBase64(file)

    await api('/client/loans', {
      method: 'POST',
      body: {
        loan_type: loanType.value,
        monthly_income_other: monthlyIncomeOther.value,
        has_existing_loan: hasExistingLoan.value,
        accept_terms: true,
        amount: amount.value,
        tenure: tenure.value,
        purpose: purpose.value.trim(),
        payslip_base64,
      },
    })
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
