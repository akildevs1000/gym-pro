<template>
  <v-container fluid>
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
                <span> Expense</span>
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
                  Expense
                </v-btn>
              </v-col>
              <v-col cols="12">
                <v-row>
                  <v-col md="2" sm="12" cols="12">
                    <v-select
                      label="Expense Type"
                      v-model="filter.type"
                      :items="[
                        { id: null, name: `Select All` },
                        { id: `Admin Expense`, name: `Admin Expense` },
                        { id: `Regular Expense`, name: `Regular Expense` },
                      ]"
                      item-value="id"
                      item-text="name"
                      hide-details
                      dense
                      outlined
                    ></v-select>
                  </v-col>
                  <v-col md="2" sm="12" cols="12">
                    <v-autocomplete
                      label="Expense Category"
                      :items="[
                        { id: null, name: `Select All` },
                        ...expense_categories,
                      ]"
                      item-text="name"
                      item-value="id"
                      placeholder="Select"
                      v-model="filter.expense_category_id"
                      hide-details
                      dense
                      outlined
                    ></v-autocomplete>
                  </v-col>
                  <!-- <v-col md="2" sm="12" cols="12">
                    <v-autocomplete
                      label="Vendor"
                      :items="[
                        { id: null, name_with_number: `Select All` },
                        ...vendors,
                      ]"
                      item-text="name_with_number"
                      item-value="id"
                      placeholder="Select"
                      v-model="filter.vendor_id"
                      hide-details
                      dense
                      outlined
                    ></v-autocomplete>
                  </v-col> -->
                  <v-col md="2" sm="12" cols="12">
                    <v-text-field
                      hide-details
                      label="Invoice Number"
                      outlined
                      dense
                      v-model="filter.invoice_number"
                    ></v-text-field>
                  </v-col>
                  <v-col>
                    <div class="mx-1" style="width:200px;">
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
            <v-icon
              color="secondary"
              small
              class="mr-2"
              @click="editItem(item)"
            >
              mdi-pencil
            </v-icon>
            <v-icon color="error" small @click="deleteItem(item)">
              mdi-delete
            </v-icon>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
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
          color: grey;
          border: 1px solid grey;
          border-radius: 4px;
          padding: 2px 8px;
          width: 100%;
          outline: none;
          box-sizing: border-box;
          height: 38px;
        }

        .number-input {
          text-align: right;
        }

        /* Optional: Add hover/focus effects */
        .common-input:focus {
          border-color: #1976d2; /* Vuetify primary color */
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
            <v-container>
              <v-row>
                <v-col cols="6">
                  <v-select
                    label="Expense Type"
                    v-model="editedItem.type"
                    :items="[
                      { id: `Admin Expense`, name: `Admin Expense` },
                      { id: `Regular Expense`, name: `Regular Expense` },
                    ]"
                    item-value="id"
                    item-text="name"
                    :hide-details="!errors.type"
                    :error="errors.type"
                    :error-messages="
                      errors && errors.type ? errors.type[0] : ''
                    "
                    dense
                    outlined
                  ></v-select>
                </v-col>
                <v-col cols="6">
                  <v-autocomplete
                    label="Expense Category"
                    :items="expense_categories"
                    item-text="name"
                    item-value="id"
                    placeholder="Select"
                    v-model="editedItem.expense_category_id"
                    :hide-details="!errors.expense_category_id"
                    :error="errors.expense_category_id"
                    :error-messages="
                      errors && errors.expense_category_id
                        ? errors.expense_category_id[0]
                        : ''
                    "
                    dense
                    outlined
                  ></v-autocomplete>
                </v-col>
                <!-- <v-col cols="12">
                  <v-autocomplete
                    label="Vendor"
                    :items="vendors"
                    item-text="name_with_number"
                    item-value="id"
                    placeholder="Select"
                    v-model="editedItem.vendor_id"
                    :hide-details="!errors.vendor_id"
                    :error="errors.vendor_id"
                    :error-messages="
                      errors && errors.vendor_id ? errors.vendor_id[0] : ''
                    "
                    dense
                    outlined
                  ></v-autocomplete>
                </v-col> -->
                <v-col md="6" sm="12" cols="12">
                  <v-text-field
                    hide-details
                    label="Invoice Number"
                    outlined
                    dense
                    v-model="editedItem.invoice_number"
                  ></v-text-field>
                  <span v-if="errors?.invoice_number" class="error--text">
                    {{ errors.invoice_number[0] }}
                  </span>
                </v-col>
                <v-col md="6" sm="12" cols="12">
                  <div>
                    <v-menu
                      v-model="dateMenu"
                      :close-on-content-click="false"
                      transition="scale-transition"
                      offset-y
                      max-width="290px"
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          label="Invoice Date"
                          :hide-details="!errors.invoice_date"
                          :error-messages="
                            errors && errors.invoice_date
                              ? errors.invoice_date[0]
                              : ''
                          "
                          v-model="editedItem.invoice_date"
                          persistent-hint
                          append-icon="mdi-calendar"
                          readonly
                          outlined
                          dense
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        style="min-height: 320px"
                        v-model="editedItem.invoice_date"
                        no-title
                        @input="dateMenu = false"
                      ></v-date-picker>
                    </v-menu>
                  </div>
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
                      <td class="text-right pr-2"><b>Total</b></td>
                      <td class="text-right">
                        <v-icon size="20" color="primary" @click="addItem"
                          >mdi-plus-circle</v-icon
                        >
                      </td>
                    </tr>
                    <tr v-for="(item, index) in editedItem.items" :key="index">
                      <td class="pa-2">
                        <input v-model="item.name" class="common-input" />
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
                      <td class="text-center">
                        <input
                          readonly
                          v-model="item.total"
                          class="common-input number-input"
                        />
                      </td>
                      <td class="text-center">
                        <v-icon size="20" color="red">mdi-close</v-icon>
                      </td>
                    </tr>
                  </table>
                </v-col>

                <v-col cols="9">
                  <v-textarea
                    rows="3"
                    hide-details
                    label="Notes"
                    outlined
                    dense
                    v-model="editedItem.notes"
                  ></v-textarea>
                  <span v-if="errors?.notes" class="error--text">
                    {{ errors.notes[0] }}
                  </span>
                </v-col>

                <v-col cols="3">
                  <table class="summary-table">
                    <tr>
                      <td>Sub Total</td>
                      <td class="text-right">
                        {{
                          $utils.currency_format(editedItem.sub_total || "0")
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Tax</td>
                      <td class="text-right">
                        {{ $utils.currency_format(editedItem.tax || "0") }}
                      </td>
                    </tr>
                    <tr>
                      <td><b>Total</b></td>
                      <td class="text-right">
                        {{ $utils.currency_format(editedItem.total || "0") }}
                      </td>
                    </tr>
                  </table>
                </v-col>

                <!-- Submit -->
                <v-col cols="12" class="text-right">
                  <v-btn color="primary" small @click="save">Save</v-btn>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
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
    Model: "Expense",
    endpoint: "expense",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: true,
    total: 0,
    headers: [
      {
        text: "Type",
        align: "left",
        sortable: true,
        key: "type",
        value: "type",
      },
      {
        text: "Category",
        align: "left",
        sortable: true,
        key: "expense_category.name",
        value: "expense_category.name",
      },
      {
        text: "Vendor",
        align: "left",
        sortable: true,
        key: "vendor.full_name",
        value: "vendor.full_name",
      },
      {
        text: "Invoice Number",
        align: "left",
        sortable: true,
        key: "invoice_number",
        value: "invoice_number",
      },
      {
        text: "Invoice Date",
        align: "left",
        sortable: true,
        key: "show_invoice_date",
        value: "show_invoice_date",
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
        text: "Total",
        align: "left",
        sortable: true,
        key: "total",
        value: "total",
      },
      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    editedIndex: -1,
    editedItem: {
      type: "RegularExpense",
      expense_category_id: 1,
      invoice_number: "",
      invoice_date: "",
      notes: "",
      sub_total: 100,
      tax: 0,
      total: 100,
      items: [
        {
          name: "Your Item",
          qty: 1,
          rate: 1,
          tax: 1,
          total: 1,
        },
      ],
      attachments: [],
    },
    defaultItem: {
      type: "",
      expense_category_id: 1,
      invoice_number: "",
      invoice_date: "",
      notes: "",
      sub_total: 100,
      tax: 0,
      total: 100,
      items: [],
      attachments: [],
    },

    response: "",
    data: [],
    expense_categories: [],
    vendors: [],
    errors: [],

    formTitle: "New",
    editPermissionId: "",
  }),

  async created() {
    await this.getExpenseCategories();
    await this.getVendors();
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
    async getExpenseCategories() {
      try {
        let { data } = await this.$axios.get(`/expense-category-list`);
        this.expense_categories = data;
      } catch (error) {}
    },
    async getVendors() {
      try {
        let { data } = await this.$axios.get(`/vendor-list`);
        this.vendors = data;
      } catch (error) {}
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
      console.log(item);
      this.errors = [];
      this.editedIndex = this.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      //this.dialog = true;
      this.dialogNewExpense = true;

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
      this.dialogNewExpense = false;
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
              this.dialogNewExpense = false;
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
