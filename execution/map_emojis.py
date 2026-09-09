import os
import re
import glob
import json

# Define the contextual icon mapping
EMOJI_ICON_MAP = {
    # Education / Academic
    "🎓": {"icon": "academicCap", "desc": "Birrete Académico (FKCU / Universidade / Ensino)"},
    "🏫": {"icon": "institution", "desc": "Instituição de Ensino Superior (UAN, UCAN, etc.)"},
    "🏛": {"icon": "landmark", "desc": "Edifício Institucional / Órgão Público / IAPI"},
    "📚": {"icon": "bookOpen", "desc": "Educação / Livros / EduKamba"},
    
    # Law & Governance
    "⚖️": {"icon": "scale", "desc": "Balança da Justiça / Direito / CLO / IAPI"},
    "⚖": {"icon": "scale", "desc": "Balança da Justiça / Direito / CLO / IAPI"},
    "📜": {"icon": "scrollText", "desc": "Decreto Executivo / Estatutos / Regulamento"},
    "🔒": {"icon": "lock", "desc": "Cadeado / Proteção de IP / Confidencialidade"},
    "🔐": {"icon": "keyRound", "desc": "Chave Segura / Cofre de Documentos"},
    "🛡": {"icon": "shieldCheck", "desc": "Escudo de Conformidade"},
    "🏷": {"icon": "tag", "desc": "Etiqueta / Classificação IAPI"},
    
    # Business, Finance & Sandbox
    "💡": {"icon": "lightbulb", "desc": "Ideia / Submissão de Ideias"},
    "💰": {"icon": "coins", "desc": "Financiamento / Sandbox / Desembolso"},
    "💵": {"icon": "banknote", "desc": "Orçamento / Valores Monetários (Kwanza/USD)"},
    "💳": {"icon": "creditCard", "desc": "Cartão de Financiamento / Meio de Pagamento"},
    "📈": {"icon": "trendingUp", "desc": "Crescimento / Viabilidade Financeira / Métricas"},
    "📊": {"icon": "barChart", "desc": "Relatórios / Business Model Canvas / Análise"},
    
    # Matching & Collaboration
    "🤝": {"icon": "handshake", "desc": "Acordo / Co-Founders / MoU / Vesting"},
    "⚡": {"icon": "zap", "desc": "Algoritmo de Matching / Velocidade / Speed Dating"},
    "👥": {"icon": "users", "desc": "Equipa / Builders / Utilizadores"},
    "👋": {"icon": "hand", "desc": "Saudação / Onboarding"},
    "🎯": {"icon": "target", "desc": "Alvo / Go-to-Market / Demo Day"},
    
    # Engineering, Tech & Startups
    "💻": {"icon": "laptop", "desc": "Engenharia & TI / Software"},
    "⚙️": {"icon": "cpu", "desc": "MVP Funcional / Tecnologia / Configuração"},
    "⚙": {"icon": "cpu", "desc": "MVP Funcional / Tecnologia / Configuração"},
    "🏗": {"icon": "layers", "desc": "Arquitetura Técnica / Diagrama de Infraestrutura"},
    "📁": {"icon": "folderGit", "desc": "Repositório GitHub / Ficheiros do Projeto"},
    "🔗": {"icon": "externalLink", "desc": "Hiperligação / Repositório Externo"},
    "🌐": {"icon": "globe", "desc": "Website / MVP Online / Domínio"},
    "🌍": {"icon": "globe", "desc": "Internacionalização / Pitch Global"},
    "📱": {"icon": "smartphone", "desc": "Dispositivo Móvel / Aplicação"},
    
    # Marketing & Communication
    "📢": {"icon": "megaphone", "desc": "Marketing & Comunicação / Divulgação"},
    "🎨": {"icon": "palette", "desc": "Design / Identidade de Marca / CLO Criativo"},
    "🗣": {"icon": "mic", "desc": "Pitch Deck / Apresentação Oral / Pitching"},
    
    # Sectors / Startups
    "🌾": {"icon": "sprout", "desc": "Agricultura / AgroLink"},
    "🔋": {"icon": "batteryCharging", "desc": "Energia / KixiEnergy"},
    "🚚": {"icon": "truck", "desc": "Logística & Transporte"},
    "🏥": {"icon": "heartPulse", "desc": "Saúde & Clínicas"},
    
    # Design Thinking & Activators
    "🗺": {"icon": "map", "desc": "Mapa de Etnografia"},
    "🌀": {"icon": "refreshCw", "desc": "Mapa de Ambiguidade / Iteração"},
    "🔄": {"icon": "repeat", "desc": "Iteração Contínua"},
    "🔬": {"icon": "flaskConical", "desc": "Pesquisa Experimental / Laboratório"},
    "📎": {"icon": "paperclip", "desc": "Anexo de Arquivo"},
    "🚀": {"icon": "rocket", "desc": "Lançamento de Startup"},
    "😈": {"icon": "helpCircle", "desc": "Advogado do Diabo / Teste de Stress"},
    "👴": {"icon": "userCheck", "desc": "Persona Sênior / Acessibilidade"},
    "🎩": {"icon": "briefcase", "desc": "Lente do Investidor"},
    
    # Status, Badges & Verification
    "✅": {"icon": "checkCircle", "desc": "Concluído / Validado / Checklist"},
    "✓": {"icon": "check", "desc": "Marca de Aprovação"},
    "✗": {"icon": "x", "desc": "Recusado / Rejeitado"},
    "❌": {"icon": "xCircle", "desc": "Risco / Obstáculo / Alerta de Bloqueio"},
    "⚠️": {"icon": "alertTriangle", "desc": "Aviso / Prazo Crítico"},
    "⚠": {"icon": "alertTriangle", "desc": "Aviso de Sistema"},
    "🔔": {"icon": "bell", "desc": "Notificação"},
    "🏅": {"icon": "award", "desc": "Selo INAPEM / Certificação Oficial"},
    "💫": {"icon": "sparkles", "desc": "Destaque de Habilidade / Badge"},
    "🔥": {"icon": "flame", "desc": "Nível de Incubação / Streak de Tração"},
    "📅": {"icon": "calendar", "desc": "Calendário / Agendamento"},
    "⏳": {"icon": "clock", "desc": "Pendente / Em Revisão / Tempo"},
    "⏰": {"icon": "clock", "desc": "Prazo / Relógio"},
    "🤖": {"icon": "bot", "desc": "Algoritmo Inteligente FKCU"},
    "📄": {"icon": "fileText", "desc": "Documento / Contrato / Minuta"},
    "📋": {"icon": "clipboardCheck", "desc": "Lista de Verificação / Triagem"},
    "📤": {"icon": "uploadCloud", "desc": "Submeter / Partilhar Arquivo"},
    "📺": {"icon": "playSquare", "desc": "Transmissão Online / Vídeo"},
    "🇦🇴": {"icon": "flagAo", "desc": "Bandeira / Contexto Nacional de Angola"},
    "🎤": {"icon": "mic", "desc": "Microfone / Speed Dating / Apresentação"},
    "👤": {"icon": "user", "desc": "Utilizador / Perfil Individual"},
    "💬": {"icon": "messageSquare", "desc": "Mensagem / Conversa / Feedback"},
    "📍": {"icon": "mapPin", "desc": "Localização / Província / Universidade"},
    "📣": {"icon": "megaphone", "desc": "Megafone / CMO Marketing"},
    "📭": {"icon": "inbox", "desc": "Caixa Vazia / Sem Notificações"},
}

