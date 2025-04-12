import axios from "axios";
import { ref, watch, onMounted } from "vue";
// import { ref, unref, reactive, onMounted, onBeforeUnmount, watch, toRefs, toRef, computed } from 'vue'

export default function usePosts(limit) {
  const posts = ref([]);
  const page = ref({ num: 1 });
  const totalPages = ref(0);
  const isPostLoading = ref(true);
  const dialogVisible = ref(false);
  const fetching = async () => {
    try {
      const response = await axios.get(
        `https://jsonplaceholder.typicode.com/posts`,
        {
          params: {
            _page: page.value.num,
            _limit: limit,
          },
        }
      );

      totalPages.value = Math.ceil(response.headers["x-total-count"] / limit);
      posts.value = response.data;
    } catch (e) {
      alert("Помилка");
    }
  };

  const removePost = (post) => {
    posts.value = posts.value.filter((p) => p.id !== post.id);
  };

  const showDialog = (bool) => {
    dialogVisible.value = bool;
  };

  const createPost = (newPost) => {
    //showDialog(false);
    posts.value.push(newPost);
  };

  //   onCreated();
  //   computed();
  //   watch();
  onMounted(fetching);

  watch(
    () => posts.value.length,
    () => {
      dialogVisible.value = false;
    }
  );
  watch(page.value, () => fetching());
  // watch(page, fetching);

  return {
    posts,
    isPostLoading,
    totalPages,
    removePost,
    dialogVisible,
    showDialog,
    createPost,
    page,
  };
}
