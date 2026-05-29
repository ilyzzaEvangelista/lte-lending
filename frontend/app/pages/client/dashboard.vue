<template>
  <div class="client-dashboard">
    <section class="dash-hero rounded-xl pa-5 pa-sm-6 mb-6">
      <div class="d-flex flex-column flex-sm-row flex-wrap align-start justify-space-between ga-4">
        <div>
          <p class="text-overline text-primary font-weight-bold mb-1 letter-space">Dashboard</p>
          <h1 class="text-h5 text-sm-h4 font-weight-bold mb-2">Welcome back</h1>
        </div>
        <v-tooltip v-if="!canSubmitLoanApplication" location="bottom" text="You already have an active loan. Close it before starting a new application.">
          <template #activator="{ props: tip }">
            <span v-bind="tip" class="d-inline-block align-self-stretch align-self-sm-auto">
              <v-btn color="primary" variant="outlined" size="small" prepend-icon="mdi-plus" class="text-none" disabled>
                New application
              </v-btn>
            </span>
          </template>
        </v-tooltip>
        <v-btn
          v-else
          to="/client/loans/apply"
          color="primary"
          variant="outlined"
          size="small"
          prepend-icon="mdi-plus"
          class="text-none align-self-stretch align-self-sm-auto"
        >
          New application
        </v-btn>
      </div>
    </section>

    <v-alert v-if="err" type="error" variant="tonal" class="mb-4 rounded-lg" border="start">{{ err }}</v-alert>

    <v-skeleton-loader v-if="loading" type="heading, card@2" class="rounded-xl mb-6" />

    <template v-else-if="dash">
      <v-row dense class="mb-6">
        <v-col v-for="chip in countChips" :key="chip.id" cols="12" sm="4">
          <v-card class="dash-stat rounded-lg pa-3" border elevation="0">
            <div class="d-flex align-center justify-space-between ga-2">
              <div>
                <p class="text-caption text-medium-emphasis mb-1">{{ chip.label }}</p>
                <p class="text-h5 font-weight-bold tabular-nums mb-0">{{ chip.value }}</p>
              </div>
              <v-avatar :color="chip.color" variant="tonal" size="40" rounded="lg">
                <v-icon :icon="chip.icon" size="22" />
              </v-avatar>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <v-alert
          v-if="dueReminder"
          :type="dueReminder.type"
          variant="tonal"
          border="start"
          class="mb-6 rounded-lg due-reminder-alert"
          prominent
          prepend-icon="mdi-calendar-month-outline"
      >
          <v-alert-title class="text-subtitle-1 font-weight-bold">{{ dueReminder.title }}</v-alert-title>
          <div v-for="(line, i) in dueReminder.lines" :key="i" class="text-body-2 mt-2">{{ line }}</div>
          <v-btn
              v-if="paymentScheduleRow?.id"
              size="small"
              variant="text"
              color="primary"
              class="text-none px-0 mt-2"
              :to="`/client/loans/${paymentScheduleRow.id}`"
          >
              Open loan {{ dueReminder.reference }}
          </v-btn>
      </v-alert>

      <v-alert
          v-else-if="noDueYetNotice"
          type="info"
          variant="tonal"
          border="start"
          class="mb-6 rounded-lg"
          prominent
          prepend-icon="mdi-information-outline"
      >
          <v-alert-title class="text-subtitle-1 font-weight-bold">No payment due yet</v-alert-title>
          <p class="text-body-2 mt-2 mb-0">
              Installments appear after a loan is <strong>approved</strong> or <strong>active</strong>. You can track
              applications below.
          </p>
      </v-alert>

      <v-card v-if="paymentScheduleRow?.next_payment_due" class="rounded-xl mb-6 next-due-card" border elevation="0">
        <v-card-text class="pa-4 pa-sm-5">
          <div class="d-flex flex-column flex-sm-row flex-sm-wrap align-sm-center justify-sm-space-between ga-4">
            <div class="d-flex align-start ga-3 min-w-0">
              <v-avatar color="warning" variant="tonal" size="48" rounded="lg" class="flex-shrink-0">
                <v-icon icon="mdi-calendar-clock" size="26" />
              </v-avatar>
              <div class="min-w-0">
                <p class="text-overline text-medium-emphasis font-weight-bold mb-1">Next payment due</p>
                <p class="text-h4 text-sm-h3 font-weight-bold text-high-emphasis mb-1">
                  {{ formatDueDate(dash.soonest_payment.next_payment_due) }}
                </p>
                <p class="text-body-2 text-medium-emphasis mb-0 text-truncate">
                  Loan <code class="text-primary">{{ dash.soonest_payment.transaction_no }}</code>
                  <span v-if="dash.soonest_payment.monthly != null"> · {{ formatMoney(dash.soonest_payment.monthly) }}/mo</span>
                </p>
              </div>
            </div>
            <v-btn
              :to="`/client/loans/${dash.soonest_payment.id}`"
              color="primary"
              variant="flat"
              prepend-icon="mdi-eye"
              class="text-none flex-shrink-0"
            >
              View loan
            </v-btn>
          </div>
        </v-card-text>
      </v-card>

      <v-card
        v-else-if="dash.counts.approved_or_active > 0 && !paymentScheduleRow?.next_payment_due"
        class="rounded-xl mb-6 pa-4"
        variant="tonal"
        color="secondary"
        border
      >
        <p class="text-body-2 mb-0">
          Your approved loan is set up; installment dates will show here once the schedule is available in your loan
          detail.
        </p>
        <v-btn
          v-if="dash.upcoming_loans?.[0]?.id"
          :to="`/client/loans/${dash.upcoming_loans[0].id}`"
          variant="text"
          color="primary"
          class="text-none px-0 mt-2"
        >
          Open loan
        </v-btn>
        <v-btn v-else to="/client/loans" variant="text" color="primary" class="text-none px-0 mt-2"> View applications </v-btn>
      </v-card>

      <v-card
        v-else-if="dash.counts.pending > 0 && dash.counts.approved_or_active > 0"
        class="rounded-xl mb-6 pa-4"
        variant="tonal"
        border
      >
        <p class="text-body-2 text-medium-emphasis mb-0">
          You still have application(s) in review alongside your active loan(s). Open an application for status updates.
        </p>
      </v-card>

      <v-card v-if="otherUpcoming.length" class="rounded-xl mb-6" border elevation="0">
        <v-card-title class="text-subtitle-1 font-weight-bold">Other active loans</v-card-title>
        <v-list density="compact" class="bg-transparent py-0">
          <v-list-item v-for="row in otherUpcoming" :key="row.id" :to="`/client/loans/${row.id}`">
            <v-list-item-title class="font-weight-medium">
              <code class="text-caption text-primary">{{ row.transaction_no }}</code>
            </v-list-item-title>
            <v-list-item-subtitle>{{ formatDueDate(row.next_payment_due) }}</v-list-item-subtitle>
            <template #append>
              <v-icon icon="mdi-chevron-right" size="small" class="text-medium-emphasis" />
            </template>
          </v-list-item>
        </v-list>
      </v-card>
    </template>

    <h2 class="text-h6 font-weight-bold mb-3">Your applications</h2>
    <ClientLoanApplicationsTable />
  </div>
