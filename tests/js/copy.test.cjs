const {test} = require('node:test');
const assert = require('node:assert/strict');
const {runInNewContext} = require('node:vm');
const fs = require('node:fs');
const code = fs.readFileSync(require('node:path').join(__dirname, '../../assets/copy.js'), 'utf8');
function setup(writeText) {
    let listener;
    const text = {value: 'message <test>\n#0 stack\n{"id":1}', hidden: true, focus() {this.focused=true;}, select() {this.selected=true;}};
    const status = {textContent:''};
    const label = {hidden:true};
    const button = {disabled:false, dataset:{logCopy:'entry',success:'Copied',error:'Failed'},parentElement:{querySelector:selector=>selector==='[data-copy-label]'?label:status}};
    runInNewContext(code, {document:{addEventListener:(_, fn)=>listener=fn,getElementById:()=>text},navigator:{clipboard:{writeText}}});
    return {text,status,button,click:()=>listener({target:{closest:()=>button}})};
}
test('copies exact multiline plain text and reports success', async () => {
    let copied;
    const ui=setup(async value=>{copied=value;});await ui.click();
    assert.equal(copied,ui.text.value);assert.equal(ui.status.textContent,'Copied');assert.equal(ui.button.disabled,false);assert.equal(ui.text.hidden,true);
});
test('prevents concurrent writes and restores button after completion', async () => {
    let done,calls=0;
    const ui=setup(()=>{calls++;return new Promise(resolve=>done=resolve);});
    const pending=ui.click();await ui.click();assert.equal(calls,1);assert.equal(ui.button.disabled,true);
    done();await pending;assert.equal(ui.button.disabled,false);
});
test('denied clipboard reveals selectable text and allows retry', async () => {
    let denied=true;
    const ui=setup(async()=>{if(denied)throw Error('denied');});await ui.click();
    assert.equal(ui.status.textContent,'Failed');assert.equal(ui.text.hidden,false);assert.equal(ui.text.focused,true);assert.equal(ui.text.selected,true);assert.equal(ui.button.disabled,false);
    denied=false;await ui.click();assert.equal(ui.text.hidden,true);assert.equal(ui.status.textContent,'Copied');
});
