<template>
  <span>
    <v-skeleton-loader v-if="loading" type="image" height="250" />
    <apexchart
      v-else
      type="area"
      height="250"
      :options="chartOptions"
      :series="series"
    />
  </span>
</template>

<script>
export default {
  name: "PeakHoursChart",
  data() {
    return {
      loading: true,
      series: [{ name: "Visitors", data: [] }],
      chartOptions: {
  chart: {
    toolbar: { show: false },
    zoom: { enabled: false },
    background: this.$vuetify.theme.dark ? "#2c3e50" : "#f9f9f9", // 🔥 set bg here
  },
  stroke: { curve: "smooth", width: 2, colors: ["#BA68C8"] },
  fill: {
    type: "gradient",
    gradient: {
      shade: "light",
      type: "vertical",
      shadeIntensity: 0.5,
      gradientToColors: ["#9c27b0"],
      inverseColors: false,
      opacityFrom: 0.7,
      opacityTo: 0.1,
      stops: [0, 90, 100],
    },
  },
  grid: {
    show: true,
    strokeDashArray: 4,
    padding: { right: 10, left: 10 },
  },
  dataLabels: { enabled: false },
  markers: { size: 0 },
  yaxis: {
    min: 0,
    max: 60,
    tickAmount: 4,
    labels: {
      formatter: (v) => v,
      style: { colors: "#9E9E9E", fontSize: "11px" },
    },
  },
  xaxis: {
    categories: [],
    labels: {
      rotate: -45,
      style: { colors: "#9E9E9E", fontSize: "10px" },
    },
    axisBorder: { show: true },
    axisTicks: { show: true },
  },
  tooltip: {
    y: { formatter: (v) => `${v} visitors` },
  },
  theme: {
    mode: this.$vuetify.theme.dark ? "dark" : "light",
  },
}

    };
  },
  created() {
    this.$axios
      .get("/analytics/peak-hours") // adjust baseURL if needed
      .then(({ data }) => {
        this.series[0].data = data.values; // [2,1,0,1,…]
        this.chartOptions.xaxis.categories = data.labels; // ["10:00", "11:00", …]
      })
      .finally(() => (this.loading = false))
      .catch((e) => console.error("PeakHours API error:", e));
  },
};
</script>
