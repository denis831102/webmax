<template>
  <button class="btn">
    <div class="adslot">
      <slot name="adslot"></slot>
    </div>
    <slot></slot>
  </button>
</template>

<script>
export default {
  name: "my-button",
};
</script>

<style scoped>
.btn {
  align-self: flex-end;
  margin: 15px 5px;
  padding: 10px 15px;
  background: none;
  color: teal;
  border: 1px solid teal;
  background: #74cea03d;
}
.adslot {
  font-size: 30pt;
}
</style>
