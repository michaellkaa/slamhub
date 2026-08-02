import fs from 'node:fs'
import path from 'node:path'

const root = process.cwd()

function copy(fromRel, toRel) {
  const from = path.join(root, fromRel)
  const to = path.join(root, toRel)
  if (!fs.existsSync(from)) {
    console.warn(`[copy-pwa-assets] missing ${fromRel}`)
    return
  }
  fs.copyFileSync(from, to)
  console.info(`[copy-pwa-assets] ${fromRel} -> ${toRel}`)
}

copy('public/build/sw.js', 'public/sw.js')
copy('public/build/manifest.webmanifest', 'public/manifest.webmanifest')
