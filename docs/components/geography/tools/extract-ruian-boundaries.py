#!/usr/bin/env python3
# Usage: unzip -p YYYYMMDD_ST_UKSG.xml.zip | python3 extract-ruian-boundaries.py <out-dir>
# Writes {vusc,okres,mop}-<geometry-level>.json, see docs/components/geography/rebuild.md
# Stream RÚIAN ST_UKSG XML from stdin; dump Vusc + Okres generalized boundaries to GeoJSON (EPSG:5514 coords).
import sys, json, re, xml.etree.ElementTree as ET
out = sys.argv[1]
import os; os.makedirs(out, exist_ok=True)
G='{http://www.opengis.net/gml/3.2}'
feats={'Vusc':[], 'Okres':[], 'Mop':[]}
def local(t): return t.split('}',1)[1] if '}' in t else t
def polys(geomEl):
    res=[]
    for poly in geomEl.iter(G+'Polygon'):
        rings=[]
        for ring in list(poly.iter(G+'exterior'))+list(poly.iter(G+'interior')):
            pl=ring.find('.//'+G+'posList'); v=list(map(float,pl.text.split()))
            rings.append([[v[i],v[i+1]] for i in range(0,len(v),2)])
        res.append(rings)
    return res
ctx=ET.iterparse(sys.stdin.buffer, events=('end',))
for ev,el in ctx:
    n=local(el.tag)
    if n in ('Vusc','Okres','Mop') and el.find('.//'+G+'Polygon') is not None:
        kod=nazev=vusc=None; geoms={}
        for c in el:
            ln=local(c.tag)
            if ln=='Kod': kod=c.text
            elif ln=='Nazev': nazev=c.text
            elif ln=='Vusc': vusc=c[0].text
            elif ln=='Geometrie':
                for g in c:
                    gl=local(g.tag)
                    if gl.startswith('GeneralizovaneHranice') or gl=='OriginalniHranice':
                        geoms[gl]=polys(g)
        feats[n].append(dict(kod=kod,nazev=nazev,vusc=vusc,geoms=geoms))
        el.clear()
    elif n=='Mopy':
        break
    elif n in ('Orp','Obec'): el.clear()
for n,fs in feats.items():
    levels=sorted({k for f in fs for k in f['geoms']})
    print(n,len(fs),'levels:',{l:sum(len(r) for f in fs for p in f['geoms'].get(l,[]) for r in p) for l in levels})
    for l in levels:
        fc={'type':'FeatureCollection','features':[{'type':'Feature','properties':{'kod':f['kod'],'nazev':f['nazev'],'vusc':f['vusc']},'geometry':{'type':'MultiPolygon','coordinates':f['geoms'][l]}} for f in fs if l in f['geoms']]}
        json.dump(fc, open(f'{out}/{n.lower()}-{l}.json','w'), ensure_ascii=False)
