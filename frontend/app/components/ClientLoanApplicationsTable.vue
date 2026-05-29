<template>
    <div class="ma-0 pa-0">
        <v-progress-linear v-if="loading" indeterminate class="mb-4" color="primary" />
        <v-alert v-if="err" type="error" variant="tonal" class="mb-4">{{ err }}</v-alert>
        <div class="d-flex align-center justify-end mb-4">
            <v-text-field
                v-model="search"
                variant="outlined"
                label="Search"
                placeholder="Reference, status, amount…"
                prepend-inner-icon="mdi-magnify"
                hide-details
                clearable
                single-line
                class="client-loans-search"
            />
        </div>
        <v-card v-if="!loading && !loans.length" flat class="elevation-1" rounded="lg">
            <v-card-text class="text-medium-emphasis">{{ emptyMessage }}</v-card-text>
        </v-card>
        <v-card v-else-if="!loading" class="elevation-1" rounded="lg">
            <v-data-table
                density="compact"
                :headers="headers"
                :items="pagedLoans"
                item-value="id"
                :loading="loading"
                items-per-page="-1"
                class="overflow-x-auto"
                hide-default-footer
                :no-data-text="searchTrimmed ? 'No applications match your search.' : 'No data.'"
            >
                <template #item.transaction_no="{ item }">
                    <code class="text-caption">{{ item.transaction_no }}</code>
                </template>
                <template #item.amount="{ item }">{{ formatMoney(item.amount) }}</template>
                <template #item.monthly="{ item }">
                    {{ item.monthly != null ? formatMoney(item.monthly) : 'N/A' }}
                </template>
                <template #item.interest="{ item }">{{ item.interest }}%</template>
                <template #item.status="{ item }">
                    <v-chip class="text-capitalize" size="small" :color="statusColor(item.status)" variant="flat"
                        >{{ item.status }}</v-chip
                    >
                </template>
                <template #item.actions="{ item }">
                    <v-btn
                        :to="`/client/loans/${item.id}`"
                        size="small"
                        variant="text"
                        color="primary"
                        prepend-icon="mdi-eye"
                    >
                        View
                    </v-btn>
                </template>
            </v-data-table>
            <v-pagination v-model="page" :length="totalPages" class="mt-4 mb-4 px-4" rounded="circle" />
        </v-card>
    </div>
</template>

<script setup>
    import { useApi } from "~~/composables/useApi";
    import { formatMoney } from "~~/composables/formatMoney";

    defineProps({
        emptyMessage: {
            type: String,
            default: "You have no applications yet.",
        },
    });

    const { api } = useApi();
    const loans = ref([]);
    const loading = ref(true);
    const err = ref("");
    const search = ref("");
    const page = ref(1);
    const pageSize = 10;

    const searchTrimmed = computed(() => search.value.trim().toLowerCase());

    const filteredLoans = computed(() => {
        const q = searchTrimmed.value;
        if (!q) return loans.value;
        return loans.value.filter((row) => loanHaystack(row).includes(q));
    });

    const totalPages = computed(() => Math.max(1, Math.ceil(filteredLoans.value.length / pageSize)));

    const pagedLoans = computed(() => {
        const list = filteredLoans.value;
        const start = (page.value - 1) * pageSize;
        return list.slice(start, start + pageSize);
    });

    watch(searchTrimmed, () => {
        page.value = 1;
    });

    watch(totalPages, (n) => {
        if (page.value > n) page.value = n;
    });

    function loanHaystack(row) {
        const parts = [
            row.transaction_no,
            row.status,
            row.purpose,
            row.amount != null ? String(row.amount) : "",
            row.tenure != null ? String(row.tenure) : "",
            row.monthly != null ? String(row.monthly) : "",
            row.interest != null ? String(row.interest) : "",
        ];
        return parts.filter(Boolean).join(" ").toLowerCase();
    }

    const headers = [
        { title: "Reference", key: "transaction_no", sortable: true },
        { title: "Amount", key: "amount", sortable: true },
        { title: "Tenure (mo)", key: "tenure", sortable: true },
        { title: "Monthly", key: "monthly" },
        { title: "Status", key: "status" },
        { title: "Interest", key: "interest" },
        { title: "Actions", key: "actions", sortable: false },
    ];

    onMounted(load);

    async function load() {
        loading.value = true;
        err.value = "";
        page.value = 1;
        try {
            const res = await api("/client/loans");
            loans.value = res.data;
        } catch (e) {
            err.value = e?.data?.message || "Could not load loans.";
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
    .client-loans-search {
        max-width: 420px;
    }
</style>