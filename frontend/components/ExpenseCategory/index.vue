<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog persistent v-model="dialogNewPaymentModes" width="500">
      <WidgetsClose left="490" @click="closeDialog" />

      <v-card class="background">
        <v-alert dense flat dark class="primary">
          {{ formTitle }} {{ Model }}
          <v-spacer></v-spacer>
        </v-alert>

        <v-card-text>
          <v-row>
            <v-col cols="12">
              <v-text-field
                hide-details
                label="Name"
                outlined
                dense
                v-model="editedItem.name"
              ></v-text-field>
              <span v-if="errors?.name" class="error--text">
                {{ errors.name[0] }}
              </span>
            </v-col>
            <v-col cols="12">
              <v-textarea
                rows="3"
                hide-details
                label="Description"
                outlined
                dense
                v-model="editedItem.description"
              ></v-textarea>
              <span v-if="errors?.description" class="error--text">
                {{ errors.description[0] }}
              </span>
            </v-col>
            <!-- Submit -->
            <v-col cols="12" class="text-right">
              <v-btn color="primary" small @click="save">Save</v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

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
            <v-card class="mb-5 rounded-md mt-3 background" elevation="0">
              <v-toolbar class="rounded-md background darken-1" dense flat>
                <span> Expense Category </span>
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
                      Expense Category
                    </v-btn>
                  </v-col>
                </v-toolbar-items>
              </v-toolbar>
            </v-card>
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
  </div>
</template>
<script>
export default {
  data: () => ({
    expandedIndex: null, // Index of the currently expanded item
    compKey: 1,
    dialogNewPaymentModes: false,
    options: {},
    Model: "Expense Category",
    endpoint: "expense-category",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: true,
    total: 0,
    headers: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        key: "name",
        value: "name",
      },
      {
        text: "Description",
        align: "left",
        sortable: true,
        key: "description",
        value: "description",
      },
      { text: "Actions", align: "center", value: "action", sortable: false },
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
    closeDialog() {
      this.dialogNewPaymentModes = false;
      ++this.compKey;
    },
    dispalyNewDialog() {
      this.errors = [];
      this.editedItem = { name: "", description: "" };
      this.editedIndex = -1;
      this.formTitle = "New";
      this.dialogNewPaymentModes = true;
      this.permission_ids = [];
    },

    getDataFromApi(url = this.endpoint) {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      let options = {
        params: {
          per_page: itemsPerPage,
          company_id: this.$auth.user.company.id,
          role_type: "employee",
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
      this.dialogNewPaymentModes = true;

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
      this.dialogNewPaymentModes = false;
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
              this.dialogNewPaymentModes = false;
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
