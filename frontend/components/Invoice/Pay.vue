<template>
  <v-dialog persistent v-model="paymentDialog" width="500">
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon color="primary" small>mdi-cash</v-icon>
      </span>
    </template>
    <WidgetsClose left="490" @click="close" />
    <v-card class="background">
      <v-toolbar dense class="violet white--text" style="font-weight: 400">
        Payment
      </v-toolbar>
      <v-card-text>
        <v-container>
          <v-row>
            <v-col class="mt-5" dense>
              <v-row>
                <v-col cols="12" dense>
                  <div style="display: flex">
                    <v-autocomplete
                      label="Payment mode"
                      :items="payment_modes"
                      item-value="id"
                      dense
                      outlined
                      v-model="payment.payment_mode_id"
                      item-text="name"
                      placeholder="Select"
                      hide-details
                      :error="Boolean(errors['payment_mode_id'])"
                      :error-messages="
                        errors && errors['payment_mode_id']
                          ? errors['payment_mode_id'][0]
                          : ''
                      "
                    >
                    </v-autocomplete>
                  </div>
                </v-col>

                <v-col cols="12" dense>
                  <v-text-field
                    label="Payment Mode Ref"
                    dense
                    outlined
                    type="text"
                    v-model="payment.payment_mode_ref"
                    :hide-details="!errors['payment_mode_ref']"
                    :error="Boolean(errors['payment_mode_ref'])"
                    :error-messages="
                      errors && errors['payment_mode_ref']
                        ? errors['payment_mode_ref'][0]
                        : ''
                    "
                  ></v-text-field>
                </v-col>

                <v-col cols="12" dense>
                  <v-text-field
                    label="Balance"
                    dense
                    outlined
                    type="text"
                    v-model="item.balance"
                    hide-details
                    readonly
                  ></v-text-field>
                </v-col>

                <v-col cols="12" dense>
                  <v-text-field
                    label="Amount"
                    dense
                    outlined
                    type="text"
                    v-model="payment.paid_amount"
                    :hide-details="!errors['paid_amount']"
                    :error="Boolean(errors['paid_amount'])"
                    :error-messages="
                      errors && errors['paid_amount']
                        ? errors['paid_amount'][0]
                        : ''
                    "
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          :disabled="
            item.balance == 0 || parseFloat(payment.paid_amount) > item.balance
          "
          v-if="can('payment_create')"
          small
          color="violet"
          @click="submit"
        >
          Submit
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ["item"],
  data: () => ({
    paymentDialog: false,
    payment: {
      payment_mode_id: 0,
      payment_mode_ref: "",
      paid_amount: 0,
    },
    payment_modes: [],
    errors: [],
  }),

  async created() {
    await this.getPaymentModes();
  },
  methods: {
    getDefaultPayload() {
      return {
        payment_mode_id: 0,
        payment_mode_ref: "",
        paid_amount: 0,
      };
    },
    async openNewPage() {
      this.payment = this.getDefaultPayload(); // ✅ reset with default structure
      this.paymentDialog = true;
      await this.getPaymentModes();
    },
    async getPaymentModes() {
      try {
        let { data } = await this.$axios.get(`/payment-modes-list`);
        this.payment_modes = data;
      } catch (error) {}
    },
    closeViewDialog() {
      this.viewDialog = false;
    },

    close() {
      this.payment = {};
      this.paymentDialog = false;
      this.errors = [];
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    submit() {
      this.$axios
        .post("invoices-payments", {
          invoice_id: this.item.id,
          ...this.payment,
          balance: parseFloat(this.item.balance),
          paid_amount: parseFloat(this.payment.paid_amount),
        })
        .then(({ data }) => {
          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.$emit("response", "Payment Has Been Recorded Successfully");
            this.errors = [];
            this.paymentDialog = false;
          }
        })
        .catch((res) => {
          console.log("🚀 ~ submit ~ res:", res)
          this.$emit("response", "Payment can not be recorded");
          this.errors = [];
          this.paymentDialog = false;
        });
    },
  },
};
</script>
