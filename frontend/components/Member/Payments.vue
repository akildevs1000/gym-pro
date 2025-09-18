<template>
  <v-dialog persistent v-model="viewDialog" width="900px" :key="memberId">
    <WidgetsClose left="890" @click="viewDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on"> <v-icon color="text" small> mdi-cash </v-icon>  Payments </span>
    </template>
    <v-card flat class="background" style="overflow: hidden">
      <v-row
        v-if="memberObject && memberObject.id"
        align="center"
        no-gutters
        class="pa-0 ma-0"
      >
        <v-col cols="12">
          <div dense flat dark class="white--text primary pa-2">
            Payments
          </div>
        </v-col>
        <v-col cols="12">
          <v-container>
            <v-card flat class="background">
              <v-card-text>
                <v-row
                  v-if="can('employee_profile_view')"
                  class="d-flex align-center"
                >
                  <v-col>
                    <div
                      class="text-right"
                      v-if="
                        can(
                          !editForm
                            ? 'employee_profile_edit'
                            : 'employee_profile_view'
                        )
                      "
                    >
                      <!-- <v-icon small color="primary" @click="editForm = !editForm"
              >mdi-{{ editForm ? "eye" : "pencil" }}</v-icon
            > -->
                    </div>
                    <v-simple-table
                      v-if="can('employee_profile_view') && !loading"
                      dense
                      flat
                      class="my-simple-table background"
                    >
                      <tbody>
                        <tr>
                          <td class="text-center text--text">#</td>
                          <td class="text-center text--text">Amount</td>
                          <td class="text-center text--text">Description</td>
                          <td class="text-center text--text">Mode</td>
                          <td class="text-center text--text">Mode Ref</td>
                          <td class="text-center text--text">Date Time</td>
                          <td class="text-center text--text">Print</td>
                        </tr>
                        <tr v-for="(payment, index) in payments" :key="index">
                          <td class="text-center">{{ payment.id }}</td>
                          <td class="text-center">{{ payment.paid_amount }}</td>
                          <td class="text-center">{{ payment.description }}</td>
                          <td class="text-center">{{ payment?.mode?.name }}</td>
                          <td class="text-center">
                            {{ payment.payment_mode_ref || "---" }}
                          </td>
                          <td class="text-center">{{ payment?.datetime }}</td>

                           <td class="text-center">
                            <MemberPrintReceipt :key="payment.id" :id="payment.id" :item="payment" />
                          </td>
                        </tr>
                      </tbody>
                    </v-simple-table>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-container>
        </v-col>
      </v-row>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  props: ["memberId", "memberObject"],
  data: () => ({
    viewDialog: false,
    menu: false,
    menu2: false,
    editForm: false,
    image: "",
    leave_managers: [],
    mime_type: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    selectedFile: "",
    upload_edit: {
      name: "",
    },

    attrs: [],
    dialog: false,
    editDialog: false,
    tab: null,
    m: false,
    expand: false,
    expand2: false,
    boilerplate: false,
    right: true,
    rightDrawer: false,
    drawer: true,
    tab: null,
    selectedItem: 1,

    on: "",
    color: "background",
    files: "",
    Model: "Member",
    endpoint: "member",
    loading: true,
    response: "",
    snackbar: false,
    plan: {},
    previewImage: null,
    snackbar: false,
    ids: [],
    titleItems: ["Mr", "Mrs", "Miss", "Ms", "Dr"],
    response: "",
    data: [],
    errors: [],
    plans: [],

    selected_plan: {
      id: null,
      name: null,
      price: null,
      description: null,
    },
    payments: [],
  }),

  watch: {
    dialog(val) {
      val || this.close();
    },
    options: {
      handler() {
        this.getDataFromApi();
      },
      deep: true,
    },
  },
  async created() {

    this.payments = this.memberObject.payments;

    this.loading = false;
  },
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    close() {
      this.dialog = false;
      this.$emit("close-popup");
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
  },
};
</script>
