# CZ district maps (okresy)

`templates/geography/cz-districts/{region}.html.twig` are generated, rendered via
`templates/geography/cz-district-map.html.twig`:

```twig
{% include '@CmsBundle/geography/cz-district-map.html.twig' with {
    region: 'jihomoravsky',
    urls: {'brno-mesto': path('...'), 'znojmo': path('...')},
    active_path: 'znojmo',
    path_fill_color: '#b3b3b3',
    stroke: 'thin',
} %}
```

Region slugs are the same as in `cz-region-map.html.twig`, so the region map `urls` can link to a page that shows the
district map of the clicked region. District slugs are listed at the top of each generated file. Praha has no okresy,
its map shows městské obvody `praha-1` … `praha-10`.

## Data source

ČÚZK RÚIAN, state-level exchange format with generalized boundaries (`ST_UKSG`), © ČÚZK, CC BY 4.0.
Okresy use `GeneralizovaneHranice4`, Praha městské obvody use `OriginalniHranice`. Coordinates are S-JTSK / Krovak
East North (EPSG:5514), which is already a planar projection, so no reprojection is needed.

## Rebuild

1. Download the latest export (published monthly, last day of month):
   `https://vdp.cuzk.gov.cz/vymenny_format/soucasna/YYYYMMDD_ST_UKSG.xml.zip` (~100 MB, ~350 MB unzipped)
2. Extract kraje, okresy and Praha městské obvody to GeoJSON (streams the XML, stdlib only):
   ```
   unzip -p YYYYMMDD_ST_UKSG.xml.zip | python3 docs/components/geography/tools/extract-ruian-boundaries.py geo
   ```
   Produces `geo/okres-GeneralizovaneHranice4.json` and `geo/mop-OriginalniHranice.json` (props `kod`, `nazev`, `vusc`).
3. Simplify with [mapshaper](https://github.com/mbloch/mapshaper) (keeps shared borders topologically consistent):
   ```
   npx mapshaper geo/okres-GeneralizovaneHranice4.json -simplify interval=120 keep-shapes -clean -o okresy-s.json precision=1
   npx mapshaper geo/mop-OriginalniHranice.json -simplify interval=25 keep-shapes -clean -o praha-mop-s.json precision=1
   ```
   `interval` is in meters, ~1 vertex per pixel at 1000 px map width.
4. Generate templates:
   ```
   node docs/components/geography/tools/build-cz-district-maps.mjs okresy-s.json praha-mop-s.json templates/geography/cz-districts
   ```
