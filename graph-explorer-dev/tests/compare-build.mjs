import ts from 'typescript';
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
const root=path.resolve(path.dirname(new URL(import.meta.url).pathname),'..');
const manifest=JSON.parse(fs.readFileSync(path.join(root,'BUILD-MANIFEST.json'),'utf8'));
function exportsOf(text){
 const file=ts.createSourceFile('x.js',text,ts.ScriptTarget.Latest,true,ts.ScriptKind.JS);assert.equal(file.parseDiagnostics.length,0);
 return file.statements.filter(ts.isExportDeclaration).flatMap(n=>n.exportClause?.elements?.map(e=>e.name.text)||[]).sort();
}
const old=fs.readFileSync(path.join(root,'Ausgangsstand/common-CPcN3Iil.js'),'utf8');
const updated=fs.readFileSync(path.join(root,'Graph-Explorer/assets',manifest.generated.commonName),'utf8');
assert.deepEqual(exportsOf(updated),exportsOf(old),'Public bundle exports preserved');
assert.ok(!updated.includes('__testWorkspace')&&!updated.includes('TestStandardTemplate'));
const htmlOld=fs.readFileSync(path.join(root,'Ausgangsstand/envendpoint.html'),'utf8');
const htmlNew=fs.readFileSync(path.join(root,'Graph-Explorer/envendpoint.html'),'utf8');
assert.ok(htmlNew.includes('./config.js'));
assert.ok(!htmlNew.includes('/graph-explorer/assets/'));
assert.ok(!htmlNew.includes('demoSelector" onchange'));
assert.ok(htmlNew.includes(manifest.generated.entryName));
assert.ok(htmlNew.includes(manifest.generated.commonName));
console.log('Public bundle exports preserved; relative local assets and explicit config; no test instrumentation.');
