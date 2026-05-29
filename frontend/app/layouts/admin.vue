<template>
    <div class="app-shell">
        <!-- Mobile: full-width bar to open the drawer -->
        <v-app-bar v-if="isMobile" color="surface" flat class="app-shell-bar">
            <v-app-bar-nav-icon aria-label="Open menu" @click="openMobileDrawer" />
            <v-img src="/logo.png" height="32" width="32" />
        </v-app-bar>

        <div class="app-shell-body d-flex">
            <v-navigation-drawer
                v-model="drawerModel"
                :permanent="!isMobile"
                :temporary="isMobile"
                :rail="!isMobile && railCollapsed"
                width="260"
                rail-width="72"
                color="surface"
                class="border-e"
            >
                <div class="d-flex flex-column fill-height">
                    <div
                        class="pa-3 d-flex align-center ga-1"
                        :class="!isMobile && railCollapsed ? 'flex-column' : 'justify-space-between'"
                    >
                        <template v-if="!railCollapsed || isMobile">
                            <v-img src="/logo.png" height="100" width="100" />
                        </template>
                        <v-tooltip v-else location="end" text="LoanHub Admin">
                            <template #activator="{ props }">
                                <v-icon v-bind="props" color="primary" size="28">mdi-shield-account-outline</v-icon>
                            </template>
                        </v-tooltip>
                    </div>

                    <div class="d-flex align-center justify-center">
                          <p class="text-caption text-medium-emphasis mt-1">Admin portal</p>
                      </div>

                    <v-divider class="mb-1" />

                    <v-list class="flex-grow-1" nav ">
                        <v-list-item
                            to="/admin"
                            title="Dashboard"
                            prepend-icon="mdi-view-dashboard-outline"
                            rounded="lg"
                            @click="closeMobileDrawer"
                        />
                        <v-list-item
                            to="/admin/loans"
                            title="Loan applications"
                            prepend-icon="mdi-file-document-outline"
                            rounded="lg"
                            @click="closeMobileDrawer"
                        />
                        <v-list-item
                            to="/admin/payments"
                            title="Payments"
                            prepend-icon="mdi-cash-multiple"
                            rounded="lg"
                            @click="closeMobileDrawer"
                        />
                        <v-list-item
                            to="/admin/logs"
                            title="Activity logs"
                            prepend-icon="mdi-history"
                            rounded="lg"
                            @click="closeMobileDrawer"
                        />
                    </v-list>

                    <div class="pa-3 border-t">
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
                                class="text-none"
                                @click="onLogout"
                            >
                                Sign out
                            </v-btn>
                        </template>
                    </div>
                </div>
            </v-navigation-drawer>

            <!-- Desktop: app bar only over the content column (not full viewport width) -->
            <v-main class="main-column flex-grow-1 bg-background">
                <v-app-bar
                    v-if="!isMobile"
                    flat
                    color="surface"
                    "
                    class="main-top-bar flex-shrink-0"
                >
                    <v-toolbar-title class="text-h6 font-weight-semibold">{{ adminPageTitle }}</v-toolbar-title>
                </v-app-bar>
                <v-container fluid class="pa-6 main-scroll">
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

    const adminPageTitle = computed(() => {
        const titles = {
            "/admin": "Dashboard",
            "/admin/loans": "Loan applications",
            "/admin/payments": "Payments",
            "/admin/logs": "Activity logs",
        };
        return titles[route.path] || "Admin";
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

    .main-top-bar {
        border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
    }

    .main-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
    }

    .border-t {
        border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
    }

    .border-e {
        border-inline-end: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
    }
</style>