/* eslint-disable */
export default {
  name: "intersection",
  mounted(el, binding) {
    const options = {
      rootMargin: "0px",
      threshold: 1.0,
    };
    const callback = (entries, observer) => {
      if (
        entries[0].isIntersecting
        // && this.page < this.totalPages
      ) {
        // this.loadMorePosts();
        binding.value();
        // console.log(entries);
        // console.log("->", binding.arg[1]);
        console.log("->", binding.arg);
      }
    };

    const observer = new IntersectionObserver(callback, options);
    observer.observe(el);
  },
};
