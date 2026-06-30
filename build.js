/**
 * build.js — Copia los archivos del proyecto a /dist listo para subir.
 * Uso: npm run build
 */

const fs   = require('fs');
const path = require('path');

const SRC  = __dirname;
const DIST = path.join(__dirname, 'dist');

const ARCHIVOS = [
  'index.html',
  'agradecimiento.html',
  'procesar-formulario.php',
  'assets/css/styles.css',
  'assets/js/script.js',
  'assets/img/logo.png',
];

// Limpiar y crear dist/
if (fs.existsSync(DIST)) fs.rmSync(DIST, { recursive: true });
fs.mkdirSync(DIST, { recursive: true });

ARCHIVOS.forEach(rel => {
  const origen  = path.join(SRC, rel);
  const destino = path.join(DIST, rel);
  fs.mkdirSync(path.dirname(destino), { recursive: true });
  if (fs.existsSync(origen)) {
    fs.copyFileSync(origen, destino);
    console.log(`✓  ${rel}`);
  } else {
    console.warn(`⚠  No encontrado: ${rel}`);
  }
});

console.log(`\n✅ Build listo en: ${DIST}`);
console.log('   Sube el contenido de /dist a Hostinger vía FTP o File Manager.');
