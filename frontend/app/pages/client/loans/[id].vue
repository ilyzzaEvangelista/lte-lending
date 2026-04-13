<template>
  <div>
    <v-btn to="/client/loans" variant="text" prepend-icon="mdi-arrow-left" class="mb-4"> Back to list </v-btn>
    <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
    <template v-if="detail">
      <h1 class="text-h4 font-weight-bold mb-2">
        Loan <code class="text-primary">{{ detail.transaction_no }}</code>
      </h1>
      <v-row>
        <v-col cols="12" md="6">
          <v-card border class="mb-4">
            <v-card-title>Summary</v-card-title>
            <v-card-text>
              <v-table density="compact" class="bg-transparent">
                <tbody>
                  <tr>
                    <td class="text-medium-emphasis">Amount</td>
                    <td>{{ formatMoney(detail.amount) }}</td>
                  </tr>
                  <tr>
                    <td class="text-medium-emphasis">Tenure</td>
                    <td>{{ detail.tenure }} months</td>
                  </tr>
                  <tr>
                    <td class="text-medium-emphasis">Status</td>
                    <td>
                      <v-chip size="small" :color="statusColor(detail.status)" variant="flat">{{ detail.status }}</v-chip>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-medium-emphasis">Interest</td>
                    <td>{{ detail.interest }}%</td>
                  </tr>
                  <tr>
                    <td class="text-medium-emphasis">Monthly</td>
                    <td>{{ detail.monthly != null ? formatMoney(detail.monthly) : '—' }}</td>
                  </tr>
                  <tr>
                    <td class="text-medium-emphasis">Payslip</td>
                    <td>{{ detail.has_payslip ? 'Uploaded' : '—' }}</td>
                  </tr>
                  <tr v-if="detail.purpose">
                    <td class="text-medium-emphasis">Purpose</td>
                    <td>{{ detail.purpose }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col v-if="canPay" cols="12" md="6">
          <v-card border>
            <v-card-title>Submit payment</v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitPay">
                <v-text-field
                  v-model.number="payAmount"
                  label="Amount (PHP)"
                  type="number"
                  min="0.01"
                  step="0.01"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-currency-php"
                />
                <v-file-input
                  v-model="receiptFiles"
                  label="Receipt image (optional)"
                  accept="image/*"
                  variant="outlined"
                  density="comfortable"
                  clearable
                />
                <v-alert v-if="payErr" type="error" variant="tonal" density="compact" class="mb-2">{{ payErr }}</v-alert>
                <v-btn type="submit" color="primary" :loading="payLoading" block>Submit</v-btn>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <h2 class="text-h6 font-weight-bold mb-2">Loan records</h2>
      <v-card v-if="!detail.loan_records?.length" border class="mb-6">
        <v-card-text class="text-medium-emphasis">No records yet (pending approval).</v-card-text>
      </v-card>
      <v-card v-else border class="mb-6">
        <v-data-table
          :headers="recordHeaders"
          :items="detail.loan_records"
          items-per-page="-1"
          hide-default-footer
          density="compact"
        >
          <template #item.payment_date="{ item }">{{ item.payment_date || '—' }}</template>
          <template #item.payment="{ item }">{{ formatMoney(item.payment) }}</template>
          <template #item.balance="{ item }">{{ formatMoney(item.balance) }}</template>
        </v-data-table>
      </v-card>

      <h2 class="text-h6 font-weight-bold mb-2">Payments</h2>
      <v-card border>
        <v-data-table
          :headers="payHeaders"
          :items="detail.loan_payments || []"
          items-per-page="-1"
          hide-default-footer
          density="compact"
        >
          <template #item.amount="{ item }">{{ formatMoney(item.amount) }}</template>
          <template #item.balance="{ item }">
            {{ item.balance != null ? formatMoney(item.balance) : '—' }}
          </template>
          <template #item.status="{ item }">
            <v-chip size="x-small" variant="flat">{{ item.status }}</v-chip>
          </template>
        </v-data-table>
      </v-card>
    </template>
    <v-skeleton-loader v-else-if="loading" type="article" />
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'
import { formatMoney } from '~~/composables/formatMoney'

definePageMeta({
  layout: 'default',
  middleware: 'auth',
})

const route = useRoute()
const { api } = useApi()

const detail = ref(null)
const loading = ref(true)
const err = ref('')
const payAmount = ref(100)
const receiptFiles = ref(null)
const payLoading = ref(false)
const payErr = ref('')

const recordHeaders = [
  { title: '# pmts', key: 'no_of_payments' },
  { title: 'Date', key: 'payment_date' },
  { title: 'Payment', key: 'payment' },
  { title: 'Balance', key: 'balance' },
]

const payHeaders = [
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
  { title: 'Balance after', key: 'balance' },
]

const canPay = computed(() => {
  const s = detail.value?.status
  return s === 'approved' || s === 'active'
})

onMounted(load)

async function load() {
  loading.value = true
  err.value = ''
  try {
    const res = await api(`/client/loans/${route.params.id}`)
    detail.value = res.data
  } catch (e) {
    err.value = e?.data?.message || 'Could not load loan.'
  } finally {
    loading.value = false
  }
}

function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

async function submitPay() {
  payErr.value = ''
  payLoading.value = true
  try {
    const body = { amount: payAmount.value }
    const f = receiptFiles.value
    const file = Array.isArray(f) ? f[0] : f
    if (file) {
      body.receipt_base64 = await fileToBase64(file)
    }
    await api(`/client/loans/${route.params.id}/payments`, { method: 'POST', body })
    receiptFiles.value = null
    await load()
  } catch (e) {
    payErr.value = e?.data?.message || 'Payment failed.'
  } finally {
    payLoading.value = false
  }
}

function statusColor(status) {
  const map = { pending: 'warning', approved: 'success', active: 'success', rejected: 'error', closed: 'secondary' }
  return map[status] || 'default'
}
</script>
