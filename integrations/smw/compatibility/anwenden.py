#!/usr/bin/env python3
"""Optional checked SMW 7.3.1 SPARQL-store statistics patch; dry-run by default."""
from pathlib import Path
import argparse,hashlib,json,shutil,tempfile,os,stat
from datetime import datetime
r=Path(__file__).resolve().parent
p=argparse.ArgumentParser(description=__doc__);p.add_argument('datei',type=Path);p.add_argument('--anwenden',action='store_true');a=p.parse_args()
meta=json.loads((r/'BASELINE.json').read_text());target=a.datei
if target.is_symlink() or not target.is_file():raise SystemExit('Expected a regular PropertyStatisticsRebuilder.php file.')
hashof=lambda b:hashlib.sha256(b).hexdigest()
current=hashof(target.read_bytes());replacement=(r/'PropertyStatisticsRebuilder.php').read_bytes()
if hashof(replacement)!=meta['after']:raise SystemExit('Replacement checksum mismatch.')
if current==meta['after']:print('SMW correction already installed. No change.');raise SystemExit(0)
if current!=meta['before']:raise SystemExit('Unknown SMW source file. No change.')
print('Exact SMW 7.3.1 source verified.')
if not a.anwenden:print('Dry-run only. Add --anwenden to back up and apply.');raise SystemExit(0)
backup=target.with_name(target.name+'.semantic-backup-'+datetime.now().strftime('%Y%m%d-%H%M%S-%f'));shutil.copy2(target,backup)
fd,tmp=tempfile.mkstemp(prefix='.semantic-smw-',dir=target.parent)
try:
 with os.fdopen(fd,'wb')as stream:stream.write(replacement)
 os.chmod(tmp,stat.S_IMODE(target.stat().st_mode));os.replace(tmp,target)
finally:
 if os.path.exists(tmp):os.unlink(tmp)
print('SMW correction applied. Backup:',backup)
