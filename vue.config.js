const { defineConfig } = require("@vue/cli-service");
// const webpack = require("webpack");
// const { version } = require("./package.json");

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
  // configureWebpack: {
  //   plugins: [
  //     new webpack.DefinePlugin({
  //       __APP_VERSION__: JSON.stringify(version),
  //     }),
  //   ],
  // },
});
