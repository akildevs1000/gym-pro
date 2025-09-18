<template>
  <div>
    <style scoped>
      .v-card {
        border-radius: 5px;
        transition: box-shadow 0.3s ease;
      }
      .v-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      }
    </style>

    <v-row dense>
      <v-col
        :cols="isMobile ? 12 : undefined"
        v-for="card in stats"
        :key="card.title"
      >
        <v-card
          elevation="3"
          class="pa-4 d-flex align-center background"
          :style="`border-left: 6px solid ${card.color} !important`"
        >
          <v-icon size="40" class="me-4" :color="card.color">{{
            card.icon
          }}</v-icon>
          <div>
            <div class="sub-title">{{ card.title }}</div>

            <!-- this is too small -->
            <small class="text-subtitle">
              {{ card.subtitle }}
            </small>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  props: ["filter"],
  data: () => ({
    endpoint: "payments",
    stats: [
      {
        title: "Cash",
        subtitle: "loading...",
        icon: "mdi-cash",
        color: "#4CAF50", // green
      },
      {
        title: "Card",
        subtitle: "loading...",
        icon: "mdi-credit-card",
        color: "#2196F3", // blue
      },
      {
        title: "Cheque",
        subtitle: "loading...",
        icon: "mdi-bank",
        color: "#FFC107", // amber
      },
      {
        title: "Online",
        subtitle: "loading...",
        icon: "mdi-laptop",
        color: "#00ACC1", // cyan
      },
      {
        title: "Bank Tranfser",
        subtitle: "loading...",
        icon: "mdi-bank-transfer",
        color: "#8D6E63", // brown
      },
      {
        title: "City Ledger",
        subtitle: "loading...",
        icon: "mdi-cash-minus",
        color: "#EF5350", // red
      },
      {
        title: "Total",
        subtitle: "loading...",
        icon: "mdi-chart-pie",
        color: "#7E57C2", // purple
      },
    ],
  }),
  computed: {
    filterKey() {
      return JSON.stringify(this.filter);
    },
    isMobile() {
      return this.$vuetify.breakpoint.smAndDown;
    },
  },
  async created() {
    await this.getStats();
  },

  watch: {
    filterKey() {
      this.getStats();
    },
  },

  methods: {
    async getStats() {
      let { data } = await this.$axios.get(`payments-summary-by-payment-mode`, {
        params: {
          ...this.filter,
        },
      });

      this.stats = [
        {
          title: "Cash",
          subtitle: data["Cash"],
          icon: "mdi-cash",
          color: "#4CAF50", // green
        },
        {
          title: "Card",
          subtitle: data["Card"],
          icon: "mdi-credit-card",
          color: "#2196F3", // blue
        },
        {
          title: "Cheque",
          subtitle: data["Cheque"],
          icon: "mdi-bank",
          color: "#FFC107", // amber
        },
        {
          title: "Online",
          subtitle: data["Cheque"],
          icon: "mdi-laptop",
          color: "#00ACC1", // cyan
        },
        {
          title: "Bank Tranfser",
          subtitle: data["Bank Tranfser"],
          icon: "mdi-bank-transfer",
          color: "#8D6E63", // brown
        },
        {
          title: "City Ledger",
          subtitle: data["City Ledger"],
          icon: "mdi-cash-minus",
          color: "#EF5350", // red
        },
        {
          title: "Total",
          subtitle: data["Total"],
          icon: "mdi-chart-pie",
          color: "#7E57C2", // purple
        },
      ];
    },
  },
};
</script>
