<template>
  <span>
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
        v-for="card in cards"
        :key="card.title"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <!-- Left‑border color comes from card.color -->
        <v-card
          elevation="6"
          class="pa-4 d-flex align-center background"
          :style="`border-left: 6px solid ${card.color} !important`"
        >
          <v-icon size="40" class="me-4" :color="card.color">{{
            card.icon
          }}</v-icon>
          <div>
            <h3 class="text-h6">{{ card.title }}</h3>

            <!-- this is too small -->
            <small class="text-subtitle">
              {{ card.subtitle }}
            </small>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </span>
</template>

<script>
export default {
  data: () => ({
    cards: [],
  }),
  async created() {
    await this.getStats();
  },
  methods: {
    async getStats() {
      let { data } = await this.$axios.get(`dashboard-counts`);

      this.cards = data;
    },
  },
};
</script>
