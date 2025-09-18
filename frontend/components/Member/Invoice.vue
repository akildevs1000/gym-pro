<template>
  <v-dialog persistent v-model="viewDialog" max-width="700px" :key="memberId">
    <WidgetsClose left="690" @click="viewDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon color="text" small>mdi-cash</v-icon>
        Invoice
      </span>
    </template>

    <v-card flat light>
      <v-alert dense class="primary white--text">
        <v-row>
          <v-col cols="6">
            Invoice for Member ({{ memberObject.full_name }})
          </v-col>
          <v-col cols="6" class="text-right">
            <v-icon @click="print" dark>mdi-printer-outline</v-icon>
          </v-col>
        </v-row>
      </v-alert>
      <v-card-text>
        <v-container class="pa-5" id="target">
          <!-- Header: Logo + Gym Info -->

          <v-row>
            <v-col cols="6">
              <img
                class="logo-image"
                title="My Time Cloud"
                :src="`logo22.png`"
                style="width: 150px"
              />
            </v-col>
            <v-col cols="6" class="text-right">
              <div class="text-subtitle-1 font-weight-bold">Gym Pro</div>
              <div class="text-caption">123 Fitness St</div>
              <div class="text-caption">Dubai, UAE, 0000</div>
              <div class="text-caption">Phone: +1 234 567 890</div>
            </v-col>
          </v-row>

          <!-- Invoice Title -->
          <v-row>
            <v-col cols="12" class="text-center">
              <strong style="font-size: 24px">Invoice</strong>
            </v-col>
          </v-row>

          <!-- Invoice Info -->
          <v-row class="mt-3">
            <v-col cols="6">
              <div><strong>Bill To:</strong> {{ memberObject?.full_name }}</div>

              <div>
                <strong>Start Date:</strong>
                {{ memberObject?.last_membership?.show_plan_start_date }}
              </div>
              <div>
                <strong>End Date:</strong>
                {{ memberObject?.last_membership?.show_plan_end_date }}
              </div>
            </v-col>
            <v-col cols="6" class="text-right">
              <div>
                <strong>Invoice #:</strong> INV-{{
                  memberObject?.last_membership?.id < 1000
                    ? 1000 + memberObject?.last_membership?.id
                    : memberObject?.last_membership?.id
                }}
              </div>
              <div><strong>Date:</strong> {{ new Date().toDateString() }}</div>
              <div>
                <strong>Plan Name:</strong>
                {{ memberObject?.last_membership.plan?.name }}
              </div>
            </v-col>
          </v-row>

          <v-row>
            <v-col>
              <strong>Description:</strong>
              {{ memberObject?.last_membership.plan?.description }}
            </v-col>
          </v-row>

          <!-- Plan Description -->

          <!-- Payment Table -->
          <v-simple-table class="mt-10" dense>
            <thead>
              <tr>
                <th class="text-left" style="width: 250px">Plan</th>
                <th class="text-right">Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ memberObject?.last_membership.plan?.name }}</td>
                <td class="text-right">
                  {{
                    $utils.currency_format(
                      memberObject?.last_membership.plan?.price
                    )
                  }}
                </td>
                <td class="text-right">
                  {{
                    $utils.currency_format(memberObject?.last_payment?.discount)
                  }}
                </td>
                <td class="text-right">
                  {{
                    $utils.currency_format(
                      memberObject?.last_payment?.paid_amount
                    )
                  }}
                </td>
                <td class="text-right">
                  {{
                    $utils.currency_format(memberObject?.last_payment?.balance)
                  }}
                </td>
              </tr>

              <tr>
                <td colspan="5" class="text-right font-weight-bold">
                  Total
                  {{
                    $utils.currency_format(memberObject?.last_payment?.total)
                  }}
                </td>
              </tr>
            </tbody>
          </v-simple-table>

          <!-- Payment Info -->
          <v-row class="mt-4">
            <v-col cols="12">
              <div><strong>Payment Mode:</strong> Cash</div>
              <div><strong>Payment Ref:</strong> ---</div>
            </v-col>
          </v-row>

          <!-- Footer -->
          <v-row>
            <v-col cols="12" class="text-center text-caption">
              <div style="margin-top: 320px">
                Thank you for choosing Gym Pro!
              </div>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
<script>
import html2canvas from "html2canvas";
import jsPDF from "jspdf";
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
  methods: {
    can(per) {
      return this.$pagePermission.can(per, this);
    },

    async print() {
      const el = document.getElementById("target");
      const card = el.closest(".v-card");

      // Backup styles
      const prevMaxHeight = card.style.maxHeight;
      const prevOverflow = card.style.overflow;

      // Temporarily expand the card
      card.style.maxHeight = "none";
      card.style.overflow = "visible";

      // Force reflow before html2canvas
      setTimeout(() => {
        html2canvas(el, {
          useCORS: true,
          scale: 3,
          scrollY: -window.scrollY,
          windowWidth: document.documentElement.scrollWidth,
          windowHeight: el.scrollHeight,
        }).then((canvas) => {
          const imgData = canvas.toDataURL("image/png");

          const pdf = new jsPDF("p", "mm", "a4"); // ✅ Portrait
          const imgWidth = 210; // A4 width in mm (portrait)
          const imgHeight = (canvas.height * imgWidth) / canvas.width;

          pdf.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
          pdf.save("performance-report.pdf");

          // Revert styles
          card.style.maxHeight = prevMaxHeight;
          card.style.overflow = prevOverflow;
        });
      }, 100);
    },
  },
};
</script>
