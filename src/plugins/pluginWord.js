export default {
  install: (app, options) => {
    app.config.globalProperties.$translate = (sentence) => {
      // return key.split(" ").reduce((o, i) => {
      //   if (o) return o[i];
      // }, options);
      // return options;

      return (
        sentence
          //.replace(",", "")
          .split(" ")
          .map((el) => {
            return options[el] ? options[el] : el;
          })
          .join(" ")
      );
    };

    app.config.globalProperties.$word = {
      pidkreslenja(key) {
        return key.split("").join("_");
      },
      hightRegistry(key) {
        return key.toUpperCase();
      },
    };

    // app.prototype.$word = {
    //   pidkreslenja(key) {
    //     return key.split("").join("_");
    //   },
    //   hightRegistry(key) {
    //     return key.toUpperCase();
    //   },
    // };

    app.provide("dictonary", options);
  },
};
