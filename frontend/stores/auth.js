import { defineStore } from 'pinia'

const TOKEN_KEY = 'lms_token'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    user: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.type === 'admin',
  },
  actions: {
    initFromStorage() {
      if (!import.meta.client) {
        return
      }
      const t = localStorage.getItem(TOKEN_KEY)
      if (t) {
        this.token = t
      }
    },
    setSession({ token, user }) {
      this.token = token
      this.user = user
      if (import.meta.client) {
        localStorage.setItem(TOKEN_KEY, token)
      }
    },
    clearSession() {
      this.token = null
      this.user = null
      if (import.meta.client) {
        localStorage.removeItem(TOKEN_KEY)
      }
    },
    async fetchMe(api) {
      const res = await api('/me')
      this.user = res.user
    },
    async logout(api) {
      try {
        await api('/logout', { method: 'POST', body: {} })
      } catch {
        /* ignore */
      }
      this.clearSession()
    },
  },
})
