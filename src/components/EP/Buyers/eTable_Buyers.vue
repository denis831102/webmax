<template>
  <eDialog_Buyers v-model:visible="setting.dialog['editBuyers'].visible" />

  <el-row :gutter="20" style="margin: 0 0 20px 10px">
    <el-col :span="6">
      <el-input
        v-model="search"
        size="small"
        style="width: 100%; height: 100%"
        placeholder="Пошук за покупцем"
        :prefix-icon="Search"
      />
    </el-col>
    <el-col :span="10">
      <el-button-group class="ml-4">
        <el-button type="primary" :icon="HomeFilled" @click="addBuyer()"
          >Додати нового
        </el-button>
        <el-button type="primary" plain :icon="Refresh" @click="getBuyer()">
          Оновити
        </el-button>
      </el-button-group>
    </el-col>
  </el-row>

  <el-table
    :data="filterTable"
    :default-sort="{ prop: 'id', order: 'ascending' }"
    :sort-method="sortMethod"
    highlight-current-row
    table-layout="fixed"
    style="width: 100%"
  >
    <el-table-column type="index" />

    <el-table-column label="Назва Покупця" sortable prop="name">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <div style="margin-left: 10px">{{ scope.row.name }}</div>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Код 1С">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Key /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.cod }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Коментар" sortable prop="commit">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><EditPen /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.commit }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Тип розрахунку" sortable prop="typeM">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Money /></el-icon>
          <span style="margin-left: 10px">{{
            scope.row.typeM == "cash" ? "КЕШ" : "Б-Г"
          }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Редагування ">
      <template #default="scope">
        <el-button size="small" @click="editBuyer(scope.$index, scope.row)">
          <el-icon><Edit /></el-icon>
        </el-button>

        <el-button
          size="small"
          type="danger"
          @click="deleteBuyer(scope.$index, scope.row)"
        >
          <el-icon><DeleteFilled /></el-icon>
        </el-button>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
/* eslint-disable */

import { inject, onActivated, computed, ref } from "vue";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";
import {
  Search,
  HomeFilled,
  CopyDocument,
  Refresh,
} from "@element-plus/icons-vue";
import { HTTP } from "@/hooks/http";
import eDialog_Buyers from "@/components/EP/Buyers/eDialog_Buyers";

const setting = inject("setting");
const search = ref("");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);

const editBuyer = (index, row) => {
  setting.value.tables["tabBuyers"].curRow = row;
  setting.value.dialog["editBuyers"].initiator = "table_Buyer_edit";
  setting.value.dialog["editBuyers"].visible = true;
};

const deleteBuyer = async (index, row) => {
  const response = await HTTP.get("", {
    params: {
      _method: "delBuyer",
      _id: row["id"],
    },
  });

  if (response.data.isSuccesfull) {
    const _tab = setting.value.tables["tabBuyers"];
    _tab.data = _tab.data.filter((el) => el.id !== row.id);
  } else {
    ElMessage.error("Заборонено видаляти покупця");
  }
};

const getBuyer = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getBuyer",
      },
    });

    setting.value.tables["tabBuyers"].data = response.data;
    ElMessage.success("Покупці оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addBuyer = () => {
  setting.value.dialog["editBuyers"].initiator = "table_Buyer_add";
  setting.value.dialog["editBuyers"].visible = true;
};

const sortMethod = () => {
  return (a, b) => {
    a > b ? 1 : -1;
  };
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabBuyers"];

  return _tabl.data.filter((row) =>
    row.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

onActivated(getBuyer);
</script>
