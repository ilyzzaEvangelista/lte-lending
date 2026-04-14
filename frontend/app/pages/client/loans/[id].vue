<template>
  <div>
      <v-btn to="/client/loans" variant="text" prepend-icon="mdi-arrow-left" class="mb-4"> Back to list </v-btn>
      <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
      <template v-if="detail">
          <h1 class="text-h4 font-weight-bold mb-2">
              Loan <code class="text-primary">{{ detail.transaction_no }}</code>
          </h1>
          <v-row>
              <v-col cols="12" md="6">
                  <v-card border class="mb-4">
                      <v-card-title>Summary</v-card-title>
                      <v-card-text>
                          <v-table density="compact" class="bg-transparent">
                              <tbody>
                                  <tr>
                                      <td class="text-medium-emphasis">Amount</td>
                                      <td>{{ formatMoney(detail.amount) ?? 'N/A' }}</td>
                                  </tr>
                                  <tr>
                                      <td class="text-medium-emphasis">Tenure</td>
                                      <td>{{ detail.tenure ?? 'N/A' }} months</td>
                                  </tr>
                                  <tr>
                                      <td class="text-medium-emphasis">Status</td>
                                      <td>
                                          <v-chip class="text-capitalize" size="small" :color="statusColor(detail.status)" variant="flat"
                                              >{{ detail.status }}</v-chip
                                          >
                                      </td>
                                  </tr>
                                  <tr>
                                      <td class="text-medium-emphasis">Interest</td>
                                      <td>{{ detail.interest ?? 'N/A' }}%</td>
                                  </tr>
                                  <tr>
                                      <td class="text-medium-emphasis">Monthly</td>
                                      <td>{{ detail.monthly != null ? formatMoney(detail.monthly) : 'N/A' }}</td>
                                  </tr>
                                  <tr>
                                      <td class="text-medium-emphasis">Approved on</td>
                                      <td>{{ repaymentStartsLabel }}</td>
                                  </tr>
                                  <tr v-if="nextPaymentDueLabel">
                                      <td class="text-medium-emphasis">Next payment due</td>
                                      <td>
                                          <div>{{ nextPaymentDueLabel }}</div>
                                          <!-- <div class="text-caption text-medium-emphasis mt-1">{{ nextPaymentDueCaption }}</div> -->
                                      </td>
                                  </tr>
                                  <tr>
                                      <td class="text-medium-emphasis">Payslip</td>
                                      <td>
                                          <div v-if="!detail.has_payslip" class="text-medium-emphasis">Not uploaded</div>
                                          <div v-else class="d-flex align-center ga-3 flex-wrap">
                                              <span>Uploaded</span>
                                              <v-icon
                                                  v-if="payslipObjectUrl"
                                                  icon="mdi-eye"
                                                  size="small"
                                                  color="primary"
                                                  class="cursor-pointer"
                                                  aria-label="Enlarge payslip"
                                                  @click="openPayslipPreview"
                                              />
                                          </div>
                                      </td>
                                  </tr>
                                  <tr v-if="detail.purpose">
                                      <td class="text-medium-emphasis">Purpose</td>
                                      <td>{{ detail.purpose ?? 'N/A' }}</td>
                                  </tr>
                              </tbody>
                          </v-table>
                      </v-card-text>
                  </v-card>
              </v-col>
              <v-col v-if="canPay" cols="12" md="6">
                  <v-card border>
                      <v-card-title>Submit payment</v-card-title>
                      <v-card-text>
                          <v-form @submit.prevent="submitPay">
                              <v-text-field
                                  v-model.number="payAmount"
                                  label="Amount (PHP)"
                                  type="number"
                                  min="0.01"
                                  step="0.01"
                                  variant="outlined"
                                  density="comfortable"
                                  prepend-inner-icon="mdi-currency-php"
                              />
                              <v-file-input
                                  v-model="receiptFiles"
                                  label="Receipt image (optional)"
                                  accept="image/*"
                                  variant="outlined"
                                  density="comfortable"
                                  clearable
                              />
                              <v-alert v-if="payErr" type="error" variant="tonal" density="compact" class="mb-2"
                                  >{{ payErr }}</v-alert
                              >
                              <v-btn type="submit" color="primary" :loading="payLoading" block>Submit</v-btn>
                          </v-form>
                      </v-card-text>
                  </v-card>
              </v-col>
          </v-row>

          <h2 class="text-h6 font-weight-bold mb-2">Loan records</h2>
          <v-card v-if="!detail.loan_records?.length" border class="mb-6">
              <v-card-text class="text-medium-emphasis">No records yet (pending approval).</v-card-text>
          </v-card>
          <v-card v-else border class="mb-6">
              <v-data-table
                  :headers="recordHeaders"
                  :items="detail.loan_records"
                  items-per-page="-1"
                  hide-default-footer
                  density="compact"
              >
                  <template #item.payment_date="{ item }">
                    {{ item.payment_date ? formatLoanDate(item.payment_date) : 'N/A' }}
                  </template>
                  <template #item.payment="{ item }">{{ formatMoney(item.payment) }}</template>
                  <template #item.balance="{ item }">{{ formatMoney(item.balance) }}</template>
              </v-data-table>
          </v-card>

          <h2 class="text-h6 font-weight-bold mb-2">Payments</h2>
          <v-card border>
              <v-data-table
                  :headers="payHeaders"
                  :items="detail.loan_payments || []"
                  items-per-page="-1"
                  hide-default-footer
                  density="compact"
              >
                  <template #item.amount="{ item }">{{ formatMoney(item.amount) }}</template>
                  <template #item.balance="{ item }">
                      {{ item.balance != null ? formatMoney(item.balance) : '—' }}
                  </template>
                  <template #item.status="{ item }">
                      <v-chip size="x-small" variant="flat">{{ item.status }}</v-chip>
                  </template>
              </v-data-table>
          </v-card>
      </template>
      <v-skeleton-loader v-else-if="loading" type="article" />

      <v-dialog v-model="payslipDialog" max-width="920">
          <v-card>
              <v-card-title class="d-flex align-center justify-space-between text-wrap">
                  <span>Payslip</span>
                  <v-btn icon="mdi-close" variant="text" aria-label="Close" @click="payslipDialog = false" />
              </v-card-title>
              <v-card-text>
                  <v-img v-if="payslipObjectUrl" :src="payslipObjectUrl" max-height="80vh" contain alt="Payslip" />
              </v-card-text>
          </v-card>
      </v-dialog>
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

  const route = useRoute();
  const config = useRuntimeConfig();
  const auth = useAuthStore();
  const { api } = useApi();

  const detail = ref(null);
  const loading = ref(true);
  const err = ref("");
  const payAmount = ref(100);
  const receiptFiles = ref(null);
  const payLoading = ref(false);
  const payErr = ref("");
  const payslipObjectUrl = ref(null);
  const payslipDialog = ref(false);

  const recordHeaders = [
      { title: "No. of payments", key: "no_of_payments" },
      { title: "Date", key: "payment_date" },
      { title: "Payment", key: "payment" },
      { title: "Balance", key: "balance" },
  ];

  const payHeaders = [
      { title: "Amount", key: "amount" },
      { title: "Status", key: "status" },
      { title: "Balance after", key: "balance" },
  ];

  const canPay = computed(() => {
      const s = detail.value?.status;
      return s === "approved" || s === "active";
  });

  function formatLoanDate(iso) {
      if (!iso) return "—";
      const s = String(iso).slice(0, 10);
      const d = new Date(`${s}T12:00:00`);
      if (Number.isNaN(d.getTime())) return "—";
      return d.toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" });
  }

  function addMonthsToIsoDate(iso, months) {
      if (!iso || months < 1) return null;
      const s = String(iso).slice(0, 10);
      const d = new Date(`${s}T12:00:00`);
      if (Number.isNaN(d.getTime())) return null;
      d.setMonth(d.getMonth() + months);
      return d.toISOString().slice(0, 10);
  }

  function todayIsoLocal() {
      const t = new Date();
      const y = t.getFullYear();
      const m = String(t.getMonth() + 1).padStart(2, "0");
      const da = String(t.getDate()).padStart(2, "0");
      return `${y}-${m}-${da}`;
  }

  /** @returns {number} -1 | 0 | 1 */
  function compareIsoDates(a, b) {
      if (!a || !b) return 0;
      const sa = String(a).slice(0, 10);
      const sb = String(b).slice(0, 10);
      if (sa < sb) return -1;
      if (sa > sb) return 1;
      return 0;
  }

  /** First loan_record.payment_date = day the amortization row was opened (approval day in this app). */
  const repaymentStartsLabel = computed(() => {
      const d = detail.value;
      if (!d) return "—";
      const records = d.loan_records;
      if (records?.length) {
          const first = records[0];
          if (first?.payment_date) return formatLoanDate(first.payment_date);
      }
      if (d.status === "pending") return "When your loan is approved";
      return "—";
  });

  /**
   * Next due: same calendar day each month from approval (anchor).
   * `n` = completed installments on the latest loan_record (`no_of_payments`).
   * If you submitted a payment for the current due (pending) and today is on or after that due,
   * we show the following month so e.g. after paying May 14 you see June 14 (even before admin confirms).
   */
  const nextPaymentDueMeta = computed(() => {
      const d = detail.value;
      if (!d || !canPay.value) return { label: "", pendingShift: false };
      const records = d.loan_records;
      if (!records?.length) return { label: "", pendingShift: false };
      const first = records[0];
      const last = records[records.length - 1];
      const bal = Number(last?.balance);
      if (!(bal > 0)) return { label: "", pendingShift: false };
      const anchor = first?.payment_date;
      if (!anchor) return { label: "", pendingShift: false };
      const n = Number(last?.no_of_payments ?? 0);
      const currentPeriodDue = addMonthsToIsoDate(anchor, n + 1);
      const payments = d.loan_payments || [];
      const hasPending = payments.some((p) => p.status === "pending");
      const pendingAdvancesSchedule =
          Boolean(hasPending && currentPeriodDue && compareIsoDates(todayIsoLocal(), currentPeriodDue) >= 0);
      const monthsAhead = n + 1 + (pendingAdvancesSchedule ? 1 : 0);
      const next = addMonthsToIsoDate(anchor, monthsAhead);
      if (!next) return { label: "", pendingShift: false };
      return { label: formatLoanDate(next), pendingShift: pendingAdvancesSchedule };
  });

  const nextPaymentDueLabel = computed(() => nextPaymentDueMeta.value.label);

  const nextPaymentDueCaption = computed(() => {
      if (!nextPaymentDueLabel.value) return "";
      const parts = [
          "Same calendar day each month from your approval date.",
          nextPaymentDueMeta.value.pendingShift
              ? "You have a payment awaiting confirmation; the next due is shown based on today’s date."
              : "After each payment is confirmed, the next due moves forward one month.",
      ];
      return parts.join(" ");
  });

  onMounted(load);

  onBeforeUnmount(revokePayslipUrl);

  function revokePayslipUrl() {
      if (payslipObjectUrl.value) {
          URL.revokeObjectURL(payslipObjectUrl.value);
          payslipObjectUrl.value = null;
      }
  }

  async function loadPayslipPreview() {
      revokePayslipUrl();
      if (!detail.value?.has_payslip) return;
      const base = `${config.public.apiBase}/api`;
      try {
          const res = await fetch(`${base}/client/loans/${route.params.id}/payslip`, {
              headers: {
                  Accept: "image/*,*/*",
                  Authorization: `Bearer ${auth.token}`,
              },
          });
          if (!res.ok) return;
          const blob = await res.blob();
          payslipObjectUrl.value = URL.createObjectURL(blob);
      } catch {
          /* ignore preview errors */
      }
  }

  function openPayslipPreview() {
      if (!payslipObjectUrl.value) return;
      payslipDialog.value = true;
  }

  async function load() {
      loading.value = true;
      err.value = "";
      revokePayslipUrl();
      try {
          const res = await api(`/client/loans/${route.params.id}`);
          detail.value = res.data;
          await loadPayslipPreview();
      } catch (e) {
          err.value = e?.data?.message || "Could not load loan.";
      } finally {
          loading.value = false;
      }
  }

  function fileToBase64(file) {
      return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = () => resolve(reader.result);
          reader.onerror = reject;
          reader.readAsDataURL(file);
      });
  }

  async function submitPay() {
      payErr.value = "";
      payLoading.value = true;
      try {
          const body = { amount: payAmount.value };
          const f = receiptFiles.value;
          const file = Array.isArray(f) ? f[0] : f;
          if (file) {
              body.receipt_base64 = await fileToBase64(file);
          }
          await api(`/client/loans/${route.params.id}/payments`, { method: "POST", body });
          receiptFiles.value = null;
          await load();
      } catch (e) {
          payErr.value = e?.data?.message || "Payment failed.";
      } finally {
          payLoading.value = false;
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