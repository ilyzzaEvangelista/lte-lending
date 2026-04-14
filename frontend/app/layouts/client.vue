<template>
  <div class="app-shell portal-shell">
      <v-app-bar v-if="isMobile" flat class="app-shell-bar portal-app-bar">
          <v-app-bar-nav-icon aria-label="Open menu" @click="openMobileDrawer" />
          <v-img src="/logo.png" height="32" width="32" />
      </v-app-bar>

      <div class="app-shell-body d-flex">
          <v-navigation-drawer
              v-model="drawerModel"
              :permanent="!isMobile"
              :temporary="isMobile"
              :rail="!isMobile && railCollapsed"
              width="268"
              rail-width="72"
              class="portal-drawer"
              elevation="0"
          >
              <div class="d-flex flex-column fill-height">
                  <div class="portal-brand pa-4">
                      <div v-if="!railCollapsed || isMobile" class="text-center pt-1 min-w-0 d-flex align-center justify-center">
                          <v-img src="/logo.png" height="100" width="100" class="flex-shrink-0" />
                      </div>
                      <div v-else class="d-flex justify-center pt-1">
                          <v-tooltip location="end" text="LoanHub — Client portal">
                              <template #activator="{ props }">
                                  <v-icon v-bind="props" color="primary" size="28">mdi-bank-outline</v-icon>
                              </template>
                          </v-tooltip>
                      </div>
                      
                      <div class="d-flex align-center justify-center">
                          <p class="text-caption text-medium-emphasis mt-1">Client portal</p>
                      </div>
                  </div>

                  <v-list class="flex-grow-1 px-3 pt-2 portal-nav-list" nav density="comfortable">
                      <v-list-item
                          to="/client/dashboard"
                          title="Dashboard"
                          prepend-icon="mdi-view-dashboard-outline"
                          rounded="lg"
                          active-class="portal-nav-item-active"
                          @click="closeMobileDrawer"
                      />
                      <v-list-item
                          to="/client/loans"
                          title="My applications"
                          prepend-icon="mdi-file-document-outline"
                          rounded="lg"
                          active-class="portal-nav-item-active"
                          @click="closeMobileDrawer"
                      />
                      <v-list-item
                          to="/client/loans/apply"
                          title="New application"
                          prepend-icon="mdi-plus-circle-outline"
                          rounded="lg"
                          active-class="portal-nav-item-active"
                          @click="closeMobileDrawer"
                      />
                  </v-list>

                  <div class="pa-3 portal-drawer-footer">
                      <template v-if="!isMobile && railCollapsed">
                          <div class="d-flex flex-column align-center ga-2">
                              <v-tooltip text="Public site" location="end">
                                  <template #activator="{ props }">
                                      <v-btn
                                          v-bind="props"
                                          to="/"
                                          icon="mdi-web"
                                          variant="text"
                                          @click="closeMobileDrawer"
                                      />
                                  </template>
                              </v-tooltip>
                              <v-tooltip text="Sign out" location="end">
                                  <template #activator="{ props }">
                                      <v-btn
                                          v-bind="props"
                                          icon="mdi-logout"
                                          variant="outlined"
                                          size="small"
                                          class="portal-footer-btn"
                                          @click="onLogout"
                                      />
                                  </template>
                              </v-tooltip>
                          </div>
                      </template>
                      <template v-else>
                          <v-btn
                              variant="outlined"
                              prepend-icon="mdi-logout"
                              block
                              class="text-none portal-footer-btn"
                              @click="onLogout"
                          >
                              Sign out
                          </v-btn>
                      </template>
                  </div>
              </div>
          </v-navigation-drawer>

          <v-main class="main-column flex-grow-1 bg-background">
              <v-app-bar
                  v-if="!isMobile"
                  flat
                  density="comfortable"
                  elevation="0"
                  class="main-top-bar portal-app-bar flex-shrink-0"
              >
                  <v-app-bar-nav-icon aria-label="Toggle sidebar" @click="railCollapsed = !railCollapsed" />
                  <v-spacer />
                  <v-toolbar-title class="text-h6 font-weight-semibold">{{ clientPageTitle }}</v-toolbar-title>
              </v-app-bar>
              <v-container fluid class="px-4 px-sm-6 py-6 main-scroll">
                  <slot />
              </v-container>
          </v-main>
      </div>
  </div>
</template>

<script setup>
  import { useApi } from "~~/composables/useApi";
  import { useAuthStore } from "~~/stores/auth";

  const { mdAndDown } = useDisplay();
  const route = useRoute();
  const auth = useAuthStore();
  const { api } = useApi();
  const router = useRouter();

  const isMobile = computed(() => mdAndDown.value);
  const railCollapsed = ref(false);
  const mobileDrawer = ref(false);

  const clientPageTitle = computed(() => {
      const titles = {
          "/client/dashboard": "Dashboard",
          "/client/loans": "My applications",
          "/client/loans/apply": "New application",
      };
      if (titles[route.path]) return titles[route.path];
      if (route.path.startsWith("/client/loans/")) return "Application details";
      return "Client portal";
  });

  const drawerModel = computed({
      get() {
          return isMobile.value ? mobileDrawer.value : true;
      },
      set(value) {
          if (isMobile.value) {
              mobileDrawer.value = value;
          }
      },
  });

  watch(isMobile, (mobile) => {
      if (!mobile) {
          mobileDrawer.value = false;
      }
  });

  function openMobileDrawer() {
      mobileDrawer.value = true;
  }

  function closeMobileDrawer() {
      if (isMobile.value) {
          mobileDrawer.value = false;
      }
  }

  async function onLogout() {
      await auth.logout(api);
      await router.push("/");
  }
</script>

<style scoped>
  .app-shell {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
  }

  .app-shell-body {
      flex: 1 1 auto;
      min-height: 0;
  }

  .app-shell-bar {
      border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .main-column {
      display: flex;
      flex-direction: column;
      flex: 1 1 auto;
      min-height: 0;
  }

  .main-scroll {
      flex: 1 1 auto;
      min-height: 0;
      overflow: auto;
  }

  .main-top-bar {
      border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .border-t {
      border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .border-e {
      border-inline-end: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  }
</style>