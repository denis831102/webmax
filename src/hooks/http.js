import axioc from "axios";

//let base64 = btoa("username:password");

export const HTTP = axioc.create({
  baseURL: "https://webmax.lond.lg.ua/php/Server.php/",
  headers: {
    // Authorization: `Basic ${base64}`,
    token: "",
  },
});
