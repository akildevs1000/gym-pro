<template>
  <v-dialog persistent v-model="viewDialog" max-width="600px" :key="memberId">
    <WidgetsClose left="590" @click="viewDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon color="text" small>mdi-package-variant-closed</v-icon>
        View Plan
      </span>
    </template>

    <v-card flat class="background">
      <v-alert dense class="primary white--text">
        View Plan for Member ({{ memberObject.full_name }})
      </v-alert>
      <v-card-text>
        <v-container v-if="memberObject && memberObject.last_membership">
          <v-row>
            <v-col cols="6">
              <v-text-field
                label="Name *"
                v-model="memberObject.last_membership.plan.name"
                outlined
                dense
                hide-details
                readonly
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                label="Price *"
                v-model="memberObject.last_membership.plan.price"
                outlined
                dense
                hide-details
                readonly
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                rows="2"
                label="Description *"
                v-model="memberObject.last_membership.plan.description"
                outlined
                dense
                hide-details
                readonly
              ></v-textarea>
            </v-col>
          </v-row>
          <v-row>
            <v-col md="6" sm="12" cols="12">
              <div>
                <v-menu
                  v-model="joiningDateMenuOpen"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  max-width="290px"
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      label="Plan Start Date"
                      :hide-details="!errors.plan_start_date"
                      :error-messages="
                        errors && errors.plan_start_date
                          ? errors.plan_start_date[0]
                          : ''
                      "
                      v-model="memberShip.plan_start_date"
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
                    v-model="memberShip.plan_start_date"
                    no-title
                    @input="joiningDateMenuOpen = false"
                  ></v-date-picker>
                </v-menu>
              </div>
            </v-col>

            <v-col md="6" sm="12" cols="12">
              <!-- <label class="col-form-label"
                    >Joining Date <span class="text-danger">*</span></label
                  > -->
              <div>
                <v-menu
                  v-model="joiningDateMenuOpen2"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  max-width="290px"
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      label="Plan End Date"
                      :hide-details="!errors.plan_end_date"
                      :error-messages="
                        errors && errors.plan_end_date
                          ? errors.plan_end_date[0]
                          : ''
                      "
                      v-model="memberShip.plan_end_date"
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
                    v-model="memberShip.plan_end_date"
                    no-title
                    @input="joiningDateMenuOpen2 = false"
                  ></v-date-picker>
                </v-menu>
              </div>
            </v-col>
          </v-row>

          <!-- Payment Details -->
          <div
            class="text-subtitle-2 font-weight-medium text--text mt-8 mb-2"
          >
            Payment Details
          </div>
          <v-row>
            <v-col cols="12" sm="6">
              <v-autocomplete
                label="Payment Mode"
                :items="payment_modes"
                item-value="id"
                item-text="name"
                v-model="memberShip.payment_mode_id"
                dense
                outlined
                :hide-details="!errors['payment_mode_id']"
                :error="Boolean(errors['payment_mode_id'])"
                :error-messages="errors?.payment_mode_id?.[0] || ''"
              />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                label="Payment Mode Ref"
                v-model="memberShip.payment_mode_ref"
                dense
                outlined
                :hide-details="!errors['payment_mode_ref']"
                :error="Boolean(errors['payment_mode_ref'])"
                :error-messages="errors?.payment_mode_ref?.[0] || ''"
              />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                label="Discount"
                v-model="memberShip.discount"
                dense
                outlined
                type="text"
                @input="setPaidAmount"
                :hide-details="!errors['discount']"
                :error="Boolean(errors['discount'])"
                :error-messages="errors?.discount?.[0] || ''"
              />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                label="Due Balance"
                v-model="memberShip.balance"
                dense
                outlined
                hide-details
                readonly
              />
            </v-col>
            <v-col cols="12">
              <v-text-field
                label="Total"
                v-model="memberShip.paid_amount"
                dense
                outlined
                type="text"
                :hide-details="!errors['paid_amount']"
                :error="Boolean(errors['paid_amount'])"
                :error-messages="errors?.paid_amount?.[0] || ''"
              />
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  props: ["memberId", "memberObject"],
  data: () => ({
    joiningDateMenuOpen: false,
    joiningDateMenuOpen2: false,
    viewDialog: false,
    dialog: false,
    Model: "Member",
    endpoint: "member",
    loading: false,
    response: "",
    snackbar: false,
    errors: [],
    memberShip: {},
    payment_modes: [],
  }),

  watch: {
    dialog(val) {
      val || this.close();
    },
  },

  async created() {
    this.getPaymentModes();

    const expiredMemberShip = this.memberObject?.last_membership;
    const {
      payment_mode_id,
      payment_mode_ref,
      discount,
      balance,
    } = this.memberObject?.last_payment;

    const today = new Date();
    const formatDate = (date) => date.toISOString().split("T")[0];

    if (expiredMemberShip?.plan?.id) {
      this.memberShip = {
        id: expiredMemberShip.id,
        plan_start_date: formatDate(today),
        plan_end_date: formatDate(
          new Date(today.getFullYear() + 1, today.getMonth(), today.getDate())
        ),
        payment_mode_id: payment_mode_id || null,
        payment_mode_ref: payment_mode_ref || "",
        plan_price: parseFloat(expiredMemberShip.plan.price),
        discount: parseFloat(discount || 0),
        balance: parseFloat(balance || 0),
        paid_amount: 0,
        member_id: expiredMemberShip?.member_id,
        plan_id: expiredMemberShip?.plan_id,
      };

      this.setPaidAmount();
    }
  },
  methods: {
    setPaidAmount() {
      let discount = this.memberShip.discount || 0;

      this.memberShip.discount = discount;
      let total = Math.abs(
        parseFloat(this.memberShip.plan_price) +
          parseFloat(this.memberShip.balance) -
          parseFloat(discount)
      );

      if (discount > total) {
        total =
          parseFloat(this.memberShip.plan_price) +
          parseFloat(this.memberShip.balance) -
          parseFloat(0);
      }
      this.memberShip.paid_amount = total;
    },
    async getPaymentModes() {
      try {
        let { data } = await this.$axios.get(`/payment-modes-list`);
        this.payment_modes = data;
      } catch (error) {}
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    close() {
      this.dialog = false;
      this.$emit("close-popup");
    },

    async update() {
      this.loading = true;

      let url = `/membership-renewal/${this.memberShip.id}`;

      try {
        const { data } = await this.$axios.get(url, {
          params: this.memberShip,
        });

        if (!data.status) {
          this.errors = data.errors || {};
        } else {
          this.errors = {};
          this.snackbar = true;
          this.response = "Membership Renewed successfully";
          this.$emit("eventFromchild");
          setTimeout(() => (this.viewDialog = false), 1000);
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
