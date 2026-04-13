<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-6">Loan applications</h1>
    <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
    <v-skeleton-loader v-if="loading" type="card, card" />
    <v-card v-else-if="!loans.length" border>
      <v-card-text>No loan applications yet.</v-card-text>
    </v-card>
    <div v-else class="d-flex flex-column ga-4">
      <v-card v-for="loan in loans" :key="loan.id" border>
        <v-card-title class="d-flex flex-wrap align-start justify-space-between ga-2">
          <div>
            <code class="text-body-1">{{ loan.transaction_no }}</code>
            <div class="text-body-2 text-medium-emphasis mt-1">{{ customerLabel(loan) }}</div>
          </div>
          <v-chip :color="statusColor(loan.status)" size="small" variant="flat">{{ loan.status }}</v-chip>
        </v-card-title>
        <v-card-text>
          <v-row dense>
            <v-col cols="6" sm="3">
              <div class="text-caption text-medium-emphasis">Amount</div>
              <div>{{ formatMoney(loan.amount) }}</div>
            </v-col>
            <v-col cols="6" sm="3">
              <div class="text-caption text-medium-emphasis">Tenure</div>
              <div>{{ loan.tenure }} mo</div>
            </v-col>
            <v-col cols="6" sm="3">
              <div class="text-caption text-medium-emphasis">Monthly</div>
              <div>{{ loan.monthly != null ? formatMoney(loan.monthly) : '—' }}</div>
            </v-col>
            <v-col cols="6" sm="3">
              <div class="text-caption text-medium-emphasis">Interest</div>
              <div>{{ loan.interest }}%</div>
            </v-col>
            <v-col cols="12">
              <div class="text-caption text-medium-emphasis">Purpose</div>
              <div>{{ loan.purpose || '—' }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Payslip</div>
              <div>{{ loan.has_payslip ? 'Yes' : 'No' }}</div>
            </v-col>
          </v-row>
          <v-divider class="my-4" />
          <v-row dense align="end">
            <v-col cols="12" sm="3">
              <v-select
                v-model="loan._edit.status"
                :items="statusItems"
                label="Status"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
            <v-col cols="12" sm="3">
              <v-text-field
                v-model.number="loan._edit.interest"
                label="Interest %"
                type="number"
                variant="outlined"
                density="compact"
                hide-details
                min="0"
                max="100"
                step="0.01"
              />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field v-model="loan._edit.admin_note" label="Admin note" variant="outlined" density="compact" hide-details />
            </v-col>
            <v-col cols="12" sm="2">
              <v-btn color="primary" block :loading="loan._saving" @click="save(loan)">Save</v-btn>
            </v-col>
          </v-row>
          <v-alert v-if="loan._msg" type="error" variant="tonal" density="compact" class="mt-3">{{ loan._msg }}</v-alert>
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
const loans = ref([])
const loading = ref(true)
const err = ref('')

const statusItems = ['pending', 'approved', 'rejected', 'active', 'closed']

onMounted(load)

function customerLabel(loan) {
  const c = loan.customer
  if (!c) {
    return ''
  }
  const name = [c.first_name, c.last_name].filter(Boolean).join(' ')
  return `${name} · ${c.email}`
}

async function load() {
  loading.value = true
  err.value = ''
  try {
    const res = await api('/admin/loans')
    loans.value = res.data.map((row) => ({
      ...row,
      _edit: {
        status: row.status,
        interest: row.interest != null ? Number(row.interest) : 0,
        admin_note: row.admin_note ?? '',
      },
      _saving: false,
      _msg: '',
    }))
  } catch (e) {
    err.value = e?.data?.message || 'Could not load loans.'
  } finally {
    loading.value = false
  }
}

async function save(loan) {
  loan._msg = ''
  loan._saving = true
  try {
    const res = await api(`/admin/loans/${loan.id}`, {
      method: 'PATCH',
      body: {
        status: loan._edit.status,
        interest: loan._edit.interest,
        admin_note: loan._edit.admin_note || null,
      },
    })
    const u = res.data
    Object.assign(loan, u)
    loan._edit.status = u.status
    loan._edit.interest = u.interest != null ? Number(u.interest) : 0
    loan._edit.admin_note = u.admin_note ?? ''
  } catch (e) {
    loan._msg = e?.data?.message || 'Update failed.'
  } finally {
    loan._saving = false
  }
}

function statusColor(status) {
  const map = { pending: 'warning', approved: 'success', active: 'success', rejected: 'error', closed: 'secondary' }
  return map[status] || 'default'
}
</script>
