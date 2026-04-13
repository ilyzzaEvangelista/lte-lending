import { useAuthStore } from '~~/stores/auth'

export default defineNuxtRouteMiddleware((to) => {
  if (import.meta.client) {
    useAuthStore().initFromStorage()
  }
  const auth = useAuthStore()
  if (!auth.token) {
    return navigateTo({ path: '/login', query: { redirect: to.fullPath } })
  }
})
