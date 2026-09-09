import re
import glob
import json
import os

emoji_pattern = re.compile(
    r'[\U00010000-\U0010ffff\u2600-\u27bf\u2300-\u23ff\u2b50\u2b55\u2190-\u21ff]'
)

files = sorted(glob.glob('*.html'))
report = {}

for f in files:
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        lines = fp.readlines()
    for line_num, line in enumerate(lines, 1):
        for m in emoji_pattern.finditer(line):
            emoji = m.group()
            # Ignore standard HTML entities or arrows if they are just simple ASCII
            code_point = f"U+{ord(emoji):04X}"
            if emoji not in report:
                report[emoji] = {
                    'code': code_point,
                    'char': emoji,
                    'count': 0,
                    'occurrences': []
                }
            report[emoji]['count'] += 1
            if len(report[emoji]['occurrences']) < 8:
                report[emoji]['occurrences'].append({
                    'file': f,
                    'line': line_num,
                    'snippet': line.strip()[:160]
                })

os.makedirs('.tmp', exist_ok=True)
with open('.tmp/emoji_report.json', 'w', encoding='utf-8') as out:
    json.dump(report, out, indent=2, ensure_ascii=False)

summary = []
for emoji, data in sorted(report.items(), key=lambda x: x[1]['count'], reverse=True):
    ex = data['occurrences'][0]
    summary.append(f"{data['code']} | Count: {data['count']} | Ex: {ex['file']}:{ex['line']} -> {ex['snippet']}")

with open('.tmp/emoji_summary.txt', 'w', encoding='utf-8') as out:
    out.write('\n'.join(summary))

print(f"Total distinct emoji/symbols found: {len(report)}")
print(f"Total occurrences: {sum(d['count'] for d in report.values())}")
