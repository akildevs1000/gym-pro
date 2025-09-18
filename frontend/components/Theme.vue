<template>
  <v-container>
    <v-data-table class="secondary text--primary" />
  </v-container>
</template>
<script>
export default {
  data: () => ({
    data: [],
  }),
  custom_options: {},

  watch: {
    dialog(val) {
      val || this.close();
      this.errors = [];
      this.search = "";
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    toggleDark() {
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark;
      localStorage.setItem("themeDark", this.$vuetify.theme.dark);
    },
  },
  mounted() {
    this.$vuetify.theme.dark = localStorage.getItem("themeDark") === "true";
  },
};
</script>
