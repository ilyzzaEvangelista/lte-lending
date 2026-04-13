<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-2">Admin overview</h1>
    <p class="text-body-1 text-medium-emphasis mb-6">
      Schema: <code>admins</code>, <code>customers</code>, <code>loan_details</code>, <code>loan_records</code>,
      <code>loan_payments</code>, <code>activity_logs</code>.
    </p>
    <v-row>
      <v-col cols="12" md="6">
        <v-card border>
          <v-card-title>Demo credentials</v-card-title>
          <v-card-text>
            <p class="mb-2">
              <strong>Admin</strong> (Staff tab): username <code>admin</code> / <code>password</code>
            </p>
            <p class="mb-0">
              <strong>Customer</strong>: <code>client@example.com</code> / <code>password</code> (username
              <code>democlient</code>)
            </p>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card border>
          <v-card-title>Quick links</v-card-title>
          <v-card-actions class="flex-wrap ga-2">
            <v-btn to="/admin/loans" color="primary">Loan applications</v-btn>
            <v-btn to="/admin/payments" variant="outlined">Payments</v-btn>
            <v-btn to="/admin/logs" variant="text">Activity logs</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin',
  middleware: 'admin',
})
</script>
