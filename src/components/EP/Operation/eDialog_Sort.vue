<template>
  <el-dialog
    :modelValue="props.visible"
    :title="form.title"
    :before-close="closeForm"
    :close-on-click-modal="false"
    :width="getWidth[0]"
  >
    <el-form :model="form" label-width="auto">
      <!-- <el-form-item label="Пункт / прізвище">
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
      </el-form-item> -->

      <el-form-item label="Дата операції">
        <el-col :span="5">
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

        <el-col :span="17">
          <el-tag type="info" size="large" style="font-size: 13pt">{{
            getTime
          }}</el-tag>
        </el-col>
      </el-form-item>

      <el-form-item label="Категорія">
        <el-select v-model="form.name_K">
          <el-option
            v-for="item in sourceTable_K"
            :key="item.name_K"
            :label="item.name_K"
            :value="item.name_K"
          >
          </el-option>
        </el-select>
      </el-form-item>

      <el-form-item label=" ">
        <el-card style="width: 100%">
          <el-row>
            <el-col :span="11">
              <el-badge
                :value="countPeresortOld"
                style="width: 100%"
                :offset="[-40, -3]"
                color="#499efc"
              >
                <el-select v-model="form.name_M_old">
                  <el-option
                    v-for="item in sourceTable_M_old"
                    :key="item.name_M"
                    :label="item.name_M"
                    :value="item.name_M"
                  ></el-option>
                </el-select>
              </el-badge>
            </el-col>

            <el-col :span="2">
              <el-icon
                style="
                  font-size: 20pt;
                  top: 50%;
                  left: 50%;
                  transform: translate(-50%, -50%);
                "
                ><Right
              /></el-icon>
            </el-col>

            <el-col :span="11">
              <el-badge
                :value="countPeresortNew"
                style="width: 100%"
                :offset="[-40, -3]"
                color="#499efc"
              >
                <el-select v-model="form.name_M_new">
                  <el-option
                    v-for="item in sourceTable_M_new"
                    :key="item.name_M"
                    :label="item.name_M"
                    :value="item.name_M"
                  >
                  </el-option>
                </el-select>
              </el-badge>
            </el-col>
          </el-row>

          <el-row>
            <el-col :span="isLessening ? 12 : 24">
              <el-input-number
                v-model="form.count_Sort"
                :precision="3"
                :step="1"
                :max="countPeresortOld"
                :min="0"
                style="top: 50%; left: 50%; transform: translate(-50%, -50%)"
              />
            </el-col>
            <el-col :span="12" v-if="isLessening">
              <el-input-number
                v-model="form.count_Sort_New"
                :precision="3"
                :step="1"
                :max="form.count_Sort"
                :min="0"
                style="top: 50%; left: 50%; transform: translate(-50%, -50%)"
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
        <el-button @click="clearForm">Очистити</el-button>
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
  watch,
} from "vue";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const emit = defineEmits([]);
const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
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
  count_Sort_New: 0,
  comment: "",
  name_K: "",
  name_M_new: "",
  name_M_old: "",
  timer: {},
  curDate: new Date(),
  title: "",
});

watch(
  () => form.name_M_old,
  () => {
    form.count_Sort = 0;
  }
);

watch(
  () => form.name_K,
  () => {
    form.name_M_old = "зробіть вибір...";
    form.name_M_new = "зробіть вибір...";
  }
);

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
  form.name_M_old = "зробіть вибір...";
  form.name_M_new = "зробіть вибір...";
  form.count_Sort = 0;
};

const closeForm = () => {
  clearForm();
  emit("update:visible", false);
};

const startTimer = () => {
  form.timer = setInterval(() => {
    form.curDate = new Date();
  }, 1000);
};

