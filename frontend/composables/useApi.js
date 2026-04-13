import { useAuthStore } from '~~/stores/auth'

export function useApi() {
  const config = useRuntimeConfig()
  const auth = useAuthStore()

  async function api(path, opts = {}) {
    const headers = {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...opts.headers,
    }
    if (auth.token) {
      headers.Authorization = `Bearer ${auth.token}`
    }
    const url = `${config.public.apiBase}/api${path}`
    return $fetch(url, {
      ...opts,
      headers,
    })
  }

  return { api }
}
