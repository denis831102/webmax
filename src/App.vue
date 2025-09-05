<template>
  <div>
    <div class="hMain" @click.prevent="$router.push({ name: 'crm' })">
      Web MetAl'ans
    </div>
    <transition name="component-fade" mode="out-in">
      <RouterView class="sRout" />
    </transition>
  </div>
</template>

<style>
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  color: #2c3e50;
  margin: 10px;
}
* {
  margin: 0;
  /* padding: 0; */
  box-sizing: border-box;
}
.hMain {
  padding: 10px;
  background: linear-gradient(rgba(0, 0, 255, 0.4), rgba(255, 255, 0, 0.4));
  /* background: #ecf5ff; */
  box-shadow: 1px 0px 9px 3px #568989;
  text-align: center;
  font-size: 20pt;
}

.logo {
  width: 20px;
  height: 20px;
}

.sRout {
  display: flex;
  /* border: 1px solid teal; */
  margin: 10px 0;
  background: #f4f7f7;
  padding: 20px 0;
}

.component-fade-enter-active,
.component-fade-leave-active {
  transition: opacity 0.3s ease;
}

.component-fade-enter-from,
.component-fade-leave-to {
  opacity: 0;
}
</style>
