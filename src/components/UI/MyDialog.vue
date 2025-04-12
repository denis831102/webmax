<template>
  <!-- <teleport to="body"> -->
  <div class="dialog" v-if="show" @click="hideDialog">
    <div @click.stop class="dialog__content">
      <slot> </slot>
    </div>
  </div>
  <!-- </teleport> -->
</template>

<script>
export default {
  name: "my-dialog",
  props: {
    show: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    hideDialog() {
      this.$emit("update:show", false);
    },
  },
};
</script>

<style scoped>
.dialog {
  top: 0;
  bottom: 0;
  right: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.5);
  position: fixed;
  display: flex;
}
.dialog__content {
  margin: auto;
  background: white;
  border-radius: 12px;
  min-height: 150px;
  min-width: 300px;
  padding: 20px;
  box-shadow: 0px 0px 15px 5px #131315;
}
</style>
