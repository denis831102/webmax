import axioc from "axios";

//let base64 = btoa("username:password");
//const curDate = new Date();
const ecp = "c3875d07f44c422f3b3bc019c23e16ae";

export const HTTP = axioc.create({
  baseURL: "https://webmax.lond.lg.ua/php/Server.php",
  headers: {
    Ecp: ecp,
    //Ecp: btoa(`${ecp}:${curDate}`),
    Token: "",
  },
});
