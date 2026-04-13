import { useApi } from '~~/composables/useApi'
import { useAuthStore } from '~~/stores/auth'

export default defineNuxtPlugin(async () => {
  const auth = useAuthStore()
  const { api } = useApi()

  auth.initFromStorage()
  if (auth.token && !auth.user) {
    try {
      await auth.fetchMe(api)
    } catch {
      auth.clearSession()
    }
  }
})
