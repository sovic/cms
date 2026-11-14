# Components

Reusable Twig components shipped with `sovic/cms`, included from host application templates via the `@CmsBundle`
namespace.

| Component                             | Templates              | Description                                    |
|---------------------------------------|------------------------|------------------------------------------------|
| [Geography maps](geography/README.md) | `templates/geography/` | Clickable SVG maps of CZ regions and districts |

## Structure

One directory per component, both in `templates/` and in `docs/components/`:

```
templates/
└── {component}/
    ├── {name}.html.twig              # public entry template, the only one host apps include
    ├── {name}-wrapper.html.twig      # minimal usage example
    └── {name}/                       # internal partials and generated files
        ├── _{partial}.html.twig      # underscore = internal, not included by host apps
        └── {variant}.html.twig       # generated data files, marked GENERATED in the header

docs/components/
├── README.md                         # this file, index of components
└── {component}/
    ├── README.md                     # usage: parameters, Twig + controller examples, styling, JS hooks
    ├── rebuild.md                    # optional: how to regenerate data / assets
    └── tools/                        # optional: generator and import scripts (not autoloaded, not deployed logic)
```

Example: `templates/geography/cz-district-map.html.twig` (entry), `templates/geography/cz-districts/_svg.html.twig`
(partial), `templates/geography/cz-districts/{region}.html.twig` (generated), docs in `docs/components/geography/`.

## Conventions for component templates

- **Document parameters** in a `{# … #}` comment at the top of the entry template: name, type, default, meaning.
- **Parameters are `snake_case`**, same as `$this->assign()` keys, so controller variables can be passed through
  directly (`with {urls: region_urls}`).
- **Defaults and validation in the entry template** (`param ?? default`, whitelist for enum-like values), so the
  component renders with `strict_variables` enabled and an invalid value renders nothing instead of failing.
- **Pass data in, no queries**: components receive ready values (urls, labels, active item), the host application
  controller builds them.
- **No `id` attributes** in repeated markup, use `data-*` attributes for JS hooks, so the component can render more
  than once per page.
- **No bundled CSS/JS**: inline only what the component cannot work without, document recommended CSS and JS init
  in the component docs.
- **Generated files are never edited by hand**, the header says which script generates them; the script and its
  instructions live in `docs/components/{component}/`.
- **Credit external data/assets** (source and license) in the generated file header and in the docs.

## Docs template

New component docs (`docs/components/{component}/README.md`) follow this outline:

```markdown
# {Component name}

One paragraph: what it renders and the typical use case.

| Template | Shows |

## Parameters
Table: parameter, type, default, description.

## Usage
Minimal `{% include %}` example, then a realistic one.

## Controller example        (when the component needs prepared data)
Host application controller, `$this->assign()` keys sorted, snake_case.

## Styling / JS              (when relevant)
Recommended CSS, JS initialization, data-*hooks.

## Data / license            (when relevant)
Source, license, link to rebuild.md.
```
