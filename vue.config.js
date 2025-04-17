const { defineConfig } = require("@vue/cli-service");
module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    client: {
      overlay: {
        runtimeErrors: (error) => {
          // if (
          //   [
          //     "ResizeObserver loop completed with undelivered notifications",
          //     "ResizeObserver loop limit exceeded",
          //   ].includes(error.message)
          // ) {
          //   return false;
          // }
          return false;
        },
      },
    },
  },
});
