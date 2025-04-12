import { ref, computed } from "vue";

export function useSortedAndSearchedPosts(sortedPosts) {
  const searchQuery = ref("");

  const sortedAndSearchedPosts = computed(() => {
    return sortedPosts.value.filter((post) =>
      post.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  });

  const newPost = computed(() => {
    return sortedAndSearchedPosts.value.map((post) => {
      return {
        ...post,
        title: post.title.replace(
          new RegExp(searchQuery.value, "g"),
          // searchQuery.value,
          `<span class="search__text">${searchQuery.value}</span>`
        ),
      };
    });
  });

  return {
    sortedAndSearchedPosts,
    searchQuery,
    newPost,
  };
}
