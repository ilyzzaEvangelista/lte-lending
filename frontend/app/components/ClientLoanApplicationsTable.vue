<template>
  <div>
    <v-progress-linear v-if="loading" indeterminate class="mb-4" color="primary" />
    <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
    <v-card v-if="!loading && !loans.length" border>
      <v-card-text class="text-medium-emphasis">{{ emptyMessage }}</v-card-text>
    </v-card>
    <v-card v-else-if="!loading" border class="overflow-x-auto">
      <v-data-table
        :headers="headers"
        :items="loans"
        :loading="loading"
        items-per-page="-1"
        hide-default-footer
        class="client-loans-table"
      >
        <template #item.transaction_no="{ item }">
          <code class="text-caption">{{ item.transaction_no }}</code>
        </template>
        <template #item.amount="{ item }">{{ formatMoney(item.amount) }}</template>
        <template #item.monthly="{ item }">
          {{ item.monthly != null ? formatMoney(item.monthly) : '—' }}
        </template>
        <template #item.interest="{ item }">{{ item.interest }}%</template>
        <template #item.status="{ item }">
          <v-chip size="small" :color="statusColor(item.status)" variant="flat">{{ item.status }}</v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-btn :to="`/client/loans/${item.id}`" size="small" variant="text" color="primary"> Details </v-btn>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'
import { formatMoney } from '~~/composables/formatMoney'

defineProps({
  emptyMessage: {
    type: String,
    default: 'You have no applications yet.',
  },
})

const { api } = useApi()
const loans = ref([])
const loading = ref(true)
const err = ref('')

const headers = [
  { title: 'Reference', key: 'transaction_no', sortable: true },
  { title: 'Amount', key: 'amount', sortable: true },
  { title: 'Tenure (mo)', key: 'tenure', sortable: true },
  { title: 'Monthly', key: 'monthly' },
  { title: 'Status', key: 'status' },
  { title: 'Interest', key: 'interest' },
  { title: '', key: 'actions', sortable: false },
]

onMounted(load)

async function load() {
  loading.value = true
  err.value = ''
  try {
    const res = await api('/client/loans')
    loans.value = res.data
  } catch (e) {
    err.value = e?.data?.message || 'Could not load loans.'
  } finally {
    loading.value = false
  }
}

function statusColor(status) {
  const map = {
    pending: 'warning',
    approved: 'success',
    active: 'success',
    rejected: 'error',
    closed: 'secondary',
  }
  return map[status] || 'default'
}
</script>
