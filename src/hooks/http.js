import axioc from "axios";

//let base64 = btoa("username:password");

export const HTTP = axioc.create({
  baseURL: "https://webmax.lond.lg.ua/php/Server.php",
  headers: {
    Ecp: "c3875d07f44c422f3b3bc019c23e16ae",
    Token: "",
  },
});
