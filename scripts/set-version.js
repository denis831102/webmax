const fs = require("fs");
const path = require("path");

// путь до package.json
const pkgPath = path.resolve(__dirname, "../package.json");
const pkg = require(pkgPath);

// 1) читаем аргумент (patch/minor/major), по умолчанию patch
const bumpType = process.argv[2] || "patch";

// 2) увеличиваем версию (x.y.z → x.y.z+1)
let [major, minor, patch] = pkg.version.split(".").map(Number);
// patch++;
switch (bumpType) {
  case "major":
    major++;
    minor = 0;
    patch = 0;
    break;
  case "minor":
    minor++;
    patch = 0;
    break;
  case "patch":
  default:
    patch++;
    break;
}
pkg.version = `${major}.${minor}.${patch}`;

// 3) сохраняем обновлённый package.json
fs.writeFileSync(pkgPath, JSON.stringify(pkg, null, 2), "utf8");

// 4) добавляем дату/время
const now = new Date();
const buildTime = now.toLocaleString("uk-UA", { hour12: false });
const versionWithBuild = `${pkg.version}`;
const versionWithDate = `build ${buildTime}`;

// 5) записываем в .env.local (Vue CLI требует префикс VUE_APP_)
fs.writeFileSync(
  ".env.local",
  [
    `VUE_APP_VERSION=${versionWithBuild}`,
    `VUE_APP_VERSION_DATE=${versionWithDate}`,
  ].join("\n"),
  "utf8"
);

console.log(`✅ Нова версія: ${pkg.version}`);
console.log(`✅ Записано в .env.local: ${versionWithDate}`);
