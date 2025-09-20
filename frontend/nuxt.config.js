import colors from "vuetify/es5/util/colors";

export default {
  buildDir: ".nuxt",
  // Target: https://go.nuxtjs.dev/config-target
  target: "static",
  generate: {
    // Interval in milliseconds between two render cycles to avoid
    // flooding a potential API with calls from the web application.
    interval: 500,
  },
  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    titleTemplate: "",
    title: "MyTime 2 Desktop",
    meta: [
      { charset: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { hid: "description", name: "description", content: "" },
      { name: "format-detection", content: "telephone=no" },
    ],
    css: [],

    link: [{ rel: "icon", type: "image/x-icon", href: "/favicon.ico" }],
    script: [],
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  // css: ["~/assets/styles"],

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [
    "~/plugins/qrcode.js",
    "~/plugins/custom-methods.js",
    { src: "~/plugins/crypto.js", mode: "client" },
    { src: "~/plugins/axios.js" },
    { src: "~/plugins/TiptapVuetify", mode: "client" },
    { src: "~/plugins/vue-apexchart.js", ssr: false },
  ],

  // Auto import components: https://go.nuxtjs.dev/config-components
  components: true,

  // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
  buildModules: [
    // https://go.nuxtjs.dev/vuetify
    "@nuxtjs/vuetify",
    "@nuxtjs/dotenv",
  ],

  // Modules: https://go.nuxtjs.dev/config-modules
  modules: [
    // https://go.nuxtjs.dev/axios
    "@nuxtjs/axios",
    // https://go.nuxtjs.dev/pwa
    "@nuxtjs/pwa",
    "@nuxtjs/auth-next",
  ],

  // Axios module configuration: https://go.nuxtjs.dev/config-axios
  axios: {
    baseURL: `/`,
  },

  auth: {
    strategies: {
      local: {
        endpoints: {
          login: { url: "login", method: "post", propertyName: "token" },
          logout: false,
          user: { url: "me", method: "get", propertyName: false },
        },
        //////////maxAge: 86400, // 24 hours

        refreshToken: true,

        token: {
          //property: "tokens.access.token",
          global: true,
          type: "Bearer",
          maxAge: 60 * 60 * 24 * 365, // 8 Hours
        },

        autoLogout: false,
      },
    },

    // redirect: {
    //   logout: "/login",
    // },
  },

  router: {
    middleware: ["auth"],
  },


  pwa: {
    manifest: {
      name: "MyTime 2 Cloud",
      short_name: "MyTime 2 Cloud",
      lang: "en",
    },
    icon: {
      source: "icon-515x512.png", // Path to your app icon
    },
  },

  // Vuetify module configuration: https://go.nuxtjs.dev/config-vuetify

  vuetify: {
    // customVariables: ["~/assets/variables.scss"],
    theme: {
      dark: true, // Start in dark mode; toggle later via $vuetify.theme.dark = true/false
      treeShake: true,
      themes: {
        options: {
          customProperties: true,
        },
        dark: {
          primary: "#6c63ff",         // Navy Blue
          secondary: "#2c3e50",       // Slightly lighter navy
          accent: "#3d5afe",          // Electric blue accent
          background: "#2c3e50",      // Very dark navy
          main_bg: "#0d152a",         // App background
          surface: "#1b263b",         // Dialogs/cards
          popup_background: "#1c2533", // Popups

          // Text
          text: "#e0e6ed",
          text_secondary: "#aab8c2",

          // Alerts
          info: "#00bcd4",
          warning: "#ffa000",
          error: "#ef5350",
          success: "#4caf50",

          // Custom
          glow: "#3d5afe",
          violet: "#6c63ff",

          input_bg: "#374151",
          dialog_bg: "#1f2937"
        },
        light: {
          primary: "#6946dd",         // Violet (your original)
          secondary: "#242424",       // Dark text
          accent: "#d8363a",          // Vibrant red
          background: "#f9f9f9",      // Light background
          main_bg: "#ECF0F4",         // Main light bg
          surface: "#ffffff",         // Cards/dialogs
          popup_background: "#ecf0f4",

          // Text
          text: "#212121",
          text_secondary: "#757575",

          // Alerts
          info: "#0097a7",
          warning: "#ffa000",
          error: "#d32f2f",
          success: "#388e3c",

          // Custom
          glow: "#6946dd",
          violet: "#6946dd",
          input_bg: "#374151",
          dialog_bg: "#1f2937"
        }
      }
    }
  },

  // Build Configuration: https://go.nuxtjs.dev/config-build
  build: {
    transpile: ["vuetify/lib", "tiptap-vuetify", "vue-apexchart"],
    interval: 500,
  },
};
