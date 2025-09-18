<template>
  <v-container fluid>
    <div v-if="can('product_access')">
      <div class="text-center ma-2">
        <v-snackbar v-model="snackbar" small top="top" :color="color">
          {{ response }}
        </v-snackbar>
        <v-snackbar v-model="snack">
          {{ snackText }}

          <template v-slot:action="{ attrs }">
            <v-btn v-bind="attrs" text @click="snack = false"> Close </v-btn>
          </template>
        </v-snackbar>
      </div>

      <v-dialog persistent v-model="dialogCropping" width="500">
        <v-card style="padding-top: 20px" class="background">
          <v-card-text>
            <VueCropper v-show="selectedFile" ref="cropper" :src="selectedFile" alt="Source Image" :aspectRatio="1"
              :autoCropArea="0.9" :viewMode="3"></VueCropper>
          </v-card-text>

          <v-card-actions>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 pull-left">
              <v-btn class="danger btn btn-danger text-left" text @click="closePopup()"
                style="float: left">Cancel</v-btn>
            </div>
            <div col="6" md="6" class="col-sm-12 col-md-6 col-12 text-right">
              <v-btn class="primary btn btn-danger text-right"
                @click="saveCroppedImageStep2(), (dialog = false)">Crop</v-btn>
            </div>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog persistent v-model="productDialog" width="600">
        <WidgetsClose left="590" @click="close" />
        <v-card class="background">
          <v-alert flat dense class="primary white--text">
            {{ editedIndex == -1 ? "Add" : "Edit" }} {{ Model }}
          </v-alert>
          <v-card-text>
            <v-container class="pa-7">
              <v-row>
                <v-col cols="6">
                  <v-row>
                    <v-col md="12" sm="12" cols="12" dense>
                      <v-text-field label="Name" dense outlined :hide-details="!errors.name" type="text"
                        v-model="item.name" :error="errors.name" :error-messages="
                          errors && errors.name ? errors.name[0] : ''
                        "></v-text-field>
                    </v-col>

                    <v-col md="12" sm="12" cols="12" dense>
                      <v-text-field label="Price" dense outlined :hide-details="!errors.price" type="text"
                        v-model="item.price" :error="errors.price" :error-messages="
                          errors && errors.price ? errors.price[0] : ''
                        "></v-text-field>
                    </v-col>

                    <v-col md="12" sm="12" cols="12" dense>
                      <v-textarea rows="2" label="Description" dense outlined :hide-details="!errors.description"
                        type="text" v-model="item.description" :error="errors.description" :error-messages="
                          errors && errors.description
                            ? errors.description[0]
                            : ''
                        "></v-textarea>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="6">
                  <div style="margin: 0 auto; width: 150px">
                    <v-img style="width: 100%; height: 150px; margin: 0 auto" :src="
                        previewImage ||
                        'https://miro.medium.com/v2/resize:fit:600/0*jGmQzOLaEobiNklD'
                      "></v-img>
                    <br />

                    <input required type="file" @change="attachment" style="display: none" accept="image/*"
                      ref="attachment_input" />

                    <span v-if="errors && errors.image" class="text-danger mt-2">{{ errors.image[0] }}</span>
                  </div>
                  <div class="text-center">
                    <v-btn small class="primary" @click="onpick_attachment">{{ !upload.name ? "Upload" : "Change" }}
                      Product Image
                      <v-icon right dark>mdi-cloud-upload</v-icon>
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn v-if="can('product_create')" small :loading="loading" color="primary" @click="submit">
              Submit
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <NoAccess v-else />
    <v-card class="background">
      <v-card-text>
        <v-data-table class="background" elevation="0" dense :headers="headers_table" :items="data"
          model-value="data.id" :loading="loadinglinear" :options.sync="options" :footer-props="{
              itemsPerPageOptions: [100, 500, 1000],
            }" :server-items-length="totalRowsCount">
          <template v-slot:top>
            <v-row>
              <v-col>
                <span> Product</span>
                <v-tooltip top color="primary">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn dense class="ma-0 px-0" x-small :ripple="false" text v-bind="attrs" v-on="on"
                      @click="getDataFromApi()">
                      <v-icon class="ml-2" dark>mdi mdi-reload</v-icon>
                    </v-btn>
                  </template>
                  <span>Reload</span>
                </v-tooltip>
              </v-col>

              <v-col class="text-right">
                <v-btn dense color="primary" small @click="openNewPage()" class="white--text">
                  <v-icon color="white" small> mdi-plus </v-icon>
                  Product
                </v-btn>
              </v-col>
            </v-row>
          </template>
          <template v-slot:item.image="{ item, index }" style="width: 300px">
            <v-row no-gutters class="align-center pa-3">
              <v-col cols="auto" style="
                    padding: 5px;
                    padding-left: 0px;
                    width: auto;
                    max-width: none;
                  " class="d-flex align-center">
                <v-avatar size="50" class="me-2" tile>
                  <v-img :src="
                        item.image
                          ? item.image
                          : 'https://miro.medium.com/v2/resize:fit:600/0*jGmQzOLaEobiNklD'
                      "></v-img>
                </v-avatar>
              </v-col>
            </v-row>
          </template>
          <template v-slot:item.options="{ item }">
            <v-menu bottom left>
              <template v-slot:activator="{ on, attrs }">
                <v-btn dark-2 icon v-bind="attrs" v-on="on">
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list dense class="background">
                <v-list-item v-if="can('product_edit')">
                  <v-list-item-title style="cursor: pointer" @click="editItem(item)">
                    <v-icon small color="text">mdi-pencil</v-icon> Edit
                  </v-list-item-title>
                </v-list-item>

                <v-list-item v-if="can('product_delete')" @click="deleteItem(item)">
                  <v-list-item-title style="cursor: pointer">
                    <v-icon color="error" small> mdi-delete </v-icon>
                    Delete
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import "cropperjs/dist/cropper.css";
import VueCropper from "vue-cropperjs";

