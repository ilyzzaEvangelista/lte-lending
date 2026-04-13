<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-6">Activity logs</h1>
    <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
    <v-card border>
      <v-data-table
        :headers="headers"
        :items="logs"
        :loading="loading"
        items-per-page="25"
        density="compact"
      >
        <template #item.logged_at="{ item }">{{ formatDate(item.logged_at) }}</template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script setup>
import { useApi } from '~~/composables/useApi'

definePageMeta({
  layout: 'admin',
  middleware: 'admin',
})

const { api } = useApi()
const logs = ref([])
const loading = ref(true)
const err = ref('')

const headers = [
  { title: 'When', key: 'logged_at' },
  { title: 'Actor', key: 'actor_type' },
  { title: 'Actor id', key: 'actor_id' },
  { title: 'Event', key: 'log_name' },
  { title: 'User ref', key: 'log_user' },
]

onMounted(async () => {
  try {
    const res = await api('/admin/logs')
    logs.value = res.data
  } catch (e) {
    err.value = e?.data?.message || 'Could not load logs.'
  } finally {
    loading.value = false
  }
})

function formatDate(d) {
  return d ? new Date(d).toLocaleString() : '—'
}
</script>
