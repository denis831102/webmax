<template>
  <!--  eslint-disable  -->
  <div v-if="posts.length">
    <h3>Список постів ({{ posts.length }})</h3>

    <transition-group name="post-list">
      <post-item
        v-for="post in posts"
        :post="post"
        :key="post.id"
        @remove="$emit('remove', post)"
        #="{ curpost }"
      >
        <!-- <template v-slot:default="slotProps"> -->
        <span class="slot">{{ curpost.id }} </span>
        <!-- </template> -->
      </post-item>
    </transition-group>
  </div>
  <h2 v-else style="color: red">Список постів пустий</h2>
</template>

<script>
import PostItem from "@/components/PostItem4";
import { computed } from "vue";

export default {
  components: {
    PostItem,
  },
  props: {
    posts: {
      type: Array,
      required: true,
    },
    searchQuery: {
      type: String,
    },
  },
  provide() {
    return {
      postsAll: computed(() => this.posts),
      nameAvtor: "Denis",
    };
  },
};
</script>

<style scoped>
.post-list-item {
  display: inline-block;
  margin-right: 10px;
}
.post-list-enter-active,
.post-list-leave-active {
  transition: all 0.4s ease;
}
.post-list-enter-from,
.post-list-leave-to {
  opacity: 0;
  transform: translateX(230px);
}
.post-list-move {
  transition: transform 0.8s ease;
}

.slot {
  font-weight: bold;
  color: blue;
}
</style>
