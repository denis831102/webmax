import { createStore } from "vuex";
import likes from "@/store/modules/likes";
import { postModule } from "@/store/modules/post";
import authenticated from "@/store/modules/authenticated";

export default createStore({
  state: () => ({
    nameAuth: "Denis",
  }),
  modules: {
    likes,
    authenticated,
    post: postModule,
  },
});
