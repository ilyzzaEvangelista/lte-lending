// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@pinia/nuxt', 'vuetify-nuxt-module'],
  css: ['@mdi/font/css/materialdesignicons.css', '~/assets/css/main.css'],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000',
    },
  },
  routeRules: {
    '/client/**': { ssr: false },
    '/admin/**': { ssr: false },
  },
  vuetify: {
    vuetifyOptions: {
      theme: {
        defaultTheme: 'light',
        themes: {
          light: {
            dark: false,
            colors: {
              primary: '#2563EB',
              secondary: '#64748B',
              accent: '#3B82F6',
              background: '#F8FAFC',
              surface: '#FFFFFF',
              error: '#DC2626',
              success: '#16A34A',
              warning: '#D97706',
            },
          },
          dark: {
            dark: true,
            colors: {
              primary: '#5B8DEF',
              secondary: '#8B9CB3',
              accent: '#5CA0FF',
              background: '#0F1419',
              surface: '#1A2332',
              error: '#FF453A',
              success: '#34C759',
              warning: '#FF9F0A',
            },
          },
        },
      },
      icons: {
        defaultSet: 'mdi',
      },
    },
  },
})