const getKategories = async () => {
  if (!setting.value.dialog["createPeresort"].visible) return;

  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getKategories",
      },
    });

    setting.value.tables["tabKategories"].data = response.data;
    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Категоріїї оновлені");
    }
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const getMaterial = async () => {
  if (!setting.value.dialog["createPeresort"].visible) return;

  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getMaterial",
        _idPunkt: props.idPunkt,
      },
    });

    setting.value.tables["tabMaterial"].data = response.data;
    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Матеріали оновлені");
    }
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addTransactionPeresort = async () => {
  try {
    const M_old = setting.value.tables["tabMaterial"].data.find(
      (el) => el.name_M == form.name_M_old
    );
    const M_new = setting.value.tables["tabMaterial"].data.find(
      (el) => el.name_M == form.name_M_new
    );

    if (!M_old || !M_new) {
      ElMessage.error(`Зробіть вибір матеріалу для пересорта!`);
      return;
    }
    if (form.count_Sort == 0) {
      ElMessage.error(`Введіть кількість матеріалу для пересорта!`);
      return;
    }
    if (isLessening.value && form.count_Sort_New == 0) {
      ElMessage.error(`Введіть кількість матеріалу при пересорта на виході!`);
      return;
    }

    const token = generateToken(8);
    const groupOperation = [
      {
        id_V: 6,
        id_M: M_old.id_M,
        d_count: form.count_Sort,
        d_price: 0,
        old_count: 0,
        old_price: 0,
        new_count: 0,
        new_price: 0,
        mode_otg: "",
        id_agent: 0,
        is_move_kassa: 0,
        token,
      },
      {
        id_V: 7,
        id_M: M_new.id_M,
        d_count: !isLessening.value ? form.count_Sort : form.count_Sort_New,
        d_price: 0,
        old_count: 0,
        old_price: 0,
        new_count: 0,
        new_price: 0,
        mode_otg: "",
        id_agent: 0,
        is_move_kassa: 0,
        token,
      },
    ];

    const response = await HTTP.post("", {
      _method: "addTransaction",
      _idUser: getCurUser.value.id,
      _idPunkt: props.idPunkt,
      _date: form.date,
      _time: getTime.value,
      _comment: `Пересорт; ${form.comment}`,
      _isEdit: 0,
      _isDel: 1,
      _opers: groupOperation,
    });

    if (response.data.isSuccesfull) {
      clearForm();
      emit("update:visible", false);
      if (+getSettingUser.value.isShowMes) {
        ElMessage.success(response.data.message);
      }
    } else {
      ElMessage.error(response.data.message);
    }
  } catch (e) {
    ElMessage.error("Помилка збереження транзакції");
  }
};

const sourceTable_K = computed(() => {
  const _tabK = setting.value.tables["tabKategories"].data;

  return _tabK.filter((row) => {
    return +row.id_K == 3 || +row.id_K == 4;
  });
});

const sourceTable_M_old = computed(() => {
  const _tab = setting.value.tables["tabMaterial"].data;

  return _tab.filter((row) => row.name_K == form.name_K && row.count > 0);
});

const sourceTable_M_new = computed(() => {
  const _tab = setting.value.tables["tabMaterial"].data;

  return _tab.filter(
    (row) => row.name_K == form.name_K && row.name_M != form.name_M_old
  );
});

const countPeresortOld = computed(() => {
  const obj = setting.value.tables["tabMaterial"].data.find((el) => {
    return el.name_M == form.name_M_old;
  });
  return obj ? obj.count : 0;
});

const countPeresortNew = computed(() => {
  const obj = setting.value.tables["tabMaterial"].data.find((el) => {
    return el.name_M == form.name_M_new;
  });
  return obj ? obj.count : 0;
});

const isLessening = computed(() => {
  const obj = setting.value.tables["tabMaterial"].data.find((el) => {
    return el.name_M == form.name_M_old;
  });
  return obj ? +obj.isLessening : 0;
});

const generateToken = (length = 12) => {
  const lower = "abcdefghijklmnopqrstuvwxyz";
  const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  const digits = "0123456789";
  //const special = "!@#$%^&*()-_=+[]{};:,.<>?";
  const all = `${lower}${upper}${digits}`;

  let password = "";

  // добираем оставшиеся символы
  for (let i = 0; i < length; i++) {
    const idx = crypto.getRandomValues(new Uint32Array(1))[0] % all.length;
    password += all[idx];
  }

  // перемешиваем символы
  return password
    .split("")
    .sort(() => Math.random() - 0.5)
    .join("");
};

onUpdated(async () => {
  startTimer();
  form.namePunkt = props.namePunkt;
  form.date = form.curDate;
  form.title = `Пересортування матеріалів / ${form.namePunkt} / ${form.nameUser}`;

  await getKategories();
  if (setting.value.tables["tabKategories"].data.length) {
    form.name_K = setting.value.tables["tabKategories"].data[2].name_K;
  }

  await getMaterial();
});

onUnmounted(() => {
  clearInterval(form.timer);
});
</script>

<style></style>
