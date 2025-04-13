export default {
  install: (app, options) => {
    app.config.globalProperties.$translate = (sentence) => {
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

    app.provide("dictonary", options);
  },
};
