<template>
  <el-space style="margin: 0 0 10px 0">
    <div class="card">
      <el-row :gutter="10" wrap>
        <!-- Менеджер @change="getMonitoring"-->
        <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
          <el-select
            v-model="checkManeger"
            multiple
            clearable
            collapse-tags
            placeholder="оберіть менеджера..."
            popper-class="custom-header"
            :max-collapse-tags="1"
            style="width: 100%"
          >
            <template #header>
              <el-checkbox v-model="checkAll" :indeterminate="indeterminate" @change="handleCheckAll">
                Усі
              </el-checkbox>
            </template>
            <el-option
              v-for="item in listManeger"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-col>

        <!-- Матеріал  @change="getMonitoring" -->
        <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
          <el-cascader
            v-model="checkMaterial"
            :options="options"
            :props="propsCascader"
            clearable
            collapse-tags
            placeholder="оберіть матеріал..."
            :max-collapse-tags="2"
            style="width: 100%"
          />
        </el-col>

        <!-- формування усіх/або тільки ВП або тільки КЛ -->
        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
          <el-radio-group>
            <el-radio-button label="усі" value="all" />
            <el-radio-button label="ВП" value="sz" />
            <el-radio-button label="КС" value="zm" />
          </el-radio-group>
        </el-col>
      </el-row>
      <el-row>
        <!-- Період формування -->
        <el-col :xs="10" :sm="10" :md="10" :lg="1" :xl="12">
          <div style="text-align: center">
            <el-switch v-model="isPeriod" />
            <el-date-picker
              v-model="valueDate"
              type="daterange"
              format="DD.MM.YYYY"
              :start-placeholder="getDate"
              :end-placeholder="getDate"
              :disabled="!isPeriod"
              :shortcuts="shortCuts"
              style="width: 210px; margin-left: 5px"
              @change="getETransaction"
            />
          </div>
        </el-col>

        <!-- Розкрити/закрити підкатегорію по номенклатурі -->
        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
          <el-radio-group style="margin-left: 5px; width: 100%">
            <el-radio-button value="false" label="+К" />
            <el-radio-button value="true" label="-К" />
          </el-radio-group>
        </el-col>
      </el-row>
    </div>
  </el-space>
</template>

<script setup>
import { ref, computed, onActivated, watch } from "vue";
import { useStore } from "vuex";
// import { User, Refresh, Tickets } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

// const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);

const checkAll = ref(false);
const indeterminate = ref(false);
const checkManeger = ref([]);
const checkMaterial = ref([]);
const options = ref([]);
const propsCascader = { multiple: true, expandTrigger: "hover" };
const listManeger = ref([]);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(true);

watch(checkManeger, (val) => {
  if (val.length === 0) {
    checkAll.value = false;
    indeterminate.value = false;
  } else if (val.length === listManeger.value.length) {
    checkAll.value = true;
    indeterminate.value = false;
  } else {
    indeterminate.value = true;
  }
});

const getManeger = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getManeger",
        _id_U: getCurUser.value.id,
      },
    });

    listManeger.value = response.data.ar_data;

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Список менеджерів оновлений");
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження менеджеров");
  }
};

const getKategories = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getKategories",
      },
    });

    options.value = response.data
      .map((el) => {
        const newChildren = el.listMaterial.map((mat) => {
          return { value: mat.id_M, label: mat.name_M };
        });
        return { value: el.id_K, label: el.name_K, children: newChildren };
      })
      .filter((el) => {
        return el.value != 1 && el.value != 6;
      });

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Категорії оновлені");
    }
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const handleCheckAll = (val) => {
  indeterminate.value = false;
  if (val) {
    checkManeger.value = listManeger.value.map((_) => _.value);
  } else {
    checkManeger.value = [];
  }
  // getMonitoring();
};

// const formatDate = (valDate, mode = "ukr") => {
//   const date = {
//     d: valDate.getDate(),
//     m: valDate.getMonth() + 1,
//     y: valDate.getFullYear(),
//   };
//   return mode == "ukr"
//     ? [(date.d < 10 ? "0" : "") + date.d, (date.m < 10 ? "0" : "") + date.m, date.y].join(".")
//     : [date.y, (date.m < 10 ? "0" : "") + date.m, (date.d < 10 ? "0" : "") + date.d].join("-");
// };

onActivated(async () => {
  await getManeger();
  await getKategories();
  // await getMonitoring();
});
</script>

<style scoped></style>
