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
      <style scoped>
        .summary-table {
          width: 100%;
          border-collapse: collapse;
          font-family: Arial, sans-serif;
        }

        .summary-table td {
          padding: 2px;
        }
        .common-input {
          font-size: 13px;
          color: #9e9e9e;
          border: 1px solid #9e9e9e;
          border-radius: 4px;
          padding: 2px 8px;
          width: 100%;
          outline: none;
          box-sizing: border-box;
          height: 38px;
        }
        .dropdown-input {
          appearance: none;
          background-color: #fff;
          background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 140 140' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolygon points='0,0 140,0 70,70' fill='%239e9e9e'/%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 8px center;
          background-size: 12px;
          cursor: pointer;
          border: 1px solid #9e9e9e;
          border-radius: 4px;
          padding: 2px 8px;
          color: #9e9e9e;
        }

        .number-input {
          text-align: right;
        }

        /* Optional: Add hover/focus effects */
        .common-input:focus {
          border-color: #1976d2; /* Vuetify primary color */
        }
        .dropdown-input:focus {
          border-color: #1976d2 !important;
          outline: none !important;
          box-shadow: 0 0 0 1px #1976d2;
        }
      </style>
      <v-dialog persistent v-model="dialogNewExpense" width="800">
        <WidgetsClose left="790" @click="closeDialog" />

        <v-card class="background">
          <v-alert dense flat dark class="primary">
            {{ formTitle }} {{ Model }}
            <v-spacer></v-spacer>
          </v-alert>

          <v-card-text>
            <v-row class="pa-3">
              <v-col cols="12">
                <v-autocomplete
                  label="Member"
                  :items="members"
                  item-text="name_with_user_id"
                  item-value="id"
                  placeholder="Select"
                  v-model="editedItem.member_id"
                  hide-details
                  dense
                  outlined
                ></v-autocomplete>
              </v-col>

              <v-col cols="12">
                <table class="summary-table">
                  <tr>
                    <td class="text-left" style="min-width: 300px">
                      <b>Item </b>
                    </td>
                    <td class="text-right pr-2"><b>Qty </b></td>
                    <td class="text-right pr-2"><b>Rate</b></td>
                    <td class="text-right pr-2"><b>Tax</b></td>
                    <td class="text-right pr-2" style="width: 30px">
                      <b>Discount</b>
                    </td>
                    <td class="text-right pr-2"><b>Total</b></td>
                    <td class="text-right">
                      <v-icon size="20" color="primary" @click="addItem"
                        >mdi-plus-circle</v-icon
                      >
                    </td>
                  </tr>
                  <tr v-for="(item, index) in editedItem.items" :key="index">
                    <td>
                      <v-autocomplete
                        label="Product"
                        :items="products"
                        item-text="name"
                        item-value="name"
                        placeholder="Select"
                        v-model="item.name"
                        hide-details
                        dense
                        outlined
                        @change="doCalculation(item)"
                      ></v-autocomplete>
                    </td>
                    <td>
                      <input
                        type="number"
                        @input="doCalculation(item)"
                        v-model="item.qty"
                        class="common-input number-input"
                      />
                    </td>
                    <td>
                      <input
                        type="number"
                        @input="doCalculation(item)"
                        v-model="item.rate"
                        class="common-input number-input"
                      />
                    </td>
                    <td>
                      <input
                        type="number"
                        @input="doCalculation(item)"
                        v-model="item.tax"
                        class="common-input number-input"
                      />
                    </td>
                    <td>
                      <input
                        type="number"
                        @input="doCalculation(item)"
                        v-model="item.discount"
                        class="common-input number-input"
                      />
                    </td>
                    <td class="text-center">
                      <input
                        readonly
                        v-model="item.total"
                        class="common-input number-input"
                      />
                    </td>
                    <td class="text-center">
                      <v-icon size="20" color="text">mdi-close</v-icon>
                    </td>
                  </tr>
                </table>
              </v-col>

              <v-col cols="8">
                <v-row>
                  <v-col cols="12">
                    <v-textarea
                      rows="2"
                      hide-details
                      label="Notes"
                      outlined
                      dense
                      v-model="editedItem.notes"
                    ></v-textarea>
                  </v-col>
                  <!-- <v-col cols="6">
                    <v-autocomplete
                      v-model="editedItem.payment_mode_id"
                      hide-details
                      label="Payment Mode"
                      dense
                      outlined
                      :items="payment_modes"
                      item-text="name"
                      item-value="id"
                    >
                    </v-autocomplete>
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                      label="Paid Amount"
                      v-model="editedItem.paid_amount"
                      hide-details
                      dense
                      outlined
                      @input="calculateBalance"
                    >
                    </v-text-field>
                  </v-col> -->
                </v-row>
              </v-col>

              <v-col cols="4">
                <table class="summary-table">
                  <tr>
                    <td>Sub Total</td>
                    <td class="text-right">
                      {{ $utils.currency_format(editedItem.sub_total || "0") }}
                    </td>
                  </tr>
                  <tr>
                    <td>Tax</td>
                    <td class="text-right">
                      {{ $utils.currency_format(editedItem.tax || "0") }}
                    </td>
                  </tr>
                  <tr>
                    <td>Discount</td>
                    <td class="text-right">
                      {{ $utils.currency_format(editedItem.discount || "0") }}
                    </td>
                  </tr>
                  <tr>
                    <td><b>Total</b></td>
                    <td class="text-right">
                      {{ $utils.currency_format(editedItem.total || "0") }}
                    </td>
                  </tr>
                  <!-- <tr>
                    <td :class="`${$vuetify.theme.dark ? 'text' : 'red'}--text`">Balance</td>
                    <td class="text-right" :class="`${$vuetify.theme.dark ? 'text' : 'red'}--text`">
                      {{ $utils.currency_format(editedItem.balance || "0") }}
                    </td>
                  </tr> -->
                </table>
              </v-col>

              <!-- Submit -->
              <v-col cols="6">
                <ul>
                  <li
                    v-for="(error, index) in errors"
                    :key="index"
                    :class="`${$vuetify.theme.dark ? 'text' : 'red'}--text`"
                  >
                    {{ error[0] }}
                  </li>
                </ul>
              </v-col>
              <v-col cols="12" class="text-right">
                <v-btn block color="primary" small @click="save">Submit</v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-dialog>
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
            <v-row>
              <v-col>
                <span> Quotation</span>
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

              <v-col class="text-right">
                <v-btn
                  dense
                  color="primary"
                  small
                  @click="dispalyNewDialog()"
                  class="white--text"
                >
                  <v-icon color="white" small> mdi-plus </v-icon>
                  Quotation
                </v-btn>
              </v-col>
              <v-col cols="12">
                <v-row>
                  <v-col md="2" sm="12" cols="12">
                    <v-autocomplete
                      label="Product"
                      :items="[{ id: null, name: `Select All` }, ...products]"
                      item-text="name"
                      item-value="id"
                      placeholder="Select"
                      v-model="filter.product_id"
                      hide-details
                      dense
                      outlined
                    ></v-autocomplete>
                  </v-col>
                  <v-col md="2" sm="12" cols="12">
                    <v-autocomplete
                      label="Member"
                      :items="[
                        { id: null, name_with_user_id: `Select All` },
                        ...members,
                      ]"
                      item-text="name_with_user_id"
                      item-value="id"
                      placeholder="Select"
                      v-model="filter.member_id"
                      hide-details
                      dense
                      outlined
                    ></v-autocomplete>
                  </v-col>
                  <v-col md="2" sm="12" cols="12">
                    <v-text-field
                      hide-details
                      label="Quotation Number"
                      outlined
                      dense
                      v-model="filter.quotation_number"
                    ></v-text-field>
                  </v-col>
                  <v-col>
                    <div class="mx-1">
                      <CustomFilter
                        @filter-attr="filterAttr"
                        :defaultFilterType="1"
                        height="40px"
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
          </template>

          <template v-slot:item.action="{ item }">
            <QuotationPrint :id="item.id" :quotation="item" />
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
    dialogNewExpense: false,
    options: {},
    filter: {},
    Model: "Quotation",
    endpoint: "quotations",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: true,
    total: 0,
    headers: [
      {
        text: "Quotation Number",
        align: "left",
        sortable: true,
        key: "quotation_number",
        value: "quotation_number",
      },
      {
        text: "Items",
        align: "left",
        sortable: true,
        key: "item_as_strings",
        value: "item_as_strings",
      },

      {
        text: "Member",
        align: "left",
        sortable: true,
        key: "member.full_name",
        value: "member.full_name",
      },

      {
        text: "Quotation Date",
        align: "left",
        sortable: true,
        key: "datetime",
        value: "datetime",
      },
      {
        text: "Sub Total",
        align: "left",
        sortable: true,
        key: "sub_total",
        value: "sub_total",
      },
      {
        text: "Tax",
        align: "left",
        sortable: true,
        key: "tax",
        value: "tax",
      },
      {
        text: "Discount",
        align: "left",
        sortable: true,
        key: "discount",
        value: "discount",
      },
      {
        text: "Total",
        align: "left",
        sortable: true,
        key: "total",
        value: "total",
      },
      // {
      //   text: "Paid Amount",
      //   align: "left",
      //   sortable: true,
      //   key: "paid_amount",
      //   value: "paid_amount",
      // },
      // {
      //   text: "Mode",
      //   align: "left",
      //   sortable: true,
      //   key: "mode.name",
      //   value: "mode.name",
      // },
      // {
      //   text: "Balance",
      //   align: "left",
      //   sortable: true,
      //   key: "balance",
      //   value: "balance",
      // },
      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    editedIndex: -1,
    editedItem: {
      member_id: 1,
      items: [{ name: "", qty: 1, rate: 1, tax: 0, discount: 0, total: 1 }],
      notes: "",
      sub_total: 0,
      tax: 0,
      total: 0,
      paid_amount: 0,
      balance: 0,
    },
    defaultItem: {
      member_id: 1,
      items: [{ name: "", qty: 1, rate: 1, tax: 0, discount: 0, total: 1 }],
      notes: "",
      sub_total: 0,
      tax: 0,
      total: 0,
      paid_amount: 0,
      balance: 0,
    },

    response: "",
    data: [],
    products: [],
    members: [],
    payment_modes: [],
    errors: [],

    formTitle: "New",
    editPermissionId: "",
  }),

  async created() {
    try {
      let { data } = await this.$axios.get(`/members-list`);
      this.members = data;
    } catch (error) {}
    try {
      let { data } = await this.$axios.get(`/products-list`);
      this.products = data;
    } catch (error) {}

    try {
      let { data } = await this.$axios.get(`/payment-modes-list`);
      this.payment_modes = data;
    } catch (error) {}
  },

  computed: {},

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
      let product = this.products.find((e) => e.name == item.name);
      const qty = parseFloat(item.qty) || 0;
      const rate = parseFloat(product?.price || item.rate) || 0;
      const tax = parseFloat(item.tax) || 0;
      const discount = parseFloat(item.discount) || 0;

      item.rate = rate;
      item.total = qty * rate + tax - discount;

      // Recalculate all totals
      let subTotal = 0;
      let totalTax = 0;
      let totalDiscount = 0;
      let grandTotal = 0;

      for (const i of this.editedItem.items) {
        const iRate = parseFloat(i.rate) || 0;
        const iTax = parseFloat(i.tax) || 0;
        const iDiscount = parseFloat(i.discount) || 0;
        const iQty = parseFloat(i.qty) || 0;
        const iTotal = iQty * iRate + iTax - iDiscount;

        subTotal += iRate * iQty;
        totalTax += iTax;
        totalDiscount += iDiscount;
        grandTotal += iTotal;
      }

      this.editedItem.sub_total = subTotal;
      this.editedItem.tax = totalTax;
      this.editedItem.discount = totalDiscount;
      this.editedItem.total = grandTotal;
    },
    calculateBalance() {
      this.editedItem.balance =
        this.editedItem.total - this.editedItem.paid_amount;
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
        tax: 0,
        discount: 0,
        total: 1,
      });
    },
    closeDialog() {
      this.dialogNewExpense = false;
      ++this.compKey;
    },
    dispalyNewDialog() {
      this.errors = [];
      this.editedIndex = -1;
      this.formTitle = "New";
      this.dialogNewExpense = true;
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
      });
    },

    editItem(item) {
      this.errors = [];
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogNewExpense = true;
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
      this.dialogNewExpense = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    save() {
      console.log("🚀 ~ save ~ this.editedItem:", this.editedItem);
      this.$axios
        .post(this.endpoint, this.editedItem)
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
    },
  },
};
</script>
