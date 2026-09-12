const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
function cell(text, input = false) {
    return { textContent: text, innerHTML: text, className: '', querySelector: () => input ? {} : null, contains: () => false };
}
const row = { cells: Array.from({length: 10}, (_, i) => cell(String(i), i === 3 || i === 5)) };
row.cells[0].textContent = 'Home'; row.cells[2].textContent = 'Away';
const fresh = { cells: row.cells.map(c => cell(c.textContent)) };
fresh.cells[6].innerHTML = '2'; fresh.cells[8].innerHTML = '1'; fresh.cells[9].innerHTML = '4';
const status = {};
const timers = [];
const navigation = [];
const context = {
    window: {blLiveConfig: {view: 'day', day: 3, currentDay: 3}, addEventListener() {},
        submitaction: action => navigation.push(action)},
    document: {activeElement: row.cells[3], querySelector: () => null,
        querySelectorAll: selector => selector === 'tr' ? [row] : [], getElementById: () => status},
    DOMParser: class { parseFromString() { return {querySelector: () => null, querySelectorAll: selector => selector === 'tr' ? [fresh] : []}; } },
    setTimeout: (fn, delay) => timers.push(delay), Date, console
};
let source = fs.readFileSync(require('path').join(__dirname,'../user/live.js'),'utf8');
source = source.replace('async function refresh()', 'window.testUpdate = update; async function refresh()');
vm.runInNewContext(source, context);
const input = row.cells[3];
context.window.testUpdate({html:'fixture',clubs:[],currentDay:3});
assert.equal(row.cells[6].innerHTML,'2'); assert.equal(row.cells[9].innerHTML,'4');
assert.strictEqual(row.cells[3],input); assert.equal(input.innerHTML,'3');
assert.strictEqual(context.document.activeElement,input);
assert.deepEqual(navigation, []);
const previousStatus = status.textContent;
context.window.testUpdate({changed:false,version:'unchanged'});
assert.equal(status.textContent,previousStatus);
assert.strictEqual(row.cells[3],input);
for (const currentDay of [2, null, 'invalid', 35]) {
    context.window.testUpdate({html:'fixture',clubs:[],currentDay});
}
assert.deepEqual(navigation, []);
context.window.testUpdate({html:'fixture',clubs:[],currentDay:4});
assert.deepEqual(navigation, ['list']);
context.window.testUpdate({html:'fixture',clubs:[],currentDay:4});
assert.deepEqual(navigation, ['list']);
assert.strictEqual(row.cells[3],input);
assert.deepEqual(timers,[60000]);
console.log('OK: scores refresh; inputs and focus preserved; automatic navigation once on advancement; invalid days ignored; 60-second interval');
