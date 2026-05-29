<template>
  <div class="admin-dashboard">
      <section class="dashboard-hero rounded-xl pa-6 pa-md-8 mb-8">
          <div class="d-flex flex-column flex-md-row flex-md-wrap align-md-center justify-md-space-between ga-4">
              <div>
                  <!-- <p class="text-overline text-primary font-weight-bold mb-1 letter-space">Dashboard</p> -->
                  <h1 class="text-h4 text-overline text-primary font-weight-bold mb-1 letter-space text-capitalize">Admin dashboard</h1>
                  <p class="text-body-1 text-medium-emphasis mb-0 text-pre-wrap">
                      Applications, portfolio exposure, and payment queue at a glance.
                  </p>
              </div>
              <v-btn
                  color="primary"
                  variant="outlined"
                  size="small"
                  prepend-icon="mdi-refresh"
                  class="text-none align-self-start align-self-md-center"
                  :loading="loading"
                  @click="load"
              >
                  Refresh
              </v-btn>
          </div>
      </section>

      <v-alert v-if="err" type="error" variant="tonal" class="mb-6 rounded-lg" border="start">{{ err }}</v-alert>

      <v-skeleton-loader v-if="loading" type="heading, card@2, table-heading" class="rounded-xl mb-8" />
      <template v-else-if="stats">
          <v-row dense class="kpi-row ma-1">
              <v-col v-for="card in kpiCards" :key="card.id" cols="12" sm="6" xl="3">
                  <v-card
                      class="kpi-card h-100 rounded-lg"
                      :class="`kpi-card--accent-${card.accent}`"
                      elevation="0"
                      border
                  >
                      <v-card-text class="kpi-card__inner pa-3">
                          <div class="d-flex align-start justify-space-between ga-2">
                              <div class="min-w-0 flex-grow-1">
                                  <p
                                      class="text-caption text-medium-emphasis font-weight-medium text-uppercase letter-space mb-1"
                                  >
                                      {{ card.label }}
                                  </p>
                                  <p class="kpi-card__value font-weight-bold mb-0" :class="card.valueClass">
                                      {{ card.value }}
                                  </p>
                                  <p class="text-caption text-medium-emphasis mt-1 mb-0">{{ card.hint }}</p>
                              </div>
                              <v-avatar
                                  :color="card.avatarColor"
                                  variant="tonal"
                                  size="36"
                                  rounded="lg"
                                  class="flex-shrink-0 kpi-card__avatar"
                              >
                                  <v-icon :icon="card.icon" size="20" />
                              </v-avatar>
                          </div>
                      </v-card-text>
                  </v-card>
              </v-col>
          </v-row>

          <v-row dense class="mt-6 ">
              <v-col cols="12" lg="7">
                  <v-card class="rounded-xl panel-card pa-3" elevation="0" border>
                      <v-card-item class="pb-0">
                          <v-card-title class="text-h6 font-weight-bold ps-0">Loans by status</v-card-title>
                      </v-card-item>
                      <v-card-text class="pt-4">
                          <div v-for="row in statusRows" :key="row.key" class="status-row mb-5">
                              <div class="d-flex align-center justify-space-between mb-2">
                                  <span class="text-body-1 font-weight-medium text-capitalize">{{ row.key }}</span>
                                  <div class="d-flex align-center ga-2">
                                      <v-chip
                                          size="small"
                                          variant="flat"
                                          :color="statusColor(row.key)"
                                          class="font-weight-medium"
                                      >
                                          {{ row.count }}
                                      </v-chip>
                                  </div>
                              </div>
                              <v-progress-linear
                                  :model-value="row.pct"
                                  :color="statusColor(row.key)"
                                  bg-color="surface-variant"
                                  height="10"
                                  rounded
                              />
                          </div>
                          <p v-if="!stats.total_loans" class="text-body-2 text-medium-emphasis mb-0">
                              No applications yet.
                          </p>
                      </v-card-text>
                  </v-card>
              </v-col>

              <v-col cols="12" lg="5">
                  <v-card class="rounded-xl panel-card mb-4 pa-3" elevation="0" border>
                      <v-card-item class="pb-0">
                          <v-card-title class="text-h6 font-weight-bold ps-0">Payments pipeline</v-card-title>
                      </v-card-item>
                      <v-card-text class="pt-4">
                          <v-row dense>
                              <v-col v-for="step in paymentSteps" :key="step.key" cols="4">
                                  <div
                                      class="pipeline-cell text-center pa-3 rounded-lg"
                                      :class="`pipeline-cell--${step.tone}`"
                                  >
                                      <v-icon :icon="step.icon" size="22" class="mb-2 opacity-90" />
                                      <div class="text-h5 font-weight-bold tabular-nums">{{ step.value }}</div>
                                      <div class="text-caption text-medium-emphasis">{{ step.label }}</div>
                                  </div>
                              </v-col>
                          </v-row>
                      </v-card-text>
                  </v-card>

                  <v-card class="rounded-xl panel-card" elevation="0" border>
                      <v-card-item class="pb-0">
                          <v-card-title class="text-h6 font-weight-bold ps-0">Activity &amp; customers</v-card-title>
                          <v-card-subtitle class="ps-0">Engagement snapshot</v-card-subtitle>
                      </v-card-item>
                      <v-card-text class="pt-2">
                          <v-list lines="two" class="bg-transparent py-0" ">
                              <v-list-item rounded="lg" class="px-2 mb-1 activity-list-item">
                                  <template #prepend>
                                      <v-avatar color="primary" variant="tonal" size="40" rounded="lg">
                                          <v-icon icon="mdi-pulse" size="22" />
                                      </v-avatar>
                                  </template>
                                  <v-list-item-title class="font-weight-medium"
                                      >Log events (7 days)</v-list-item-title
                                  >
                                  <v-list-item-subtitle>Audit trail volume</v-list-item-subtitle>
                                  <template #append>
                                      <span class="text-h6 font-weight-bold tabular-nums"
                                          >{{ stats.activity_last_7_days }}</span
                                      >
                                  </template>
                              </v-list-item>
                              <v-list-item rounded="lg" class="px-2 activity-list-item">
                                  <template #prepend>
                                      <v-avatar color="secondary" variant="tonal" size="40" rounded="lg">
                                          <v-icon icon="mdi-account-group-outline" size="22" />
                                      </v-avatar>
                                  </template>
                                  <v-list-item-title class="font-weight-medium"
                                      >Registered customers</v-list-item-title
                                  >
                                  <v-list-item-subtitle>Total borrower accounts</v-list-item-subtitle>
                                  <template #append>
                                      <span class="text-h6 font-weight-bold tabular-nums"
                                          >{{ stats.customers_count }}</span
                                      >
                                  </template>
                              </v-list-item>
                          </v-list>
                      </v-card-text>
                  </v-card>
              </v-col>
          </v-row>
      </template>
  </div>
