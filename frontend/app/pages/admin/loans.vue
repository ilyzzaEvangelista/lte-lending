<template>
  <div>
      <div class="d-flex flex-wrap align-end justify-space-between gap-4 mb-6">
          <h1 class="text-h4 font-weight-bold mb-0">Loan applications</h1>
          <v-text-field
              v-model="search"
              class="loans-search"
              density="comfortable"
              variant="outlined"
              label="Search"
              placeholder="Reference, client, email, status…"
              prepend-inner-icon="mdi-magnify"
              hide-details
              clearable
              single-line
          />
      </div>
      <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
      <v-skeleton-loader v-if="loading" type="table-heading, table-thead, table-tbody, table-tfoot" />
      <v-card v-else-if="!loans.length" border>
          <v-card-text>No loan applications yet.</v-card-text>
      </v-card>
      <v-card v-else class="elevation-1" rounded="lg">
          <v-data-table
              v-model:expanded="expanded"
              :headers="headers"
              :items="pagedLoans"
              item-value="id"
              show-expand
              class="overflow-x-auto"
              density="compact"
              hide-default-footer
              :no-data-text="searchTrimmed ? 'No applications match your search.' : 'No data.'"
          >
              <template #item.transaction_no="{ item }">
                  <code class="text-caption">{{ item.transaction_no }}</code>
              </template>
              <template #item.customer="{ item }">
                  <div class="text-body-2">{{ customerName(item) }}</div>
                  <div class="text-caption text-medium-emphasis">{{ item.customer?.email }}</div>
              </template>
              <template #item.amount="{ item }">{{ formatMoney(item.amount) ?? 'N/A' }}</template>
              <template #item.tenure="{ item }">{{ item.tenure ?? 'N/A' }} mo</template>
              <template #item.monthly="{ item }"> {{ formatMoney(item.monthly) ?? 'N/A' }} </template>
              <template #item.interest="{ item }">{{ item.interest ?? 'N/A' }}%</template>
              <template #item.purpose="{ item }">{{ item.purpose ?? 'N/A' }}</template>
              <template #item.status="{ item }">
                  <v-chip class="text-capitalize" :color="statusColor(item.status)" size="small" variant="flat"
                      >{{ item.status }}</v-chip
                  >
              </template>
              <template #item.payslip_preview="{ item }">
                  <div v-if="item._payslipObjectUrl" class="d-flex align-center ga-2">
                      <v-icon
                          icon="mdi-eye"
                          size="small"
                          color="primary"
                          class="cursor-pointer"
                          @click="openPayslipPreview(item)"
                      />
                  </div>
                  <span v-else-if="item.has_payslip" class="text-caption text-medium-emphasis">Loading…</span>
                  <span v-else class="text-medium-emphasis">—</span>
              </template>
              <template #expanded-row="{ columns, item }">
                  <tr>
                      <td :colspan="columns.length" class="admin-loans-expanded py-4 px-4">
                          <v-row dense align="end">
                              <v-col cols="12" sm="3">
                                  <v-select
                                      v-model="item._edit.status"
                                      :items="statusItems"
                                      item-title="title"
                                      item-value="value"
                                      label="Status"
                                      variant="outlined"
                                      density="compact"
                                      hide-details
                                  />
                              </v-col>
                              <v-col cols="12" sm="3">
                                  <v-text-field
                                      v-model.number="item._edit.interest"
                                      label="Interest %"
                                      type="number"
                                      variant="outlined"
                                      density="compact"
                                      hide-details
                                      min="0"
                                      max="100"
                                      step="0.01"
                                  />
                              </v-col>
                              <v-col cols="12" sm="4">
                                  <v-text-field
                                      v-model="item._edit.admin_note"
                                      label="Note"
                                      variant="outlined"
                                      density="compact"
                                      hide-details
                                  />
                              </v-col>
                              <v-col cols="12" sm="2">
                                  <v-btn
                                      color="primary"
                                      block
                                      class="text-none"
                                      :loading="item._saving"
                                      @click="save(item)"
                                  >
                                      Save
                                  </v-btn>
                              </v-col>
                          </v-row>
                          <v-alert v-if="item._msg" type="error" variant="tonal" density="compact" class="mt-3"
                              >{{ item._msg }}</v-alert
                          >
                      </td>
                  </tr>
              </template>
          </v-data-table>

          <v-pagination v-model="page" :length="totalPages" class="mt-4" rounded="circle" />
      </v-card>

      <v-dialog v-model="payslipDialog" max-width="920">
          <v-card v-if="payslipDialogItem">
              <v-card-title class="d-flex align-center justify-space-between text-wrap pr-2">
                  <span>Payslip · {{ payslipDialogItem.transaction_no }}</span>
                  <v-btn icon="mdi-close" variant="text" aria-label="Close" @click="payslipDialog = false" />
              </v-card-title>
              <v-divider />
              <v-card-text class="pa-4">
                  <v-img
                      v-if="payslipDialogItem._payslipObjectUrl"
                      :src="payslipDialogItem._payslipObjectUrl"
                      max-height="75vh"
                      contain
                      class="rounded"
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
  const loans = ref([]);
  const loading = ref(true);
  const err = ref("");
  const expanded = ref([]);
  const payslipDialog = ref(false);
  const payslipDialogItem = ref(null);
  const page = ref(1);
  const pageSize = 10;
  const search = ref("");

  const searchTrimmed = computed(() => search.value.trim());

  const statusItems = [
      { title: "Pending", value: "pending" },
      { title: "Approved", value: "approved" },
      { title: "Rejected", value: "rejected" },
      { title: "Active", value: "active" },
      { title: "Closed", value: "closed" },
  ];

  const headers = [
      { title: "Reference", key: "transaction_no", sortable: true, minWidth: "140" },
      { title: "Client", key: "customer", sortable: false, minWidth: "180" },
      { title: "Amount", key: "amount", sortable: true },
      { title: "Tenure", key: "tenure", sortable: true },
      { title: "Monthly", key: "monthly", sortable: false },
      { title: "Interest", key: "interest", sortable: true },
      { title: "Purpose", key: "purpose", sortable: false, minWidth: "120" },
      { title: "Status", key: "status", sortable: true },
      { title: "Payslip", key: "payslip_preview", sortable: false, width: "140" },
  ];

  const filteredLoans = computed(() => {
      const q = searchTrimmed.value.toLowerCase();
      if (!q) return loans.value;
      return loans.value.filter((loan) => haystack(loan).includes(q));
  });

  const totalPages = computed(() => Math.max(1, Math.ceil(filteredLoans.value.length / pageSize)));

  const pagedLoans = computed(() => {
      const list = filteredLoans.value;
      const start = (page.value - 1) * pageSize;
      return list.slice(start, start + pageSize);
  });

  watch(searchTrimmed, () => {
      page.value = 1;
      expanded.value = [];
  });

  watch(totalPages, (n) => {
      if (page.value > n) page.value = n;
  });

  onMounted(load);
  onBeforeUnmount(revokeAllPayslipUrls);

  function haystack(loan) {
      const c = loan.customer;
      const name = [c?.first_name, c?.last_name].filter(Boolean).join(" ");
      const parts = [
          loan.transaction_no,
          name,
          loan.first_name,
          loan.last_name,
          loan.phone,
          loan.city,
          loan.country,
          loan.profession,
          loan.loan_type,
          c?.username,
          c?.email,
          loan.status,
          loan.purpose,
          loan.admin_note,
          loan.amount != null ? String(loan.amount) : "",
          loan.tenure != null ? String(loan.tenure) : "",
          loan.interest != null ? String(loan.interest) : "",
      ];
      return parts.filter(Boolean).join(" ").toLowerCase();
  }

  function customerName(loan) {
      const c = loan.customer;
      if (!c) return "";
      return [c.first_name, c.last_name].filter(Boolean).join(" ") || c.username || "—";
  }

  function revokeAllPayslipUrls() {
      for (const loan of loans.value) {
          if (loan._payslipObjectUrl) {
              URL.revokeObjectURL(loan._payslipObjectUrl);
              loan._payslipObjectUrl = undefined;
          }
      }
  }

  async function loadPayslipPreviews(list) {
      const base = `${config.public.apiBase}/api`;
      await Promise.all(
          list.map(async (loan) => {
              if (!loan.has_payslip) return;
              try {
                  const res = await fetch(`${base}/admin/loans/${loan.id}/payslip`, {
                      headers: {
                          Accept: "image/*,*/*",
                          Authorization: `Bearer ${auth.token}`,
                      },
                  });
                  if (!res.ok) return;
                  const blob = await res.blob();
                  if (loan._payslipObjectUrl) URL.revokeObjectURL(loan._payslipObjectUrl);
                  loan._payslipObjectUrl = URL.createObjectURL(blob);
              } catch {
                  /* ignore preview errors */
              }
          })
      );
  }

  async function load() {
      loading.value = true;
      err.value = "";
      revokeAllPayslipUrls();
      expanded.value = [];
      search.value = "";
      page.value = 1;
      try {
          const res = await api("/admin/loans");
          loans.value = res.data.map((row) => ({
              ...row,
              _edit: {
                  status: row.status,
                  interest: row.interest != null ? Number(row.interest) : 0,
                  admin_note: row.admin_note ?? "",
              },
              _saving: false,
              _msg: "",
              _payslipObjectUrl: undefined,
          }));
          await loadPayslipPreviews(loans.value);
      } catch (e) {
          err.value = e?.data?.message || "Could not load loans.";
      } finally {
          loading.value = false;
      }
  }

  function openPayslipPreview(item) {
      payslipDialogItem.value = item;
      payslipDialog.value = true;
  }

  async function save(loan) {
      loan._msg = "";
      loan._saving = true;
      try {
          const res = await api(`/admin/loans/${loan.id}`, {
              method: "PATCH",
              body: {
                  status: loan._edit.status,
                  interest: loan._edit.interest,
                  admin_note: loan._edit.admin_note || null,
              },
          });
          const u = res.data;
          Object.assign(loan, u);
          loan._edit.status = u.status;
          loan._edit.interest = u.interest != null ? Number(u.interest) : 0;
          loan._edit.admin_note = u.admin_note ?? "";
      } catch (e) {
          loan._msg = e?.data?.message || "Update failed.";
      } finally {
          loan._saving = false;
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
  .admin-loans-expanded {
      background-color: rgb(var(--v-theme-surface));
      border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .loans-search {
      flex: 1 1 280px;
      max-width: 400px;
      min-width: 200px;
  }
</style>