export default {
  head() {
    return {
      link: [
        {
          rel: "stylesheet",
          href: "~/assets/source-sans-pro.css", // Adjust the path if needed
        },
      ],
    };
  },
  components: {
    VueCropper,
  },

  data: () => ({
    totalRowsCount: 0,
    refresh: true,
    filters: {},
    snack: false,
    snackText: "",
    loadinglinear: true,
    image: "",
    cropedImage: "",
    cropper: "",
    autoCrop: false,
    dialogCropping: false,
    selectedFile: "",
    productDialog: false,
    loading: false,
    //total: 0,
    per_page: 1000,
    color: "background",
    response: "",
    snackbar: false,
    btnLoader: false,
    item: {
      name: "",
      description: "",
      price: "",
    },
    upload: {
      name: "",
    },
    previewImage: null,
    options: {},
    Model: "Product",
    endpoint: "products",
    snackbar: false,
    loading: false,
    //total: 0,
    editedIndex: -1,
    response: "",
    data: [],
    errors: [],
    key: 1,
    headers_table: [
      {
        text: "Image",
        align: "left",
        sortable: true,
        key: "image",
        value: "image",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Name",
        align: "left",
        sortable: true,
        key: "name",
        value: "name",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Price",
        align: "left",
        sortable: true,
        key: "price",
        value: "price",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Description",
        align: "left",
        sortable: true,
        key: "description",
        value: "description",
        filterable: true,
        filterSpecial: false,
      },
      {
        text: "Options",
        align: "left",
        sortable: false,
        key: "options",
        value: "options",
      },
    ],
    refresh: false,
    image_name: null,
  }),

  async created() {
    this.loading = false;
    await this.getDataFromApi();
  },
  mounted() {},
  computed: {},
  watch: {
    options: {
      async handler() {
        await this.getDataFromApi();
      },
      deep: true,
    },
  },
  methods: {
    async handleEventFromChild() {
      this.refresh = true;
      await this.getDataFromApi();
    },
    getDefaultPayload() {
      return {
        name: "",
        description: "",
        price: "",
      };
    },
    editItem({ image, ...rest }) {
      this.previewImage = image;
      this.editedIndex = 0;
      this.item = rest;
      this.productDialog = true;
    },
    async openNewPage() {
      this.editedIndex = -1;
      this.item = this.getDefaultPayload(); // ✅ reset with default structure
      this.productDialog = true;
    },
    closePopup() {
      //croppingimagestep5
      this.$refs.attachment_input.value = null;
      this.dialogCropping = false;
    },
    saveCroppedImageStep2() {
      this.cropedImage = this.$refs.cropper.getCroppedCanvas().toDataURL();

      this.image_name = this.cropedImage;
      this.previewImage = this.cropedImage;

      this.dialogCropping = false;
    },
    close() {
      this.item = {};
      this.productDialog = false;
      this.errors = [];
    },
    can(per) {
      return this.$pagePermission.can(per, this);
    },
    async getDataFromApi() {
      this.loadinglinear = true;

      let config = { params: { ...this.filters, ...this.options } };

      let { data } = await this.$axios.get("products", config);

      this.data = data.data;
      this.totalRowsCount = data.total;
      this.loadinglinear = false;
    },
    deleteItem(item) {
      confirm(
        "Are you sure you wish to delete , to mitigate any inconvenience in future."
      ) &&
        this.$axios
          .delete(`${this.endpoint}/${item.id}`)
          .then(async ({ data }) => {
            if (!data.status) {
              this.errors = data.errors;
            } else {
              this.refresh = true;
              await this.getDataFromApi();
              this.snackbar = data.status;
              this.response = data.message;
            }
          })
          .catch((err) => console.log(err));
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },
    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;

      if (file[0].size > 1024 * 1024) {
        e.preventDefault();
        this.errors["image"] = ["File too big (> 1MB). Upload less than 1MB"];
        return;
      }

      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.selectedFile = event.target.result;

          this.$refs.cropper.replace(this.selectedFile);
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);

        this.dialogCropping = true;
      }
    },
    mapper(obj) {
      let item = new FormData();

      for (let x in obj) {
        item.append(x, obj[x]);
      }
      item.append("image", this.upload.name);

      return item;
    },
    submit() {
      const originalData = Object.assign({}, this.item);
      const formData = this.mapper(originalData);

      const file = this.$refs.attachment_input?.files?.[0];
      const isNew = this.editedIndex === -1;

      const submitForm = (data) => {
        isNew ? this.store(data) : this.update(data);
      };

      if (!file) {
        submitForm(formData);
        return;
      }

      const croppedCanvas = this.$refs.cropper.getCroppedCanvas();
      this.cropedImage = croppedCanvas.toDataURL();

      croppedCanvas.toBlob((blob) => {
        if (!blob) {
          console.error("Image blob creation failed");
          return;
        }

        // Append cropped image to FormData
        formData.append("image", blob, "cropped_image.jpg");
        formData.append("attachment_input", blob, "cropped_image.jpg");

        submitForm(formData);
      }, "image/jpeg");
    },
    async store(item) {
      this.$axios
        .post("/products", item)
        .then(async ({ data }) => {
          this.processResponse("Product Inserted successfully", data);
        })
        .catch((e) => console.log(e));
    },

    async update(item) {
      this.$axios
        .post(`/products-update/${this.item.id}`, item)
        .then(async ({ data }) => {
          this.loading = false;
          this.processResponse("Product Updated successfully", data);
        })
        .catch((e) => console.log(e));
    },

    async processResponse(res, data) {
      if (!data.status) {
        this.errors = data.errors;
      } else {
        this.errors = [];
        this.snackbar = true;
        this.response = res;
        this.productDialog = false;
        await this.getDataFromApi();
      }
    },
  },
};
</script>
