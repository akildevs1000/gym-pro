<template>
  <v-dialog persistent v-model="viewDialog" max-width="700px">
    <WidgetsClose left="690" @click="viewDialog = false" />
    <template v-slot:activator="{ on, attrs }">
      <span v-bind="attrs" v-on="on">
        <v-icon color="text" small>mdi-printer-outline</v-icon>
      </span>
    </template>

    <v-card flat light>
      <v-alert dense class="primary white--text">
        <v-row>
          <v-col cols="6"> Payment Receipt </v-col>
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

          <v-row>
            <v-col cols="12" class="text-center">
              <strong style="font-size: 24px">Payment Receipt</strong>
            </v-col>
          </v-row>

          <v-row class="mt-3">
            <v-col cols="12" class="text-right">
              <div><strong>Payment Receipt #:</strong> TRN-{{ item?.id }}</div>
              <div>
                <strong>Pritned On: </strong> {{ new Date().toDateString() }}
              </div>
            </v-col>
          </v-row>

          <!-- Plan Description -->

          <!-- Payment Table -->
          <v-simple-table class="mt-10" dense>
            <tbody>
              <tr>
                <th style="width: 50%">Amount</th>
                <td>
                  {{ $utils.currency_format(item?.paid_amount) }}
                </td>
              </tr>
              <tr>
                <th style="width: 50%">Description</th>
                <td>{{ item?.description }}</td>
              </tr>
              <tr>
                <th style="width: 50%">Mode</th>
                <td>{{ item?.mode?.name }}</td>
              </tr>
              <tr>
                <th style="width: 50%">Mode Ref</th>
                <td>{{ item?.payment_mode_ref || "---" }}</td>
              </tr>
              <tr>
                <th style="width: 50%">Date Time</th>
                <td>{{ item?.datetime }}</td>
              </tr>
            </tbody>
          </v-simple-table>

          <!-- Footer -->
          <v-row>
            <v-col cols="12" class="text-center text-caption">
              <div style="margin-top: 420px">
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
  props: ["id", "item"],
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
          pdf.save(`Payment-Receipt-${this.item.id}.pdf`);

          // Revert styles
          card.style.maxHeight = prevMaxHeight;
          card.style.overflow = prevOverflow;
        });
      }, 100);
    },
  },
};
</script>
