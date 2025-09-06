const fs = require("fs");
const path = require("path");

// путь до package.json
const pkgPath = path.resolve(__dirname, "../package.json");
const pkg = require(pkgPath);

// 1) увеличиваем patch-версию (x.y.z → x.y.z+1)
let [major, minor, patch] = pkg.version.split(".").map(Number);
patch++;
pkg.version = `${major}.${minor}.${patch}`;

// сохраняем обновлённый package.json
fs.writeFileSync(pkgPath, JSON.stringify(pkg, null, 2), "utf8");

// 2) добавляем дату/время
const now = new Date();
const buildTime = now.toLocaleString("uk-UA", { hour12: false });
const versionWithBuild = `${pkg.version}`;
// const versionWithBuild = `${pkg.version} (build ${buildTime})`;

// записываем в .env.local (Vue CLI требует префикс VUE_APP_)
fs.writeFileSync(".env.local", `VUE_APP_VERSION=${versionWithBuild}\n`, "utf8");

console.log(`✅ Новая версия: ${pkg.version}`);
console.log(`✅ Записано в .env.local: ${versionWithBuild}`);
