# Geography maps

Clickable SVG maps of the Czech Republic, rendered server-side as inline SVG (no JS library, no external requests).

| Template                                            | Shows                                   |
|-----------------------------------------------------|-----------------------------------------|
| `@CmsBundle/geography/cz-region-map.html.twig`      | whole country, 14 regions (kraje)       |
| `@CmsBundle/geography/cz-district-map.html.twig`    | one region, its districts (okresy)      |

Typical use is a two-level navigation: the region map links to a region page, the region page shows the district map
of that region and links to district pages.

- [Parameters](#parameters)
- [Region map](#region-map)
- [District map](#district-map)
- [Region → district navigation](#region--district-navigation)
- [Styling](#styling)
- [Tooltips](#tooltips)
- [Slugs](#slugs)
- [Rebuilding the district maps](rebuild.md)

## Parameters

Both templates take the same parameters, the district map additionally requires `region`.

| Parameter         | Type                    | Default     | Description                                                                         |
|-------------------|-------------------------|-------------|-------------------------------------------------------------------------------------|
| `region`          | string                  | –           | District map only, region slug (see [Slugs](#slugs)). Unknown slug renders nothing. |
| `urls`            | `{slug: url}`           | `{}`        | Areas with an url are wrapped in `<a>`, the others are not clickable.               |
| `active_path`     | string                  | `''`        | Slug of the area that gets the `active` class.                                      |
| `path_fill_color` | string                  | `'#b3b3b3'` | Fill color of the areas.                                                            |
| `stroke`          | `''`, `thinner`, `thin` | `''`        | Border width between the areas.                                                     |

Both maps are `width: 100%; height: auto`, the size is controlled by the wrapping element.

## Region map

```twig
<div class="map-wrapper w-100">
    {% include '@CmsBundle/geography/cz-region-map.html.twig' with {
        urls: {
            'jihomoravsky': path('region_detail', {region: 'jihomoravsky'}),
            'prazsky': path('region_detail', {region: 'prazsky'}),
        },
        active_path: 'jihomoravsky',
        stroke: 'thin',
    } %}
</div>
```

## District map

```twig
<div class="map-wrapper w-100" style="max-height: 70vh;">
    {% include '@CmsBundle/geography/cz-district-map.html.twig' with {
        region: 'jihomoravsky',
        urls: {
            'brno-mesto': path('district_detail', {region: 'jihomoravsky', district: 'brno-mesto'}),
            'znojmo': path('district_detail', {region: 'jihomoravsky', district: 'znojmo'}),
        },
        active_path: 'znojmo',
    } %}
</div>
```

Every region is drawn at the same width, so tall regions (Olomoucký, Plzeňský) render taller, limit the wrapper
height when the layout needs it.

Each district `<path>` has `data-district` (slug) and `data-code` (RÚIAN okres code, e.g. `3701` for Blansko) for
JS hooks. The SVG has no `id` attributes, so the map can be rendered more than once on a page.

Praha has no okresy, its map shows městské obvody `praha-1` … `praha-10`.

## Region → district navigation

Controller in the host application. Keep the slug lists in one place (a constant, an enum or the database), the
templates only need the `urls` maps.

```php
<?php

namespace App\Controller;

use Sovic\Cms\Controller\ProjectBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegionController extends ProjectBaseController
{
    private const array REGIONS = [
        'jihocesky' => 'Jihočeský kraj',
        'jihomoravsky' => 'Jihomoravský kraj',
        'karlovarsky' => 'Karlovarský kraj',
        'kralovehradecky' => 'Královéhradecký kraj',
        'liberecky' => 'Liberecký kraj',
        'moravskoslezsky' => 'Moravskoslezský kraj',
        'olomoucky' => 'Olomoucký kraj',
        'pardubicky' => 'Pardubický kraj',
        'plzensky' => 'Plzeňský kraj',
        'prazsky' => 'Hlavní město Praha',
        'stredocesky' => 'Středočeský kraj',
        'ustecky' => 'Ústecký kraj',
        'vysocina' => 'Kraj Vysočina',
        'zlinsky' => 'Zlínský kraj',
    ];

    #[Route('/kraje', name: 'region_index')]
    public function index(): Response
    {
        $this->assign('region_urls', $this->getRegionUrls());

        return $this->render('region/index.html.twig');
    }

    #[Route('/kraje/{region}', name: 'region_detail')]
    public function detail(string $region): Response
    {
        if (!isset(self::REGIONS[$region])) {
            throw $this->createNotFoundException();
        }

        // district slugs, e.g. from the database: entities with a `slug` column matching the map slugs
        $districts = ['blansko', 'brno-mesto', 'brno-venkov', 'breclav', 'hodonin', 'vyskov', 'znojmo'];

        $districtUrls = [];
        foreach ($districts as $slug) {
            $districtUrls[$slug] = $this->generateUrl('district_detail', ['region' => $region, 'district' => $slug]);
        }

        $this->assign('district_urls', $districtUrls);
        $this->assign('region', $region);
        $this->assign('region_name', self::REGIONS[$region]);
        $this->assign('region_urls', $this->getRegionUrls());

        return $this->render('region/detail.html.twig');
    }

    private function getRegionUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::REGIONS) as $slug) {
            $urls[$slug] = $this->generateUrl('region_detail', ['region' => $slug]);
        }

        return $urls;
    }
}
```

`templates/region/index.html.twig`:

```twig
{% extends 'base.html.twig' %}

{% block content %}
    <h1>Kraje</h1>
    <div class="map-wrapper w-100">
        {% include '@CmsBundle/geography/cz-region-map.html.twig' with {urls: region_urls} %}
    </div>
{% endblock %}
```

`templates/region/detail.html.twig` – district map of the region, small region map as a switcher:

```twig
{% extends 'base.html.twig' %}

{% block content %}
    <h1>{{ region_name }}</h1>
    <div class="row">
        <div class="col-lg-8">
            <div class="map-wrapper w-100" style="max-height: 70vh;">
                {% include '@CmsBundle/geography/cz-district-map.html.twig' with {
                    region: region,
                    urls: district_urls,
                } %}
            </div>
        </div>
        <div class="col-lg-4">
            <div class="map-wrapper w-100">
                {% include '@CmsBundle/geography/cz-region-map.html.twig' with {
                    active_path: region,
                    stroke: 'thin',
                    urls: region_urls,
                } %}
            </div>
        </div>
    </div>
{% endblock %}
```

## Styling

Areas use inline `style` (fill and stroke come from the parameters), so CSS overrides of `fill` need `!important`.
The templates ship no CSS, add it to the project stylesheet:

```css
.map-wrapper a path {
    cursor: pointer;
    transition: fill .15s ease-in-out;
}

.map-wrapper a:hover path,
.map-wrapper path.active {
    fill: var(--bs-primary) !important;
}
```

District map borders keep the same on-screen width at any map size (`vector-effect: non-scaling-stroke`: 2 px,
`thinner` 1.5 px, `thin` 1 px). Region map borders scale with the map.

## Tooltips

Areas carry Bootstrap 5 tooltip attributes (`data-bs-toggle="tooltip"`, `data-bs-title` = area name). Bootstrap
tooltips are opt-in, initialize them once on the page:

```js
document.querySelectorAll('.map-wrapper [data-bs-toggle="tooltip"]')
    .forEach((el) => new bootstrap.Tooltip(el));
```

Without the init, nothing breaks, the areas just have no tooltip.

## Slugs

Region slugs (`urls` keys of the region map, `region` of the district map) and district slugs (`urls` keys of the
district map). District slugs are ASCII slugs of the official names, each generated file also lists them in its
header comment.

| Region            | Districts                                                                                                                                                                                  |
|-------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `jihocesky`       | `ceske-budejovice`, `cesky-krumlov`, `jindrichuv-hradec`, `pisek`, `prachatice`, `strakonice`, `tabor`                                                                                     |
| `jihomoravsky`    | `blansko`, `brno-mesto`, `brno-venkov`, `breclav`, `hodonin`, `vyskov`, `znojmo`                                                                                                           |
| `karlovarsky`     | `cheb`, `karlovy-vary`, `sokolov`                                                                                                                                                          |
| `kralovehradecky` | `hradec-kralove`, `jicin`, `nachod`, `rychnov-nad-kneznou`, `trutnov`                                                                                                                      |
| `liberecky`       | `ceska-lipa`, `jablonec-nad-nisou`, `liberec`, `semily`                                                                                                                                    |
| `moravskoslezsky` | `bruntal`, `frydek-mistek`, `karvina`, `novy-jicin`, `opava`, `ostrava-mesto`                                                                                                              |
| `olomoucky`       | `jesenik`, `olomouc`, `prostejov`, `prerov`, `sumperk`                                                                                                                                     |
| `pardubicky`      | `chrudim`, `pardubice`, `svitavy`, `usti-nad-orlici`                                                                                                                                       |
| `plzensky`        | `domazlice`, `klatovy`, `plzen-jih`, `plzen-mesto`, `plzen-sever`, `rokycany`, `tachov`                                                                                                    |
| `prazsky`         | `praha-1`, `praha-2`, `praha-3`, `praha-4`, `praha-5`, `praha-6`, `praha-7`, `praha-8`, `praha-9`, `praha-10`                                                                              |
| `stredocesky`     | `benesov`, `beroun`, `kladno`, `kolin`, `kutna-hora`, `melnik`, `mlada-boleslav`, `nymburk`, `praha-vychod`, `praha-zapad`, `pribram`, `rakovnik`                                          |
| `ustecky`         | `decin`, `chomutov`, `litomerice`, `louny`, `most`, `teplice`, `usti-nad-labem`                                                                                                            |
| `vysocina`        | `havlickuv-brod`, `jihlava`, `pelhrimov`, `trebic`, `zdar-nad-sazavou`                                                                                                                     |
| `zlinsky`         | `kromeriz`, `uherske-hradiste`, `vsetin`, `zlin`                                                                                                                                           |

District data: © ČÚZK, RÚIAN, CC BY 4.0. Region map: Wikimedia Commons, see the comment in
`cz-region-map.html.twig`.
