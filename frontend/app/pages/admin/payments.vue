<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-6">Loan payments</h1>
    <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
    <v-skeleton-loader v-if="loading" type="card, card" />
    <v-card v-else-if="!payments.length" border>
      <v-card-text>No payments yet.</v-card-text>
    </v-card>
    <div v-else class="d-flex flex-column ga-4">
      <v-card v-for="p in payments" :key="p.id" border>
        <v-card-title class="d-flex flex-wrap justify-space-between align-start ga-2">
          <div>
            <span class="text-h6">{{ p.full_name }}</span>
            <div class="text-body-2 text-medium-emphasis">{{ p.transaction_no }} · {{ p.customer?.email }}</div>
          </div>
          <v-chip size="small" variant="flat">{{ p.status }}</v-chip>
        </v-card-title>
        <v-card-text>
          <div class="d-flex flex-wrap ga-4 mb-3">
            <span>Amount: <strong>{{ formatMoney(p.amount) }}</strong></span>
            <span v-if="p.balance != null">Balance after: <strong>{{ formatMoney(p.balance) }}</strong></span>
            <span>Receipt: {{ p.has_receipt ? 'Yes' : 'No' }}</span>
          </div>
          <div v-if="p.status === 'pending'" class="d-flex flex-wrap ga-2">
            <v-btn color="primary" size="small" :loading="p._busy" @click="setStatus(p, 'confirmed')">Confirm</v-btn>
            <v-btn variant="outlined" size="small" :loading="p._busy" @click="setStatus(p, 'rejected')">Reject</v-btn>
          </div>
          <v-alert v-if="p._msg" type="error" variant="tonal" density="compact" class="mt-2">{{ p._msg }}</v-alert>
        </v-card-text>
      </v-card>
    </div>
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'
import { formatMoney } from '~~/composables/formatMoney'

definePageMeta({
  layout: 'admin',
  middleware: 'admin',
})

const { api } = useApi()
const payments = ref([])
const loading = ref(true)
const err = ref('')

onMounted(load)

async function load() {
  loading.value = true
  err.value = ''
  try {
    const res = await api('/admin/payments')
    payments.value = res.data.map((row) => ({ ...row, _busy: false, _msg: '' }))
  } catch (e) {
    err.value = e?.data?.message || 'Could not load payments.'
  } finally {
    loading.value = false
  }
}

async function setStatus(p, status) {
  p._msg = ''
  p._busy = true
  try {
    const res = await api(`/admin/payments/${p.id}`, {
      method: 'PATCH',
      body: { status },
    })
    Object.assign(p, res.data)
  } catch (e) {
    p._msg = e?.data?.message || 'Update failed.'
  } finally {
    p._busy = false
  }
}
</script>
