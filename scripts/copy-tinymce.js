import fs from 'node:fs';
import path from 'node:path';

const projectRoot = process.cwd();

const srcRoot = path.join(projectRoot, 'node_modules', 'tinymce');
const destRoot = path.join(projectRoot, 'public', 'vendor', 'tinymce');

const required = [
  'icons',
  'models',
  'plugins',
  'skins',
  'themes',
];

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function copyDir(from, to) {
  if (!fs.existsSync(from)) {
    throw new Error(`Missing source directory: ${from}`);
  }
  ensureDir(path.dirname(to));
  fs.cpSync(from, to, { recursive: true, force: true });
}

function copyFile(from, to) {
  if (!fs.existsSync(from)) {
    return;
  }
  ensureDir(path.dirname(to));
  fs.copyFileSync(from, to);
}

if (!fs.existsSync(srcRoot)) {
  console.error('TinyMCE is not installed. Run: npm i');
  process.exit(1);
}

ensureDir(destRoot);

for (const name of required) {
  copyDir(path.join(srcRoot, name), path.join(destRoot, name));
}

// Helpful to have the core file around for debugging / future script-tag use.
copyFile(path.join(srcRoot, 'tinymce.min.js'), path.join(destRoot, 'tinymce.min.js'));
copyFile(path.join(srcRoot, 'tinymce.js'), path.join(destRoot, 'tinymce.js'));

console.log(`TinyMCE assets copied to: ${path.relative(projectRoot, destRoot)}`);