</template>

<script setup>
  import { useApi } from "~~/composables/useApi";
  import { formatMoney } from "~~/composables/formatMoney";
  import { useAuthStore } from "~~/stores/auth";

  definePageMeta({
      layout: "client",
      middleware: "auth",
  });

  const auth = useAuthStore();
  const { api } = useApi();

  const canSubmitLoanApplication = computed(() => auth.user?.can_submit_loan_application !== false);

  const dash = ref(null);
  const loading = ref(true);
  const err = ref("");

  const countChips = computed(() => {
      const c = dash.value?.counts;
      if (!c) return [];
      return [
          { id: "all", label: "Applications", value: c.applications, icon: "mdi-file-document-multiple-outline", color: "primary" },
          { id: "pend", label: "Pending review", value: c.pending, icon: "mdi-timer-sand", color: "warning" },
          { id: "live", label: "Approved / active", value: c.approved_or_active, icon: "mdi-check-decagram-outline", color: "success" },
      ];
  });

  /** Prefer API soonest row; otherwise first approved/active loan that has a computed due date. */
  const paymentScheduleRow = computed(() => {
      const d = dash.value;
      if (!d) return null;
      if (d.soonest_payment?.next_payment_due) return d.soonest_payment;
      return d.upcoming_loans?.find((r) => r.next_payment_due) ?? null;
  });

  /** Pending-only: nothing in the payment schedule yet. */
  const noDueYetNotice = computed(() => {
      const d = dash.value;
      if (!d) return false;
      if (paymentScheduleRow.value?.next_payment_due) return false;
      return d.counts.pending > 0 && d.counts.approved_or_active === 0;
  });

  const otherUpcoming = computed(() => {
      const list = dash.value?.upcoming_loans;
      const primary = paymentScheduleRow.value;
      if (!list?.length || !primary?.next_payment_due) return [];
      return list.filter((r) => r.id !== primary.id && r.next_payment_due);
  });

  /** Calendar days from today to due (negative = overdue). */
  function daysFromTodayToDueDate(iso) {
      if (!iso) return null;
      const s = String(iso).slice(0, 10);
      const parts = s.split("-").map(Number);
      if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) return null;
      const [y, m, d] = parts;
      const due = new Date(y, m - 1, d);
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      return Math.round((due.getTime() - today.getTime()) / 86400000);
  }

  /** Shown whenever a next due date exists — including far-future (“not due yet”) dates. */
  const dueReminder = computed(() => {
      const sp = paymentScheduleRow.value;
      const iso = sp?.next_payment_due;
      if (!iso || !sp?.transaction_no) return null;

      const days = daysFromTodayToDueDate(iso);
      if (days === null) return null;

      const dateLabel = formatDueDate(iso);
      const monthly = sp.monthly != null ? formatMoney(sp.monthly) : null;

      let type = "success";
      let title = "Upcoming installment";
      const lines = [];

      if (days < 0) {
          type = "error";
          title = "Payment overdue";
          const n = Math.abs(days);
          lines.push(
              `Your scheduled installment was due ${n} day${n === 1 ? "" : "s"} ago (${dateLabel}). Please submit your payment as soon as you can.`,
          );
      } else if (days === 0) {
          type = "warning";
          title = "Payment due reminder";
          lines.push(`Your installment is due today (${dateLabel}).`);
      } else if (days === 1) {
          type = "warning";
          title = "Payment due reminder";
          lines.push(`Your installment is due tomorrow (${dateLabel}).`);
      } else if (days <= 7) {
          type = "warning";
          title = "Payment due reminder";
          lines.push(`Your next installment is in ${days} days (${dateLabel}).`);
      } else {
          lines.push("Nothing is due right now — your next installment is scheduled for later.");
          lines.push(`${dateLabel} (${days} days from today).`);
      }

      if (monthly) {
          lines.push(`Expected monthly installment: ${monthly} (see your loan for remaining balance).`);
      }

      return {
          type,
          title,
          reference: sp.transaction_no,
          lines,
      };
  });

  onMounted(async () => {
      if (auth.token) {
          try {
              await auth.fetchMe(api);
          } catch {
              /* ignore */
          }
      }
      await load();
  });

  async function load() {
      loading.value = true;
      err.value = "";
      try {
          const res = await api("/client/dashboard");
          dash.value = res.data;
      } catch (e) {
          err.value = e?.data?.message || "Could not load dashboard.";
      } finally {
          loading.value = false;
      }
  }

  function formatDueDate(iso) {
      if (!iso) return "—";
      const d = new Date(`${String(iso).slice(0, 10)}T12:00:00`);
      if (Number.isNaN(d.getTime())) return iso;
      return d.toLocaleDateString(undefined, { weekday: "short", year: "numeric", month: "short", day: "numeric" });
  }
</script>

<style scoped>
  .client-dashboard {
      max-width: min(1200px, 100%);
      margin-inline: auto;
  }

  .letter-space {
      letter-spacing: 0.08em;
  }

  .dash-hero {
      background: linear-gradient(
          125deg,
          rgb(var(--v-theme-surface)) 0%,
          rgba(var(--v-theme-primary), 0.05) 50%,
          rgba(var(--v-theme-primary), 0.1) 100%
      );
      border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .dash-stat {
      background: rgb(var(--v-theme-surface));
  }

  .next-due-card {
      background: linear-gradient(180deg, rgba(var(--v-theme-warning), 0.08) 0%, rgb(var(--v-theme-surface)) 48%);
  }

  .tabular-nums {
      font-variant-numeric: tabular-nums;
  }
</style>