</template>

<script setup>
  import { useApi } from "~~/composables/useApi";
  import { formatMoney } from "~~/composables/formatMoney";

  definePageMeta({
      layout: "admin",
      middleware: "admin",
  });

  const { api } = useApi();
  const stats = ref(null);
  const loading = ref(true);
  const err = ref("");

  const kpiCards = computed(() => {
      const s = stats.value;
      if (!s) return [];
      return [
          {
              id: "apps",
              label: "Loan applications",
              value: s.total_loans,
              hint: "Across every status",
              icon: "mdi-file-document-multiple-outline",
              avatarColor: "primary",
              accent: "primary",
              valueClass: "",
          },
          {
              id: "pending",
              label: "Pending review",
              value: s.loans_pending_review,
              hint: "Awaiting your decision",
              icon: "mdi-clock-alert-outline",
              avatarColor: "warning",
              accent: "warning",
              valueClass: s.loans_pending_review > 0 ? "text-warning" : "",
          },
          {
              id: "exposure",
              label: "Portfolio exposure",
              value: formatMoney(s.portfolio_exposure),
              hint: "Approved + active principal",
              icon: "mdi-chart-timeline-variant",
              avatarColor: "success",
              accent: "success",
              valueClass: "text-truncate",
          },
          {
              id: "paypend",
              label: "Payments pending",
              value: s.payments_pending,
              hint: "Confirm or reject",
              icon: "mdi-cash-clock",
              avatarColor: "warning",
              accent: "secondary",
              valueClass: s.payments_pending > 0 ? "text-warning" : "",
          },
      ];
  });

  const paymentSteps = computed(() => {
      const s = stats.value;
      if (!s) return [];
      return [
          {
              key: "ok",
              label: "Confirmed",
              value: s.payments_confirmed,
              icon: "mdi-check-decagram-outline",
              tone: "success",
          },
          { key: "wait", label: "Pending", value: s.payments_pending, icon: "mdi-timer-sand", tone: "warning" },
          {
              key: "no",
              label: "Rejected",
              value: s.payments_rejected,
              icon: "mdi-close-circle-outline",
              tone: "error",
          },
      ];
  });

  const statusRows = computed(() => {
      const s = stats.value;
      if (!s?.loans_by_status) return [];
      const total = s.total_loans || 0;
      return Object.entries(s.loans_by_status).map(([key, count]) => ({
          key,
          count,
          pct: total ? Math.round((count / total) * 100) : 0,
      }));
  });

  onMounted(load);

  async function load() {
      loading.value = true;
      err.value = "";
      try {
          const res = await api("/admin/dashboard");
          stats.value = res.data;
      } catch (e) {
          err.value = e?.data?.message || "Could not load dashboard.";
      } finally {
          loading.value = false;
      }
  }

  function statusColor(status) {
      const map = {
          pending: "warning",
          approved: "success",
          active: "success",
          rejected: "error",
          closed: "secondary",
      };
      return map[status] || "default";
  }
