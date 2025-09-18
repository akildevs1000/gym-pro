<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog persistent v-model="dialogNewVendor" width="700">
      <WidgetsClose left="690" @click="closeDialog" />

      <v-card class="background">
        <v-alert dense flat dark class="primary">
          {{ formTitle }} {{ Model }}
          <v-spacer></v-spacer>
        </v-alert>

        <v-card-text>
          <v-row>
            <v-col cols="12">
              <v-select
                label="Vendor Type"
                v-model="editedItem.type"
                :items="[`Company`, `Personal`]"
                :hide-details="!errors.type"
                :error="errors.type"
                :error-messages="errors && errors.type ? errors.type[0] : ''"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="4">
              <v-select
                label="Title"
                v-model="editedItem.title"
                :items="[`Mr`, `Mrs`, `Miss`, `Ms`, `Dr`]"
                :hide-details="!errors.title"
                :error="errors.title"
                :error-messages="errors && errors.title ? errors.title[0] : ''"
                dense
                outlined
              ></v-select>
            </v-col>
            <v-col cols="4">
              <v-text-field
                hide-details
                label="First Name"
                outlined
                dense
                v-model="editedItem.first_name"
              ></v-text-field>
              <span v-if="errors?.first_name" class="error--text">
                {{ errors.first_name[0] }}
              </span>
            </v-col>
            <v-col cols="4">
              <v-text-field
                hide-details
                label="Last Name"
                outlined
                dense
                v-model="editedItem.last_name"
              ></v-text-field>
              <span v-if="errors?.last_name" class="error--text">
                {{ errors.last_name[0] }}
              </span>
            </v-col>
            <v-col cols="6">
              <v-text-field
                hide-details
                label="Company Name"
                outlined
                dense
                v-model="editedItem.company_name"
              ></v-text-field>
              <span v-if="errors?.company_name" class="error--text">
                {{ errors.company_name[0] }}
              </span>
            </v-col>
            <v-col cols="6">
              <v-text-field
                hide-details
                label="Tax Number"
                outlined
                dense
                v-model="editedItem.tax_number"
              ></v-text-field>
              <span v-if="errors?.tax_number" class="error--text">
                {{ errors.tax_number[0] }}
              </span>
            </v-col>
            <v-col cols="4">
              <v-text-field
                hide-details
                label="Email"
                outlined
                dense
                v-model="editedItem.email"
              ></v-text-field>
              <span v-if="errors?.email" class="error--text">
                {{ errors.email[0] }}
              </span>
            </v-col>
            <v-col cols="4">
              <v-text-field
                hide-details
                label="Work Phone"
                outlined
                dense
                v-model="editedItem.work_phone"
              ></v-text-field>
              <span v-if="errors?.work_phone" class="error--text">
                {{ errors.work_phone[0] }}
              </span>
            </v-col>
            <v-col cols="4">
              <v-text-field
                hide-details
                label="Phone"
                outlined
                dense
                v-model="editedItem.phone_number"
              ></v-text-field>
              <span v-if="errors?.phone_number" class="error--text">
                {{ errors.phone_number[0] }}
              </span>
            </v-col>
            <!-- Submit -->

            <v-col md="4" cols="12" sm="12">
              <v-autocomplete
                :items="countries"
                item-text="name"
                item-value="name"
                label="Country"
                v-model="editedItem.country"
                outlined
                :hide-details="true"
                dense
                @change="getStates(editedItem.country)"
              ></v-autocomplete>
            </v-col>
            <v-col md="4" cols="12" sm="12">
              <v-autocomplete
                :readonly="editedItem.country == 'International'"
                :items="states"
                item-text="name"
                item-value="name"
                label="State"
                v-model="editedItem.state"
                outlined
                :hide-details="true"
                dense
                @change="getCities"
              ></v-autocomplete>
            </v-col>
            <v-col md="4" cols="12" sm="12">
              <v-text-field
                :readonly="editedItem.country == 'International'"
                label="Zip Code"
                v-model="editedItem.zip_code"
                outlined
                :hide-details="true"
                dense
              ></v-text-field>
            </v-col>
            <v-col cols="12" class="text-right">
              <v-btn color="primary" small @click="save">Save</v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-row>
      <v-col md="12">
        <v-data-table class="background"
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
            <v-card class="mb-5 rounded-md mt-3" elevation="0">
              <v-toolbar class="rounded-md" dense flat>
                <span> Vendor</span>
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
                <v-spacer></v-spacer>
                <v-toolbar-items>
                  <v-col>
                    <v-btn
                      dense
                      color="primary"
                      small
                      @click="dispalyNewDialog()"
                      class="white--text"
                    >
                      <v-icon color="white" small> mdi-plus </v-icon>
                      Vendor
                    </v-btn>
                  </v-col>
                </v-toolbar-items>
              </v-toolbar>
            </v-card>
          </template>

          <template v-slot:item.company="{ item, index }" style="width: 300px">
            <div v-if="item.type == 'Company'">
              <div>Name: {{ item.company_name }}</div>
              <div>Tax #: {{ item.tax_number }}</div>
              <div>Work Phone: {{ item.work_phone }}</div>
            </div>
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
        </v-data-table></v-col
      >
    </v-row>
  </div>
</template>
<script>
export default {
  data: () => ({
    countries: require("@/jsons/countries.json"),
    states:[],
    expandedIndex: null, // Index of the currently expanded item
    compKey: 1,
    dialogNewVendor: false,
    options: {},
    Model: "Vendor",
    endpoint: "vendor",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: true,
    total: 0,
    headers: [
      { text: "Type", value: "type", sortable: true },
      { text: "Company", value: "company", sortable: true },
      { text: "Full Name", value: "full_name", sortable: true },
      { text: "Email", value: "email", sortable: true },
      { text: "Phone Number", value: "phone_number", sortable: true },
      { text: "Full Address", value: "full_address", sortable: true },
      { text: "Actions", value: "action", align: "center", sortable: false },
    ],

    editedIndex: -1,
    editedItem: {
      name: "",
    },
    defaultItem: {
      name: "",
    },

    response: "",
    data: [],
    errors: [],

    formTitle: "New",
    editPermissionId: "",
  }),

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
    getStates(country) {
      // Find the country object from the countries array
      const countryObj = this.countries.find((e) => e.name === country);

      // Check if the country object exists
      if (countryObj) {
        // Set the states array from the found country object
        this.states = countryObj.states || [];
      } else {
        // If country not found, clear the states array and handle error
        this.states = [];
      }
    },
    closeDialog() {
      this.dialogNewVendor = false;
      ++this.compKey;
    },
    dispalyNewDialog() {
      this.errors = [];
      this.editedItem = { name: "", description: "" };
      this.editedIndex = -1;
      this.formTitle = "New";
      this.dialogNewVendor = true;
      this.permission_ids = [];
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
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
      this.dialogNewVendor = true;
      this.getStates(item.country)
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
      this.dialogNewVendor = false;
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
              this.dialogNewVendor = false;
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
