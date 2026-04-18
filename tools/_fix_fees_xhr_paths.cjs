const fs = require('fs');
const path = require('path');
const d = path.join(__dirname, '../resources/js/spa/fees/pages');
for (const f of fs.readdirSync(d)) {
    if (!f.endsWith('.jsx')) continue;
    const p = path.join(d, f);
    let s = fs.readFileSync(p, 'utf8');
    s = s.replace("from '../api/xhrJson'", "from '../../api/xhrJson'");
    fs.writeFileSync(p, s);
}
console.log('ok');