</script>

<style scoped>
  .admin-dashboard {
      width: 100%;
      max-width: min(1320px, 100%);
      margin-inline: auto;
  }

  .letter-space {
      letter-spacing: 0.08em;
  }

  .dashboard-hero {
      background: linear-gradient(
          135deg,
          rgb(var(--v-theme-surface)) 0%,
          rgba(var(--v-theme-primary), 0.06) 45%,
          rgba(var(--v-theme-primary), 0.12) 100%
      );
      border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .kpi-card {
      transition:
          box-shadow 0.2s ease,
          transform 0.2s ease;
      border-inline-start: 3px solid transparent;
  }

  .kpi-card__value {
      font-size: 1.25rem;
      line-height: 1.3;
      letter-spacing: -0.02em;
  }

  @media (min-width: 600px) {
      .kpi-card__value {
          font-size: 1.35rem;
      }
  }

  .kpi-card__avatar {
      margin-top: 2px;
  }

  .kpi-card:hover {
      box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
      transform: translateY(-1px);
  }

  .kpi-card--accent-primary {
      border-inline-start-color: rgb(var(--v-theme-primary));
  }
  .kpi-card--accent-warning {
      border-inline-start-color: rgb(var(--v-theme-warning));
  }
  .kpi-card--accent-success {
      border-inline-start-color: rgb(var(--v-theme-success));
  }
  .kpi-card--accent-secondary {
      border-inline-start-color: rgb(var(--v-theme-secondary));
  }

  .panel-card {
      background: rgb(var(--v-theme-surface));
  }

  .status-row:last-child {
      margin-bottom: 0 !important;
  }

  .pipeline-cell {
      border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
      background: rgb(var(--v-theme-surface));
  }

  .pipeline-cell--success {
      background: rgba(var(--v-theme-success), 0.08);
  }
  .pipeline-cell--warning {
      background: rgba(var(--v-theme-warning), 0.1);
  }
  .pipeline-cell--error {
      background: rgba(var(--v-theme-error), 0.06);
  }

  .activity-list-item {
      border: 1px solid transparent;
  }

  .activity-list-item:hover {
      background: rgba(var(--v-theme-on-surface), 0.04);
      border-color: rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .quick-link {
      transition:
          transform 0.2s ease,
          box-shadow 0.2s ease;
      border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  }

  .quick-link:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 28px rgba(15, 23, 42, 0.1);
  }

  .quick-link--muted:hover {
      box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
  }

  .tabular-nums {
      font-variant-numeric: tabular-nums;
  }
</style>