const fs = require('fs');
const path = require('path');

const replacements = [
    [/á/g, 'á'], [/à/g, 'à'], [/â/g, 'â'], [/ã/g, 'ã'],
    [/é/g, 'é'], [/ê/g, 'ê'], [/í/g, 'í'], [/í/g, 'í'],
    [/ó/g, 'ó'], [/ô/g, 'ô'], [/õ/g, 'õ'], [/ú/g, 'ú'],
    [/ç/g, 'ç'], [/à/g, 'à'], [/Ç/g, 'Ç'], [/É/g, 'É'],
    [/Ó/g, 'Ó'], [/°/g, '°'], [/—/g, '—'], [/🛒/g, '🛒'],
    [/Æ/g, 'Æ'], [/æ/g, 'æ'], [/×/g, '×'], [/⚠️/g, '⚠️'],
    [/ó/g, 'ó'], [/ção/g, 'ção'], [/ê/g, 'ê'], [/í/g, 'í']
];

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            if (!file.includes('node_modules') && !file.includes('.git')) {
                results = results.concat(walk(file));
            }
        } else if (file.endsWith('.php') || file.endsWith('.html') || file.endsWith('.js')) {
            results.push(file);
        }
    });
    return results;
}

const files = walk('.');
console.log(`Encontrados ${files.length} arquivos para verificar.`);

files.forEach(file => {
    try {
        let content = fs.readFileSync(file, 'utf8');
        let originalContent = content;

        replacements.forEach(([regex, replacement]) => {
            content = content.replace(regex, replacement);
        });

        if (content !== originalContent) {
            fs.writeFileSync(file, content, 'utf8');
            console.log(`✅ Corrigido: ${file}`);
        }
    } catch (err) {
        console.error(`❌ Erro em ${file}: ${err.message}`);
    }
});
