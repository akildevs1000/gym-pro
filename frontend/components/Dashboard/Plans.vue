<template>
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
    <template v-slot:item.serial="{ item, index }">
      {{ index + 1 }}
    </template>
  </v-data-table>
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
        text: "#",
        align: "left",
        sortable: true,
        key: "serial",
        value: "serial",
      },
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
