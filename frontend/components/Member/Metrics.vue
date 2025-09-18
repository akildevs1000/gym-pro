<template>
  <v-dialog persistent v-model="viewDialog" width="800px" :key="memberId">
    <WidgetsClose left="790" @click="viewDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon color="text" small>mdi-chart-bar</v-icon>
        Member Metrics
      </span>
    </template>

    <v-card flat class="background">
      <div dense flat dark class="white--text primary pa-2">Member Summary</div>
      <v-container>
        <v-row v-if="memberObject" dense>
          <v-col cols="12"> </v-col>

          <!-- Metrics Cards -->
          <v-col
            cols="12"
            sm="6"
            md="3"
            v-for="metric in metrics"
            :key="metric.label"
          >
            <v-card class="pa-4 background" outlined>
              <div class="text-subtitle-1">{{ metric.label }}</div>
              <div>{{ metric.value }}</div>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ["memberId", "memberObject"],
  data: () => ({
    viewDialog: false,
    metrics: [],
    defaultMember: {
      id: 1,
      name: "John Doe",
      plans: [
        {
          plan_name: "Monthly",
          start_date: "2025-07-01",
          end_date: "2025-07-31",
          price: 100,
        },
        {
          plan_name: "Monthly",
          start_date: "2025-08-01",
          end_date: "2025-08-31",
          price: 100,
        },
      ],
      payments: [
        {
          paid_amount: 100,
          description: "Monthly Fee",
          mode: { name: "Cash" },
          payment_mode_ref: "CASH001",
          datetime: "2025-07-01",
        },
        {
          paid_amount: 100,
          description: "Monthly Fee",
          mode: { name: "Cash" },
          payment_mode_ref: "CASH002",
          datetime: "2025-08-01",
        },
      ],
    },
  }),
  created() {
    const member = this.memberObject || this.defaultMember;
    const memberShipPayments = member.payments_sum_paid_amount || 0;
    const purshasesPayments = member.invoices_sum_paid_amount || 0;

    const memberShipBalance = member.payments_sum_balance || 0;
    const purshasesBalance = member.invoices_sum_balance || 0;

    const totalPaid = memberShipPayments + purshasesPayments;
    const totalBalance = memberShipBalance + purshasesBalance;


    const expiry = member?.last_membership?.show_plan_end_date ?? "---";
    const { paid_amount, datetime } = member?.last_payment ?? "---";

    // Total renewals
    const totalRenewals = Math.floor(Math.random() * 10) + 1;

    // Status: Active / Expired / Inactive
    let status = member?.last_membership?.status == "1" ? "Active" : "Expired";
    let plan_name = member?.last_membership?.plan?.name || "---";

    this.metrics = [
      { label: "Current Plan", value: plan_name },
      { label: "Status", value: status },
      { label: "Next Expiry", value: expiry },

      { label: "Total Renewals", value: 0 },
      { label: "Total Paid", value: totalPaid },
      { label: "Balance", value: totalBalance },

      { label: "Last Paid Amount", value: `${paid_amount}` },
      { label: "Last Payment Date", value: `${datetime}` },
    ];

    this.memberObject = member;
  },
};
</script>
