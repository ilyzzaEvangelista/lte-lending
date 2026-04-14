<template>
  <div>
      <div class="d-flex flex-wrap align-end justify-space-between gap-4 mb-6">
          <h1 class="text-h4 font-weight-bold mb-0">Loan payments</h1>
          <v-text-field
              v-if="!loading && payments.length"
              v-model="search"
              class="admin-payments-search"
              density="comfortable"
              variant="outlined"
              label="Search"
              placeholder="Customer, email, transaction…"
              prepend-inner-icon="mdi-magnify"
              hide-details
              clearable
              single-line
          />
      </div>
      <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
      <v-skeleton-loader v-if="loading" type="table-heading, table-thead, table-tbody" />
      <v-card v-else-if="!payments.length" border>
          <v-card-text>No payments yet.</v-card-text>
      </v-card>
      <v-card v-else border class="overflow-x-auto">
          <v-data-table
              :headers="headers"
              :items="pagedPayments"
              item-value="id"
              density="compact"
              hide-default-footer
              items-per-page="-1"
              class="payments-table"
              :no-data-text="searchTrimmed ? 'No payments match your search.' : 'No data.'"
          >
              <template #item.full_name="{ item }">
                  <span class="text-body-2">{{ item.full_name ?? 'N/A' }}</span>
              </template>
              <template #item.transaction_no="{ item }">
                  <code class="text-caption">{{ item.transaction_no }}</code>
              </template>
              <template #item.customer_email="{ item }">
                  <span class="text-body-2">{{ item.customer_email ?? 'N/A' }}</span>
              </template>
              <template #item.amount="{ item }">{{ formatMoney(item.amount) }}</template>
              <template #item.balance="{ item }">
                  {{ item.balance != null ? formatMoney(item.balance) : 'N/A' }}
              </template>
              <template #item.receipt_preview="{ item }">
                  <div v-if="item._receiptObjectUrl" class="d-flex align-center ga-2 py-1">
                      <v-btn
                          prepend-icon="mdi-eye"
                          size="small"
                          color="primary"
                          class="cursor-pointer"
                          aria-label="Enlarge receipt"
                          @click="openReceiptPreview(item)"
                          variant="text"
                      >
                          View
                      </v-btn>
                  </div>
              </template>
              <template #item.status="{ item }">
                  <v-chip class="text-capitalize" size="small" variant="flat" :color="statusColor(item.status)"
                      >{{ item.status }}</v-chip
                  >
              </template>
              <template #item.actions="{ item }">
                  <div v-if="item.status === 'pending'" class="d-flex flex-wrap ga-1 py-1">
                      <v-btn
                          color="primary"
                          size="small"
                          class="text-none"
                          :loading="item._busy"
                          @click="setStatus(item, 'confirmed')"
                      >
                          Confirm
                      </v-btn>
                      <v-btn
                          variant="outlined"
                          size="small"
                          class="text-none"
                          :loading="item._busy"
                          @click="setStatus(item, 'rejected')"
                      >
                          Reject
                      </v-btn>
                  </div>
                  <span v-else class="text-medium-emphasis">N/A</span>
                  <v-alert v-if="item._msg" type="error" variant="tonal" density="compact" class="mt-2 mb-0"
                      >{{ item._msg }}</v-alert
                  >
              </template>
          </v-data-table>
          <v-pagination
              v-model="page"
              :length="totalPages"
              class="mt-4 mb-4 px-4"
              rounded="circle"
          />
      </v-card>

      <v-dialog v-model="receiptDialog" max-width="920">
          <v-card v-if="receiptDialogItem">
              <v-card-title class="d-flex align-center justify-space-between text-wrap pr-2">
                  <span>Receipt · {{ receiptDialogItem.transaction_no }}</span>
                  <v-btn icon="mdi-close" variant="text" aria-label="Close" @click="receiptDialog = false" />
              </v-card-title>
              <v-divider />
              <v-card-text class="pa-4">
                  <v-img
                      v-if="receiptDialogItem._receiptObjectUrl"
                      :src="receiptDialogItem._receiptObjectUrl"
                      max-height="75vh"
                      contain
                      class="rounded"
                      alt="Receipt"
                  />
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
      layout: "admin",
      middleware: "admin",
  });

  const config = useRuntimeConfig();
  const auth = useAuthStore();
  const { api } = useApi();
  const payments = ref([]);
  const loading = ref(true);
  const err = ref("");
  const receiptDialog = ref(false);
  const receiptDialogItem = ref(null);
  const search = ref("");
  const page = ref(1);
  const pageSize = 10;

  const searchTrimmed = computed(() => search.value.trim().toLowerCase());

  const filteredPayments = computed(() => {
      const q = searchTrimmed.value;
      if (!q) return payments.value;
      return payments.value.filter((p) => paymentHaystack(p).includes(q));
  });

  const totalPages = computed(() => Math.max(1, Math.ceil(filteredPayments.value.length / pageSize)));

  const pagedPayments = computed(() => {
      const list = filteredPayments.value;
      const start = (page.value - 1) * pageSize;
      return list.slice(start, start + pageSize);
  });

  watch(searchTrimmed, () => {
      page.value = 1;
  });

  watch(totalPages, (n) => {
      if (page.value > n) page.value = n;
  });

  function paymentHaystack(p) {
      const parts = [
          p.full_name,
          p.transaction_no,
          p.customer_email,
          p.status,
          p.amount != null ? String(p.amount) : "",
          p.balance != null ? String(p.balance) : "",
          p.loan_detail?.transaction_no,
          p.loan_detail?.status,
      ];
      return parts.filter(Boolean).join(" ").toLowerCase();
  }

  const headers = [
      { title: "Customer", key: "full_name", sortable: true, minWidth: "120" },
      { title: "Transaction", key: "transaction_no", sortable: true, minWidth: "130" },
      { title: "Email", key: "customer_email", sortable: true, minWidth: "160" },
      { title: "Amount", key: "amount", sortable: true },
      { title: "Balance after", key: "balance", sortable: true },
      { title: "Receipt", key: "receipt_preview", align: "start", sortable: false, width: "120" },
      { title: "Status", key: "status", sortable: true, width: "110" },
      { title: "Actions", key: "actions", align: "center", sortable: false, minWidth: "200" },
  ];

  onMounted(load);
  onBeforeUnmount(revokeAllReceiptUrls);

  function statusColor(status) {
      const map = {
          pending: "warning",
          confirmed: "success",
          rejected: "error",
      };
      return map[status] || "default";
  }

  function revokeAllReceiptUrls() {
      for (const p of payments.value) {
          if (p._receiptObjectUrl) {
              URL.revokeObjectURL(p._receiptObjectUrl);
              p._receiptObjectUrl = undefined;
          }
      }
  }

  async function loadReceiptPreviews(list) {
      const base = `${config.public.apiBase}/api`;
      await Promise.all(
          list.map(async (p) => {
              if (!p.has_receipt) return;
              try {
                  const res = await fetch(`${base}/admin/payments/${p.id}/receipt`, {
                      headers: {
                          Accept: "image/*,*/*",
                          Authorization: `Bearer ${auth.token}`,
                      },
                  });
                  if (!res.ok) return;
                  const blob = await res.blob();
                  if (p._receiptObjectUrl) URL.revokeObjectURL(p._receiptObjectUrl);
                  p._receiptObjectUrl = URL.createObjectURL(blob);
              } catch {
                  /* ignore preview errors */
              }
          })
      );
  }

  function openReceiptPreview(item) {
      if (!item._receiptObjectUrl) return;
      receiptDialogItem.value = item;
      receiptDialog.value = true;
  }

  async function load() {
      loading.value = true;
      err.value = "";
      page.value = 1;
      revokeAllReceiptUrls();
      try {
          const res = await api("/admin/payments");
          payments.value = res.data.map((row) => ({
              ...row,
              customer_email: row.customer?.email ?? "—",
              _busy: false,
              _msg: "",
              _receiptObjectUrl: undefined,
          }));
          await loadReceiptPreviews(payments.value);
      } catch (e) {
          err.value = e?.data?.message || "Could not load payments.";
      } finally {
          loading.value = false;
      }
  }

  async function setStatus(p, status) {
      p._msg = "";
      p._busy = true;
      try {
          const res = await api(`/admin/payments/${p.id}`, {
              method: "PATCH",
              body: { status },
          });
          Object.assign(p, {
              ...res.data,
              customer_email: res.data.customer?.email ?? p.customer_email,
              _busy: false,
              _msg: "",
          });
      } catch (e) {
          p._msg = e?.data?.message || "Update failed.";
      } finally {
          p._busy = false;
      }
  }
</script>

<style scoped>
.admin-payments-search {
  flex: 1 1 280px;
  max-width: 400px;
  min-width: 200px;
}
</style>