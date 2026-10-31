# Project documents

| Deliverable | File |
|---|---|
| 1. Database schema (ER diagram) | [er-diagram.md](er-diagram.md), image: [er-diagram.png](er-diagram.png) / [er-diagram.svg](er-diagram.svg), Mermaid source: [er.mmd](er.mmd) |
| 1b. Schema design (tables, keys, foreign keys) | [schema-design.md](schema-design.md), image: [schema-design.png](schema-design.png) / [schema-design.svg](schema-design.svg) |
| 2. Relational mapping | [relational-mapping.md](relational-mapping.md), image: [relational-mapping.png](relational-mapping.png) / [relational-mapping.svg](relational-mapping.svg) |
| 3. Low-fidelity prototype | [low-fi-prototype.html](low-fi-prototype.html), image: [low-fi-prototype.png](low-fi-prototype.png) |

Rebuild the diagrams after editing: `python3 docs/src/er_chen.py` and `python3 docs/src/schema_design.py` and `python3 docs/src/relation_mapping.py` write the SVGs; export PNGs by opening the SVG in headless Chrome with `--screenshot` (window size = SVG size). The wireframes are screenshots of the HTML file. `er.mmd` is the Mermaid crow's-foot source.
