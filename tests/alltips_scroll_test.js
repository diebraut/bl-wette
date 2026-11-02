const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert/strict');
const source = fs.readFileSync(path.join(__dirname, '../user/alltips.php'), 'utf8');
const code = source.slice(source.indexOf('    function setupDirectionalScrolling()'), source.indexOf('    function observeStickyColumns()'));
function setup(height) {
    let clock = 0, nextId = 0;
    const frames = new Map(), handlers = {}, properties = {};
    const win = {innerHeight: height, scrollX: 0, scrollY: 0,
        getComputedStyle: () => ({zoom: '1'}), matchMedia: () => ({matches: false}),
        scrollTo(pos) { this.scrollX = pos.left; this.scrollY = pos.top; },
        requestAnimationFrame(fn) { const id = ++nextId; frames.set(id, fn); return id; },
        cancelAnimationFrame(id) { frames.delete(id); }};
    const heading = {getBoundingClientRect: () => ({bottom: 200 - win.scrollY})};
    const area = {dataset: {}, isConnected: true, scrollLeft: 0, scrollTop: 0, clientWidth: 500, clientHeight: 270,
        style: {setProperty(name, value) { properties[name] = value; }},
        addEventListener(type, fn) { handlers[type] = fn; },
        getBoundingClientRect: () => ({top: 80-win.scrollY, bottom: 350-win.scrollY}),
        querySelector: () => heading,
        querySelectorAll: () => Array.from({length: 10}, (_, i) => ({getBoundingClientRect: () => ({top: 200+i*30-win.scrollY-area.scrollTop, bottom: 230+i*30-win.scrollY-area.scrollTop})}))};
    const ctx = vm.createContext({document: {body: {}, hidden: false, querySelector: () => area}, window: win,
        performance: {now: () => clock}, tablePageLiftSpace: 0, updateStickyColumns() {}});
    vm.runInContext(code + '\nsetupDirectionalScrolling();', ctx);
    const target = inHeading => ({closest: () => inHeading ? heading : null});
    const touch = (x,y) => ({identifier: 1, clientX: x, clientY: y});
    return {win, area, frames, ctx, properties,
        start(inHeading) { handlers.touchstart({target: target(inHeading), touches: [touch(200,200)]}); },
        move(x,y) { clock+=20; handlers.touchmove({touches: [touch(x,y)],cancelable: true,preventDefault(){}}); },
        end() { handlers.touchend({touches: []}); },
        wheel(inHeading,dx=20,dy=40) { let canceled=false; handlers.wheel({target:target(inHeading),deltaX:dx,deltaY:dy,deltaMode:0,cancelable:true,preventDefault(){canceled=true;}});return canceled; },
        frame() { clock+=16; const callbacks=[...frames.values()];frames.clear();callbacks.forEach(fn=>fn(clock)); }};
}
{
    const t=setup(350); // Exactly five complete player rows fit.
    t.start(true);t.move(195,120);t.end();
    assert.equal(t.area.scrollTop,0);assert.equal(t.area.scrollLeft,0);assert.equal(t.win.scrollY,0);
    assert.equal(t.frames.size,0,'No list momentum from a heading swipe');
    assert.ok(t.wheel(true));assert.equal(t.area.scrollTop,0);assert.equal(t.area.scrollLeft,0);
}
{
    const t=setup(320); // Only four complete player rows fit.
    t.start(true);t.move(198,150);
    assert.equal(t.win.scrollY,50);assert.equal(t.area.scrollTop,0);assert.equal(t.area.scrollLeft,0);
    t.move(190,-100);t.end();
    assert.equal(t.win.scrollY,80,'Table stops at the top of the window');
    assert.equal(t.frames.size,0);assert.equal(t.ctx.tablePageLiftSpace,80);
    t.start(true);t.move(200,300);t.end();
    assert.equal(t.win.scrollY,0,'Header can restore the original page position');
    assert.equal(t.ctx.tablePageLiftSpace,0);
}
{
    const t=setup(320);
    t.start(true);t.move(100,195);t.end();
    assert.equal(t.win.scrollY,0);assert.equal(t.area.scrollLeft,100,'Horizontal heading swipe moves columns');
    assert.equal(t.area.scrollTop,0);
    assert.equal(t.frames.size,1,'Horizontal heading swipe keeps its smooth momentum');
    t.start(false);t.move(160,198);t.move(120,190);t.end();
    const before=t.area.scrollLeft;t.frame();
    assert.ok(t.area.scrollLeft>before);assert.equal(t.area.scrollTop,0,'Body keeps one-axis momentum');
    t.start(true);assert.equal(t.frames.size,0,'Touching the heading stops a prior list fling');
}
{
    const t=setup(350);
    assert.ok(t.wheel(true,40,2));
    assert.equal(t.area.scrollLeft,40,'Horizontal wheel scrolling also works over the heading');
    assert.equal(t.area.scrollTop,0);
}
console.log('OK: horizontal header touch/wheel and momentum; vertical header blocks list; four/five-row lift threshold; body scrolling retained');

// The table fills the available height for short and long lists alike.
const heightCode = source.slice(source.indexOf('    function updateStickyColumns()'), source.indexOf('    function setupDirectionalScrolling()'));
function tableHeight(bodyHeight, zoom=1, scrollTop=0) {
    const properties = {};
    const area = {scrollTop,style:{setProperty(key,value){properties[key]=parseFloat(value);}},offsetHeight:412,clientHeight:400,
        getBoundingClientRect:()=>({top:80})};
    const table = {style:{setProperty(){}},tHead:{offsetHeight:120},tBodies:[{offsetHeight:bodyHeight}],closest:()=>area,
        querySelector(selector){return selector==='tbody tr[data-user-id]' ? {offsetHeight:30} : {offsetWidth:40};}};
    const context = vm.createContext({tablePageLiftSpace:0,
        window:{innerHeight:700,getComputedStyle:()=>({zoom:String(zoom)})},
        document:{body:{},querySelector(selector){return selector==='.table-scroll table' ? table : {getBoundingClientRect:()=>({height:25})};}}});
    vm.runInContext(heightCode+'\nupdateStickyColumns();',context);
    return properties;
}
{
    const short=tableHeight(90);
    assert.equal(short['--table-scroll-height'],528,'Short lists fill the available window height');
    assert.equal(short['--table-scroll-tail-height'],396,'Tail retains the full vertical scroll range');
    const long=tableHeight(900);
    assert.equal(long['--table-scroll-height'],528,'Long lists use the same available height');
    const zoomed=tableHeight(90,0.62);
    const expected=(700-80-25-(25+10*0.62)-32*0.62)/0.62;
    assert.ok(Math.abs(zoomed['--table-scroll-height']-expected)<1e-8,'Body zoom is compensated');
}
for (const offset of [0,15,30,60,90]) {
    const height=tableHeight(90,1,offset);
    const viewport=height['--table-scroll-height'];
    const tail=height['--table-scroll-tail-height'];
    assert.equal(viewport,528,'Window height stays fully used during scrolling');
    assert.equal(90+tail-(viewport-120-12),90,'Scroll range stays equal to player height');
}
assert.equal(tableHeight(900,1,900)['--table-scroll-height'],528,'Full height remains after scrolling all players away');
console.log('OK: full available window height for short/long lists, zoom compensation and stable vertical scroll range');