def analyze_all_files():
    all_html = glob.glob('*.html') + glob.glob('public/*.html') + glob.glob('public/paginas/**/*.html', recursive=True)
    all_html = sorted(list(set(all_html)))
    
    findings = []
    unmapped = set()
    
    for f in all_html:
        with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
            lines = fp.readlines()
        for idx, line in enumerate(lines, 1):
            for emoji, meta in EMOJI_ICON_MAP.items():
                if emoji in line:
                    findings.append({
                        "file": f,
                        "line": idx,
                        "emoji": emoji,
                        "icon": meta["icon"],
                        "desc": meta["desc"],
                        "snippet": line.strip()[:140]
                    })
            # Check for any other unexpected high unicode characters
            for char in line:
                code = ord(char)
                if ((0x1F300 <= code <= 0x1FAFF) or (0x2600 <= code <= 0x27BF)) and char not in EMOJI_ICON_MAP:
                    unmapped.add((char, f"U+{code:04X}", f, idx, line.strip()[:80]))
                    
    print(f"Total mapped occurrences found: {len(findings)}")
    print(f"Total unmapped occurrences: {len(unmapped)}")
    with open('.tmp/unmapped_emojis.txt', 'w', encoding='utf-8') as uf:
        for u in sorted(list(unmapped)):
            uf.write(f"{u[1]} ({u[0]}) in {u[2]}:{u[3]} -> {u[4]}\n")
            
    with open('.tmp/mapped_emojis.json', 'w', encoding='utf-8') as out:
        json.dump(findings, out, indent=2, ensure_ascii=False)

if __name__ == '__main__':
    analyze_all_files()
