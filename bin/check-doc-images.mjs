#!/usr/bin/env node
/**
 * Checks every image referenced from the repo's tracked markdown.
 *
 * Each image must resolve, relative to the file that references it, to a real
 * webp or svg under docs/website/images/, and carry alt text. Fenced code is
 * skipped (code samples are not images). Exits 1 on any failure.
 *
 * Usage: node bin/check-doc-images.mjs
 */
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const root = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const imagesDir = path.join(root, 'docs/website/images') + path.sep;
const files = execFileSync('git', ['ls-files', '*.md'], { cwd: root, encoding: 'utf8' }).split('\n').filter(Boolean);

let total = 0;
const problems = [];
for (const file of files) {
	const body = fs.readFileSync(path.join(root, file), 'utf8').replace(/```[\s\S]*?```/g, (m) => m.replace(/[^\n]/g, ' '));
	const refs = [
		...[...body.matchAll(/!\[([^\]]*)\]\(([^)\s]+)/g)].map((m) => ({ at: m.index, alt: m[1], src: m[2] })),
		...[...body.matchAll(/<img\b[^>]*\bsrc=["']([^"']+)["'][^>]*>/gi)].map((m) => ({ at: m.index, alt: (m[0].match(/\balt=["']([^"']*)["']/i) || [])[1] || '', src: m[1] })),
	];
	for (const { at, alt, src } of refs) {
		total++;
		const where = `${file}:${body.slice(0, at).split('\n').length}`;
		if (/^https?:/i.test(src)) { problems.push(`${where} remote image, keep it in docs/website/images: ${src}`); continue; }
		const target = path.resolve(path.dirname(path.join(root, file)), src);
		if (!fs.existsSync(target)) problems.push(`${where} missing: ${src}`);
		else if (!target.startsWith(imagesDir)) problems.push(`${where} outside docs/website/images: ${src}`);
		if (!/\.(webp|svg)$/i.test(src)) problems.push(`${where} not webp or svg: ${src}`);
		if (!alt.trim()) problems.push(`${where} no alt text: ${src}`);
	}
}
console.log(`check-doc-images: ${files.length} markdown files, ${total} image references, ${problems.length} problem(s)`);
for (const p of problems) console.log('  ' + p);
process.exit(problems.length ? 1 : 0);
