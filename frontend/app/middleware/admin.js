import { useAuthStore } from '~~/stores/auth'

export default defineNuxtRouteMiddleware(() => {
  if (import.meta.client) {
    useAuthStore().initFromStorage()
  }
  const auth = useAuthStore()
  if (!auth.token) {
    return navigateTo('/login')
  }
  if (!auth.isAdmin) {
    return navigateTo('/client/dashboard')
  }
})
