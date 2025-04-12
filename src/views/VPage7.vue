<template>
  <!--  eslint-disable  -->
  <h1>Сервіс v7</h1>

  <div class="card flex justify-center">
    <PanelMenu :model="items" class="w-full md:w-80" />
  </div>

  <div class="card flex justify-center">
    <h1>Перевірка</h1>
    <InputText v-model="text" />
    <Button label="Save" icon="pi pi-user" iconPos="top" />
    <p>{{ text }}</p>
  </div>

  <!-- <div class="card flex justify-center">
    <DatePicker v-model="date" />
  </div> -->
  <div class="card flex justify-center"></div>
</template>

<script>
/* eslint-disable */
import "primeicons/primeicons.css";
import "@/prime/style.css";

import PanelMenu from "primevue/panelmenu";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
// import DatePicker from "primevue/datepicker";

import { ref } from "vue";

export default {
  components: {
    PanelMenu,
    InputText,
    Button,
    // DatePicker,
  },
  data() {
    return {
      text: "",
      date: "",
    };
  },
  setup(props) {
    const items = ref([
      {
        label: "Файли",
        icon: "pi pi-file",
        items: [
          {
            label: "Documents",
            icon: "pi pi-file",
            items: [
              {
                label: "Invoices",
                icon: "pi pi-file-pdf",
                items: [
                  {
                    label: "Pending",
                    icon: "pi pi-stop",
                  },
                  {
                    label: "Paid",
                    icon: "pi pi-check-circle",
                  },
                ],
              },
              {
                label: "Clients",
                icon: "pi pi-users",
              },
            ],
          },
          {
            label: "Images",
            icon: "pi pi-image",
            items: [
              {
                label: "Logos",
                icon: "pi pi-image",
              },
            ],
          },
        ],
      },
      {
        label: "Cloud",
        icon: "pi pi-cloud",
        items: [
          {
            label: "Upload",
            icon: "pi pi-cloud-upload",
          },
          {
            label: "Download",
            icon: "pi pi-cloud-download",
          },
          {
            label: "Sync",
            icon: "pi pi-refresh",
          },
        ],
      },
      {
        label: "Devices",
        icon: "pi pi-desktop",
        items: [
          {
            label: "Phone",
            icon: "pi pi-mobile",
          },
          {
            label: "Desktop",
            icon: "pi pi-desktop",
          },
          {
            label: "Tablet",
            icon: "pi pi-tablet",
          },
        ],
      },
    ]);

    return {
      items,
    };
  },
};
</script>

<!--  eslint-disable  -->
<style scoped>
h1 {
  align-self: center;
  margin: 20px;
}
</style>
