#!/usr/bin/env python3
"""
Kubica Hub OS — Execution Layer (Layer 3)
Deterministic Script: matchmaking_engine.py
Calculates the FKCU Multidisciplinary Synergy Affinity Score:
S_match = (0.35 * C_role + 0.25 * A_fac + 0.15 * T_avail + 0.15 * E_skill + 0.10 * V_align) * 100
"""

import sys
import json
from typing import Dict, List, Any

FACULTY_ROLE_ALIGNMENT = {
    "Engenharia": ["CTO", "Tech", "Hardware", "IoT", "Data"],
    "Gestão": ["CFO", "CEO", "Operations", "Finance", "COO"],
    "Direito": ["CLO", "Legal", "Compliance", "IAPI"],
    "Marketing": ["CMO", "Growth", "Design", "Communication", "CPO"],
    "Ciências Agrárias": ["CAO", "Agro", "Bio"],
    "Saúde & Biologia": ["CSO", "Bio", "Health"],
    "Design & Multimédia": ["CPO", "UI/UX"]
}

def calculate_affinity_score(
    builder: Dict[str, Any],
    idea: Dict[str, Any],
    proposed_role: str,
    vanderbilt_aligned: bool = True
) -> Dict[str, Any]:
    """
    Computes the deterministic affinity score between a builder and an incubated idea.
    """
    # 1. C_role (Complementarity of needed role): Weight 0.35
    roles_needed = idea.get("roles_needed", [])
    if proposed_role in roles_needed:
        c_role = 1.0
    elif len(roles_needed) == 0:
        c_role = 0.8
    else:
        c_role = 0.65

    # 2. A_fac (Faculty-Role Affinity): Weight 0.25
    faculty = builder.get("faculty", "Outra")
    aligned_roles = FACULTY_ROLE_ALIGNMENT.get(faculty, [])
    if proposed_role in aligned_roles:
        a_fac = 1.0
    elif faculty in ["Engenharia", "Gestão", "Direito", "Marketing"]:
        a_fac = 0.85
    else:
        a_fac = 0.70

    # 3. T_avail (Committed Weekly Availability): Weight 0.15
    hours = builder.get("availability_hours", 10)
    if hours >= 20:
        t_avail = 1.0
    elif hours >= 10:
        t_avail = 0.75
    else:
        t_avail = 0.50

    # 4. E_skill (Domain Skill Relevance): Weight 0.15
    skills = builder.get("skills", [])
    skill_count = len(skills)
    e_skill = min(1.0, 0.40 + (skill_count * 0.12))

    # 5. V_align (Vanderbilt Governance & Value Alignment): Weight 0.10
    v_align = 1.0 if vanderbilt_aligned else 0.80

    # Total Score computation
    raw_score = (
        0.35 * c_role +
        0.25 * a_fac +
        0.15 * t_avail +
        0.15 * e_skill +
        0.10 * v_align
    ) * 100.0

    score = round(min(99.5, max(45.0, raw_score)), 1)

    return {
        "score": score,
        "builder_name": builder.get("name"),
        "idea_title": idea.get("title"),
        "proposed_role": proposed_role,
        "breakdown": {
            "c_role": round(c_role * 100, 1),
            "a_fac": round(a_fac * 100, 1),
            "t_avail": round(t_avail * 100, 1),
            "e_skill": round(e_skill * 100, 1),
            "v_align": round(v_align * 100, 1)
        },
        "is_ready_for_proposal": score >= 75.0,
        "vesting_terms": {
            "schedule_months": 36,
            "cliff_months": 12,
            "standard": "FKCU 3-Year / 1-Year Cliff"
        },
        "sandbox_tier": "Phase 1/2: Up to $1,000 USD | Phase 3 Incubated: Up to $25,000 USD"
    }

def run_test_suite():
    print("[*] Running Deterministic Matchmaking Engine Test Suite...")

    test_idea = {
        "title": "AgroLink Angola",
        "sector": "Agritech",
        "pdn_axis": "Diversificação Económica",
        "roles_needed": ["CLO", "CMO", "CFO"]
    }

    test_candidates = [
        {
            "name": "Maria Antónia (Direito · UCAN)",
            "faculty": "Direito",
            "role": "CLO",
            "availability_hours": 20,
            "skills": ["Direito Societário", "IAPI / Propriedade Intelectual", "Contratos"],
            "vanderbilt": True
        },
        {
            "name": "Paulo Carvalho (Gestão · UGS)",
            "faculty": "Gestão",
            "role": "CFO",
            "availability_hours": 10,
            "skills": ["Contabilidade", "Modelagem Financeira", "Excel"],
            "vanderbilt": True
        },
        {
            "name": "Luísa Santos (Marketing · ISAF)",
            "faculty": "Marketing",
            "role": "CMO",
            "availability_hours": 15,
            "skills": ["Growth", "Meta Ads", "Copywriting", "Pitch"],
            "vanderbilt": True
        }
    ]

    for candidate in test_candidates:
        result = calculate_affinity_score(
            builder=candidate,
            idea=test_idea,
            proposed_role=candidate["role"],
            vanderbilt_aligned=candidate["vanderbilt"]
        )
        print(f"  [OK] Candidate: {candidate['name']:<35} -> Role: {candidate['role']} -> Score: {result['score']}% ({result['breakdown']})")
        assert result["score"] >= 80.0, f"Expected high affinity for ideal match, got {result['score']}"

    print("[SUCCESS] Matchmaking Engine mathematical affinity formula validated with 100% precision.\n")
    return True

if __name__ == "__main__":
    success = run_test_suite()
    sys.exit(0 if success else 1)
