<template>
  <v-container fluid>
    <div>
      <div class="text-center ma-2">
        <v-snackbar
          v-model="snackbar"
          top="top"
          color="secondary"
          elevation="24"
        >
          {{ response }}
        </v-snackbar>
      </div>
    </div>

    <div class="mb-5">
      <PaymentCards :filter="filter" :key="JSON.stringify(filter)" />
    </div>

    <v-card class="background">
      <v-card-text>
        <v-data-table
          class="background"
          v-model="ids"
          item-key="id"
          :headers="headers"
          :items="data"
          :loading="loading"
          :options.sync="options"
          :footer-props="{
            itemsPerPageOptions: [50, 100, 500, 1000],
          }"
        >
          <template v-slot:top>
            <v-container fluid>
              <v-row dense>
                <v-col>
                  <span> Payment</span>
                  <v-tooltip top color="primary">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                        dense
                        class="ma-0 px-0"
                        x-small
                        :ripple="false"
                        text
                        v-bind="attrs"
                        v-on="on"
                        @click="getDataFromApi()"
                      >
                        <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
                      </v-btn>
                    </template>
                    <span>Reload</span>
                  </v-tooltip>
                </v-col>

                <!-- <v-col class="text-right">
                  <v-btn
                    dense
                    color="primary"
                    small
                    @click="dispalyNewDialog()"
                    class="white--text"
                  >
                    <v-icon color="white" small> mdi-plus </v-icon>
                    Payment
                  </v-btn>
                </v-col> -->
                <v-col cols="12">
                  <v-row>
                    <v-col md="2" sm="12" cols="12">
                      <v-autocomplete
                        label="Member"
                        :items="[
                          { id: null, full_name: `Select All` },
                          ...members,
                        ]"
                        item-text="full_name"
                        item-value="id"
                        placeholder="Select"
                        v-model="filter.member_id"
                        hide-details
                        dense
                        outlined
                      ></v-autocomplete>
                    </v-col>
                    <v-col md="2" sm="12" cols="12">
                      <v-autocomplete
                        label="Payment Mode"
                        :items="[
                          { id: null, name: `Select All` },
                          ...payment_modes,
                        ]"
                        item-text="name"
                        item-value="id"
                        placeholder="Select"
                        v-model="filter.payment_mode_id"
                        hide-details
                        dense
                        outlined
                      ></v-autocomplete>
                    </v-col>
                    <v-col md="2" sm="12" cols="12">
                      <v-text-field
                        hide-details
                        label="Payment Mode Ref"
                        outlined
                        dense
                        v-model="filter.payment_mode_ref"
                      ></v-text-field>
                    </v-col>
                    <v-col>
                      <div class="mx-1">
                        <CustomFilter
                          @filter-attr="filterAttr"
                          :defaultFilterType="1"
                          height="50px"
                        />
                      </div>
                    </v-col>
                    <v-col>
                      <v-btn
                        :loading="loading"
                        dense
                        color="primary"
                        small
                        @click="getDataFromApi()"
                        class="white--text"
                      >
                        Submit
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-container>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>
<script>
export default {
  data: () => ({
    dateMenu: false,

    expandedIndex: null, // Index of the currently expanded item
    compKey: 1,
    dialogNew: false,
    options: {},
    filter: {},
    Model: "Payments",
    endpoint: "payments",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: true,
    total: 0,

    headers: [
      {
        text: "#",
        align: "left",
        sortable: true,
        key: "id",
        value: "id",
      },
      {
        text: "Member",
        align: "left",
        sortable: true,
        key: "member.full_name",
        value: "member.full_name",
      },
      {
        text: "Mode",
        align: "left",
        sortable: true,
        key: "mode.name",
        value: "mode.name",
      },
      {
        text: "Ref #",
        align: "left",
        sortable: true,
        key: "payment_mode_ref",
        value: "payment_mode_ref",
      },
      {
        text: "Paid Amount",
        align: "left",
        sortable: true,
        key: "paid_amount",
        value: "paid_amount",
      },
      {
        text: "Balance",
        align: "left",
        sortable: true,
        key: "balance",
        value: "balance",
      },
      {
        text: "Total",
        align: "left",
        sortable: true,
        key: "total",
        value: "total",
      },
    ],
    editedIndex: -1,
    editedItem: {},
    defaultItem: {},

    response: "",
    data: [],
    members: [],
    payment_modes: [],
    stats: [],
    errors: [],

    formTitle: "New",
    editPermissionId: "",
  }),

  async created() {
    await this.getMembersList();
    await this.getPaymentModeList();
  },

  watch: {
    editedIndex(val) {
      this.formTitle = val === -1 ? "New" : "Edit";
    },
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
    doCalculation(item) {
      const qty = parseFloat(item.qty) || 0;
      const rate = parseFloat(item.rate) || 0;
      const tax = parseFloat(item.tax) || 0;

      item.total = qty * rate + tax;

      // Recalculate all totals
      let subTotal = 0;
      let totalTax = 0;
      let grandTotal = 0;

      for (const i of this.editedItem.items) {
        const iRate = parseFloat(i.rate) || 0;
        const iTax = parseFloat(i.tax) || 0;
        const iQty = parseFloat(i.qty) || 0;
        const iTotal = iQty * iRate + iTax;

        subTotal += iRate * iQty;
        totalTax += iTax;
        grandTotal += iTotal;
      }

      this.editedItem.sub_total = subTotal;
      this.editedItem.tax = totalTax;
      this.editedItem.total = grandTotal;
    },
    filterAttr(data) {
      this.filter.from_date = data.from;
      this.filter.to_date = data.to;
    },
    addItem() {
      this.editedItem.items.push({
        name: "Your Item",
        qty: 1,
        rate: 1,
        tax: 1,
        total: 1,
      });
    },
    async getMembersList() {
      try {
        let { data } = await this.$axios.get(`/members-list`);
        this.members = data;
      } catch (error) {}
    },
    async getPaymentModeList() {
      try {
        let { data } = await this.$axios.get(`/payment-modes-list`);
        this.payment_modes = data;
      } catch (error) {}
    },
    closeDialog() {
      this.dialogNew = false;
      ++this.compKey;
    },
    dispalyNewDialog() {
      this.errors = [];
      this.editedIndex = -1;
      this.formTitle = "New";
      this.dialogNew = true;
      this.permission_ids = [];
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          ...this.filter,
        },
      };

      this.$axios.get(`${url}?page=${page}`, options).then(({ data }) => {
        this.data = data.data;
        this.total = data.total;
        this.loading = false;

        this.getStats();
      });
    },

    async getStats() {
      let { data } = await this.$axios.get(`payments-summary-by-payment-mode`, {
        params: {
          ...this.filter,
        },
      });

      this.stats = [
        {
          title: "Cash",
          subtitle: data["Cash"] || 0,
          icon: "mdi-cash",
          color: "#4CAF50", // green
        },
        {
          title: "Card",
          subtitle: data["Card"] || 0,
          icon: "mdi-credit-card",
          color: "#2196F3", // blue
        },
        {
          title: "Cheque",
          subtitle: data["Cheque"] || 0,
          icon: "mdi-bank",
          color: "#FFC107", // amber
        },
        {
          title: "Online",
          subtitle: data["Cheque"] || 0,
          icon: "mdi-laptop",
          color: "#00ACC1", // cyan
        },
        {
          title: "Bank Tranfser",
          subtitle: data["Bank Tranfser"] || 0,
          icon: "mdi-bank-transfer",
          color: "#8D6E63", // brown
        },
        {
          title: "City Ledger",
          subtitle: data["City Ledger"] || 0,
          icon: "mdi-led-variant-on",
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

    editItem(item) {
      console.log(item);
      this.errors = [];
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      //this.dialog = true;
      this.dialogNew = true;

      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
        },
      };

      this.$axios
        .get("assign-permission/role-id/" + item.id, options)
        .then(({ data }) => {
          //this.data = data;
          this.loading = false;
          if (data[0]) {
            this.permission_ids = data[0].permission_ids;
            this.editPermissionId = data[0].id;

            //alert(this.editPermissionId);
          }

          // else
          //   this.$router.push("/assign_permission/create");
        });
    },

    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(this.endpoint + "/" + item.id)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
    },

    close() {
      this.dialog = false;
      this.dialogNew = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    save() {
      let payload = {
        ...this.editedItem,
        company_id: this.$auth.user.company.id,
      };
      if (this.editedIndex > -1) {
        this.$axios
          .put(this.endpoint + "/" + this.editedItem.id, payload)
          .then(({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.getDataFromApi();

              this.snackbar = data.status;
              this.response = data.message;
              this.dialogNew = false;
              this.close();
            }
          })
          .catch((err) => console.log(err));
      } else {
        this.$axios
          .post(this.endpoint, payload)
          .then(({ data }) => {
            console.log("🚀 ~ .then ~ data:", data);
            if (!data.status) {
              this.errors = data.errors;
              console.log("🚀 ~ .then ~ data.errors:", data.errors);
            } else {
              this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
              this.close();
              this.errors = [];
              this.search = "";
            }
          })
          .catch((res) => console.log(res));
      }
    },
  },
};
</script>
