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
        title: "Total",
        subtitle: "loading...",
        icon: "mdi-cash-multiple",
        color: "#4CAF50", // green
      },
      {
        title: "Pending Balance",
        subtitle: "loading...",
        icon: "mdi-cash-clock",
        color: "#2196F3", // blue
      },
    //   {
    //     title: "Profit / Loss",
    //     subtitle: "loading...",
    //     icon: "mdi-chart-line",
    //     color: "#FFC107", // amber
    //   },
      
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
      let { data } = await this.$axios.get(`invoices-counts`, {
        params: {
          ...this.filter,
        },
      });

      this.stats = data;
    },
  },
};
</script>
