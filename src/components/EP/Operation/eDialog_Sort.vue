<template>
  <el-dialog
    :modelValue="props.visible"
    title="Пересортування матеріалів"
    :before-close="closeForm"
    :close-on-click-modal="false"
    :width="getWidth[0]"
  >
    <el-form :model="form" label-width="auto">
      <el-form-item label="Пункт / прізвище">
        <el-col :span="15">
          <el-tag type="primary" style="width: 100%">{{
            form.namePunkt
          }}</el-tag>
        </el-col>
        <el-col :span="1" style="text-align: center">
          <span>/</span>
        </el-col>
        <el-col :span="8">
          <el-tag type="primary">{{ form.nameUser }}</el-tag>
        </el-col>
      </el-form-item>
      <el-form-item label="Дата операції">
        <el-col :span="11">
          <el-date-picker
            v-model="form.date"
            type="date"
            format="DD.MM.YYYY"
            value-format="YYYY-MM-DD"
            :placeholder="getDate"
            style="width: 100%"
          />
        </el-col>
        <el-col :span="2" style="text-align: center">
          <span>-</span>
        </el-col>
        <el-col :span="11">
          <el-tag type="info" size="large" style="font-size: 13pt">{{
            getTime
          }}</el-tag>
        </el-col>
      </el-form-item>

      <el-form-item label="Категорія для сортування">
        <el-select v-model="form.name_K">
          <el-option
            v-for="item in sourceTable_K"
            :key="item.name_K"
            :label="item.name_K"
            :value="item.name_K"
          >
          </el-option>
        </el-select>

        <el-card style="width: 100%">
          <el-row>
            <el-col :span="10">
              <el-select v-model="form.name_M_old">
                <el-option
                  v-for="item in sourceTable_M"
                  :key="item.name_M"
                  :label="item.name_M"
                  :value="item.name_M"
                ></el-option>
              </el-select>
            </el-col>
            <el-col :span="3">
              <el-icon style="font-size: 18pt"><Right /></el-icon>
            </el-col>
            <el-col :span="10">
              <el-select v-model="form.name_M_new">
                <el-option
                  v-for="item in sourceTable_M"
                  :key="item.name_M"
                  :label="item.name_M"
                  :value="item.name_M"
                >
                </el-option>
              </el-select>
            </el-col>
            <el-col :span="11">
              <el-input-number
                v-model="count_Sort"
                :precision="3"
                :step="1"
                :max="500"
                :min="0"
              />
            </el-col>
          </el-row>
        </el-card>
      </el-form-item>

      <el-form-item label="Коментар">
        <el-input v-model="form.comment" type="textarea" />
      </el-form-item>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="clearForm" :disabled="!form.isSave"
          >Очистити</el-button
        >
        <el-button @click="closeForm">Вийти</el-button>
        <el-button type="primary" @click="addTransactionPeresort"
          >Зберегти</el-button
        >
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import {
  defineProps,
  inject,
  computed,
  defineEmits,
  reactive,
  onUpdated,
  onUnmounted,
} from "vue";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const emit = defineEmits([]);
const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const props = defineProps({
  visible: Boolean,
  namePunkt: String,
  idPunkt: Number,
});
const form = reactive({
  nameUser: getCurUser.value.PIB,
  namePunkt: "",
  date: "",
  count_Sort: 0,
  comment: "",
  name_K: "",
  name_M_new: "",
  name_M_old: "",
  timer: {},
  curDate: new Date(),
});

const getWidth = computed(() => {
  return [
    setting.value.displaySize == "large" ? "800px" : "600px",
    setting.value.displaySize == "large" ? "200px" : "180px",
  ];
});

const getDate = computed(() => {
  const date = {
    d: form.curDate.getDate(),
    m: form.curDate.getMonth() + 1,
    y: form.curDate.getFullYear(),
  };
  return [
    (date.d < 10 ? "0" : "") + date.d,
    (date.m < 10 ? "0" : "") + date.m,
    date.y,
  ].join(".");
});

const getTime = computed(() => {
  const time = {
    h: form.curDate.getHours(),
    m: form.curDate.getMinutes(),
    s: form.curDate.getSeconds(),
  };
  return [
    (time.h < 10 ? "0" : "") + time.h,
    (time.m < 10 ? "0" : "") + time.m,
    (time.s < 10 ? "0" : "") + time.s,
  ].join(":");
});

const clearForm = () => {
  form.comment = "";
};

const closeForm = () => {
  emit("update:visible", false);
};

const sourceTable_K = computed(() => {
  const _tabK = setting.value.tables["tabKategories"].data;
  return _tabK.filter((row) => {
    return +row.id_K == 3 || +row.id_K == 4;
  });
});

const sourceTable_M = computed(() => {
  const _tab = setting.value.tables["tabMaterial"].data;

  return _tab.filter((row) => {
    return row.name_K == form.name_K;
  });
});

const getKategories = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getKategories",
      },
    });

    setting.value.tables["tabKategories"].data = response.data;
    ElMessage.success("Категоріїї оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const getMaterial = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getMaterial",
      },
    });

    setting.value.tables["tabMaterial"].data = response.data;
    ElMessage.success("Матеріали оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addTransactionPeresort = async () => {
  try {
    const id_M_old = setting.value.tables["tabMaterial"].data.find((el) => {
      return form.name_M_old == el.name_M;
    }).id_M;
    const id_M_new = setting.value.tables["tabMaterial"].date.find((el) => {
      return form.name_M_new == el.name_M;
    }).id_M;
    const groupOperation = [
      {
        id_V: 6,
        id_M: id_M_old,
        d_count: form.count_Sort,
        d_price: 0,
        old_count: 0,
        old_price: 0,
        new_count: 0,
        new_price: 0,
      },
      {
        id_V: 7,
        id_M: id_M_new,
        d_count: form.count_Sort,
        d_price: 0,
        old_count: 0,
        old_price: 0,
        new_count: 0,
        new_price: 0,
      },
    ];

    const response = await HTTP.post("", {
      _method: "addTransaction",
      _idUser: getCurUser.value.id,
      _idPunkt: props.idPunkt,
      _date: form.date,
      _time: getTime.value,
      _comment: form.comment,
      _opers: groupOperation,
    });

    if (response.data.isSuccesfull) {
      emit("update:visible", false);
      ElMessage.success(response.data.message);
    } else {
      ElMessage.error(response.data.message);
    }
  } catch (e) {
    ElMessage.error("Помилка збереження транзакції");
  }
};

const startTimer = () => {
  form.timer = setInterval(() => {
    form.curDate = new Date();
  }, 1000);
};

onUpdated(async () => {
  startTimer();
  form.namePunkt = props.namePunkt;
  form.date = form.curDate;

  await getKategories();
  form.name_K = setting.value.tables["tabKategories"].data[2].name_K;

  await getMaterial();
  form.name_M_old = setting.value.tables["tabMaterial"].data[6].name_M;
  form.name_M_new = setting.value.tables["tabMaterial"].data[4].name_M;
});

onUnmounted(() => {
  clearInterval(form.timer);
});
</script>

<style></style>
