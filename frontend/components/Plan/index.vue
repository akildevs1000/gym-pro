<template>
  <div>
    <div class="text-center ma-2">
      <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
        {{ response }}
      </v-snackbar>
    </div>
    <v-dialog persistent v-model="dialogNewPlan" width="700">
      <WidgetsClose left="690" @click="closeDialog" />

      <v-card class="background">
        <v-alert dense flat dark class="primary">
          {{ formTitle }} {{ Model }}
          <v-spacer></v-spacer>
        </v-alert>

        <v-card-text>
          <v-row>
            <!-- Plan Name -->
            <v-col cols="6">
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

            <!-- Description -->
            <v-col cols="6">
              <v-text-field
                hide-details
                label="Description"
                outlined
                dense
                v-model="editedItem.description"
              ></v-text-field>
              <span v-if="errors?.description" class="error--text">
                {{ errors.description[0] }}
              </span>
            </v-col>

            <!-- Price -->
            <v-col cols="6">
              <v-text-field
                hide-details
                label="Price"
                type="number"
                outlined
                dense
                v-model="editedItem.price"
              ></v-text-field>
              <span v-if="errors?.price" class="error--text">
                {{ errors.price[0] }}
              </span>
            </v-col>

            <!-- Duration in Days -->
            <v-col cols="6">
              <v-text-field
                hide-details
                label="Duration (Days)"
                type="number"
                outlined
                dense
                v-model="editedItem.duration_in_days"
              ></v-text-field>
              <span v-if="errors?.duration_in_days" class="error--text">
                {{ errors.duration_in_days[0] }}
              </span>
            </v-col>

            <!-- Features (JSON list input) -->
            <v-col cols="12">
              <v-textarea
                hide-details
                label="Features (one per line)"
                outlined
                dense
                rows="3"
                v-model="featuresInput"
              ></v-textarea>
              <span v-if="errors?.features" class="error--text">
                {{ errors.features[0] }}
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
            <v-card class="mb-5 rounded-md mt-3" elevation="0">
              <v-toolbar class="rounded-md background darken-1" dense flat>
                <span> Plans</span>
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
                      Plan
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
    dialogNewPlan: false,
    options: {},
    Model: "Plan",
    endpoint: "plans",
    search: "",
    snackbar: false,
    dialog: false,
    ids: [],
    loading: true,
    total: 0,
    headers: [
      {
        text: "Plan",
        align: "left",
        sortable: true,
        key: "name",
        value: "name",
      },
      {
        text: "Price",
        align: "left",
        sortable: true,
        key: "price",
        value: "price",
      },
      {
        text: "Duration (Days)",
        align: "left",
        sortable: true,
        key: "duration_in_days",
        value: "duration_in_days",
      },
      {
        text: "Features",
        align: "left",
        sortable: true,
        key: "features",
        value: "features",
      },
      { text: "Actions", align: "center", value: "action", sortable: false },
    ],
    editedIndex: -1,
    editedItem: {
      name: "",
      price: null,
      duration_in_days: null,
      description: "",
      features: [],
    },
    defaultItem: {
      name: "",
      price: null,
      duration_in_days: null,
      description: "",
      features: [],
    },
    featuresInput: "",

    response: "",
    data: [],
    errors: [],

    permission_ids: [],
    permissions: [],
    formTitle: "New",
    editPermissionId: "",
  }),

  computed: {},

  watch: {
    featuresInput(val) {
      this.editedItem.features = val.split("\n").filter(Boolean);
    },
    "editedItem.features": {
      handler(val) {
        this.featuresInput = (val || []).join("\n");
      },
      immediate: true,
    },
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
      this.dialogNewPlan = false;
      ++this.compKey;
    },
    handleSelectedPermissions(e) {
      this.permission_ids = e;
    },
    dispalyNewDialog() {
      this.errors = [];
      this.editedItem = { name: "", description: "" };
      this.editedIndex = -1;
      this.formTitle = "New";
      this.dialogNewPlan = true;
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
      this.dialogNewPlan = true;

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
      this.dialogNewPlan = false;
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
              this.dialogNewPlan = false;
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
              alert(data.status);
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
