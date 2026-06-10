---
name: new-admin-module
description: Scaffold a complete new admin module inside the sovic/cms package (Symfony 7.2). Use whenever the user asks to create a new admin section, resource, CRUD module, or admin page. Generates Entity, Repository, Form, Controller (+ API controller for delete/toggle), and Twig templates following the package's established conventions (page, post, page-group, menu, tag modules as reference).
argument-hint: <ModuleName> [table_name]
---

# New Admin Module

Scaffold a complete admin CRUD module **inside the `sovic/cms` package itself** (this repository), not a consuming
app. The argument is the **singular PascalCase name** of the resource (e.g. `Tag`, `Product`, `Article`). The optional
second argument is the snake_case table name if it differs from the default.

> **Context:** This repo IS the `sovic/cms` Composer package. Everything lives under namespace `Sovic\Cms\…` and
> directory `src/`. There is **no `config/`, no `App\` namespace, and no `config/packages/doctrine.yaml`** here — entity
> mappings and route loading are configured by the downstream app that installs this package (it scans
> `@CmsBundle/Controller/` for attribute routes). Do **not** add Doctrine config or route YAML.

Derive all names from the argument:

- `{Name}` — PascalCase class name (e.g. `Tag`)
- `{name}` — camelCase / lowercase (e.g. `tag`)
- `{name_snake}` — snake_case table & template dir (e.g. `tag`, or `page_group` → template dir `page-group`)
- `{alias}` — short query-builder alias, the entity's first letter(s) (e.g. `t` for Tag, `p` for Page)

**Route segments and route names are SINGULAR** in this package: `/admin/tag/list`, route name `admin:tag:list` — *not*
plural. Template directories use kebab-case (`page-group`), table names use snake_case (`page_group`).

Ask the user for the module name if not provided, plus which **actions** (list / edit / delete), which **fields** are
editable, and whether a **sidebar link** is wanted. Confirm derived names before generating files.

---

## STEP 0 — Check what already exists (do this first)

Many entities already live in `src/Entity/` (Tag, Page, Post, PageGroup, MenuItem, …) along with their repositories,
models, and factories. **Always check before creating anything** — you often only need to add the missing admin layer
(controller + form + templates, and maybe a couple of repository methods), not a new entity.

```
ls src/Entity/{Name}.php src/Repository/{Name}Repository.php src/Form/Admin/{Name}.php \
   src/Controller/Admin/{Name}Controller.php src/Controller/Admin/Api/Web/{Name}Controller.php \
   templates/admin/{name_snake}/
```

If the entity exists, read it and reuse its real column/getter names (e.g. Tag's boolean getter is `isIsPublic()`).
Do **not** regenerate or "fix" an existing entity unless asked.

---

## Files to Create (only the ones missing)

```
src/Entity/{Name}.php                              # often already exists — check first
src/Repository/{Name}Repository.php                # add search/count methods if missing
src/Form/Admin/{Name}.php
src/Controller/Admin/{Name}Controller.php          # list + edit
src/Controller/Admin/Api/Web/{Name}Controller.php  # delete + toggle-state (if those actions are wanted)
templates/admin/{name_snake}/list.html.twig
templates/admin/{name_snake}/edit.html.twig
```

Do **not** generate migrations — the user runs them manually after reviewing entity changes.

---

## 1. Entity — `src/Entity/{Name}.php`

Package entities use **explicit Doctrine attributes with short class names** (not the `ORM\` alias), `Types::*`
constants, `private`/`protected` typed properties, and manual getters/setters. **No `declare(strict_types=1)`.**

```php
<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Repository\{Name}Repository;
use Sovic\Common\Entity\Project;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;

#[Table(name: '{name_snake}')]
#[Index(name: 'project_id', columns: ['project_id'])]
#[Entity(repositoryClass: {Name}Repository::class)]
class {Name}
{
    use IdentityColumnTrait;

    #[ManyToOne(targetEntity: Project::class)]
    #[JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE', options: ['default' => null])]
    protected ?Project $project = null;

    #[Column(name: 'name', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $name = null;

    #[Column(name: 'is_public', type: Types::BOOLEAN, nullable: false, options: ['default' => 0])]
    protected bool $isPublic = false;

    // getters / setters …
}
```

**Conventions:**

- Namespace `Sovic\Cms\Entity`; no `declare(strict_types=1)`; import each Doctrine mapping class individually.
- `use IdentityColumnTrait` from `Sovic\Common\Entity\Trait\` for the `id` column (or declare `id` explicitly like the
  older `Tag` entity does — match the surrounding entity if editing one).
- Common traits — `Sovic\Common\Entity\Trait\`: `IdentityColumnTrait`, `CreatedAtTrait`, `UpdatedAtTrait`,
  `DeletedAtTrait`, `AddressTrait`.
- CMS traits — `Sovic\Cms\Entity\Trait\`: `IsPublicTrait`, `MetaColumnsTrait` (SEO), `PublishedAtTrait`,
  `PrivateSlugTrait`, `MenuItemTrait`, `LoggableEntityTrait`.
- Most resources are **project-scoped** via a nullable `ManyToOne` to `Sovic\Common\Entity\Project`. Add the
  `project_id` `#[Index]` when present.
- Boolean columns: `is_` prefix. DateTime columns: `At` suffix. Columns nullable unless there's a strong reason.
- Add `#[Index]` on the `#[Table]` lines for commonly filtered columns. Use `bcmath` for money — never float.

---

## 2. Repository — `src/Repository/{Name}Repository.php`

```php
<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Sovic\Cms\Entity\{Name};
use Sovic\Common\DataList\SearchRequestInterface;
use Sovic\Common\Entity\Project;

/**
 * @method {Name}|null find($id, $lockMode = null, $lockVersion = null)
 * @method {Name}|null findOneBy(array $criteria, array $orderBy = null)
 * @method {Name}[]    findAll()
 * @method {Name}[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class {Name}Repository extends EntityRepository
{
    /**
     * @return {Name}[]
     */
    public function findBySearchRequest(
        SearchRequestInterface $searchRequest,
        ?Project $project = null,
    ): array {
        $qb = $this->buildQuery($searchRequest, $project);
        $qb->orderBy('{alias}.name', 'ASC');
        $qb->setFirstResult($searchRequest->getOffset());
        $qb->setMaxResults($searchRequest->getLimit());

        return $qb->getQuery()->getResult();
    }

    public function countBySearchRequest(
        SearchRequestInterface $searchRequest,
        ?Project $project = null,
    ): int {
        $qb = $this->buildQuery($searchRequest, $project);
        $qb->select('COUNT({alias}.id)');

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException) {
            return 0;
        }
    }

    private function buildQuery(
        SearchRequestInterface $searchRequest,
        ?Project $project,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('{alias}');

        if ($project !== null) {
            $qb->andWhere('{alias}.project = :project');
            $qb->setParameter('project', $project);
        }

        $search = $searchRequest->getSearch();
        if ($search) {
            $qb->andWhere('{alias}.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }

        return $qb;
    }
}
```

**Conventions:**

- Namespace `Sovic\Cms\Repository`; extends `EntityRepository` (never `ServiceEntityRepository`).
- Include the `@method` PHPDoc block for IDE typing (every repo in this package has it).
- Each `$qb->` call on its own line — **no method chaining**.
- `findBySearchRequest()` + `countBySearchRequest()` share a private `buildQuery()` that returns a `QueryBuilder` and
  never executes. Catch both `NoResultException | NonUniqueResultException` in the count.
- Add `?Project $project = null` and a `{alias}.project = :project` filter for project-scoped resources.
- Alias is the entity initial (`t`, `p`, …). If editing an existing repo, match the alias it already uses.
- **`SearchRequestInterface` API** (`Sovic\Common\DataList`): `getSearch()`, `getOffset()`, `getLimit()`, `getPage()`,
  `getPagination(int $total): Pagination`, `toArray()`. There is **no** `setTotal()` / `getQueryParams()`.

---

## 3. Form — `src/Form/Admin/{Name}.php`

The form class is named `{Name}` (not `{Name}Type`) and lives in `Sovic\Cms\Form\Admin`.

```php
<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class {Name} extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Název',
                'required' => true,
                'attr' => [
                    'maxlength' => 255,
                ],
            ]
        );

        // Add more fields here, each via its own $builder->add(

        $builder->add('save', SubmitType::class, [
            'label' => 'Uložit změny',
            'attr' => [
                'class' => FormTheme::BtnSubmitClass,
            ],
        ]);
    }
}
```

**Conventions:**

- Import `Sovic\Cms\Form\FormTheme` (note: `Sovic\Cms\Form\FormTheme`, **not** `…\Form\Theme\FormTheme`).
- `$builder->setMethod('POST');` first.
- Each field added with its own `$builder->add(` call — never chain. `'attr'` items each on their own line.
- Labels in Czech.
- Submit button always last, label `'Uložit změny'`, class `FormTheme::BtnSubmitClass`.
- **Usually omit `configureOptions`/`data_class`** — the controller passes the entity to `createForm`, so it is inferred
  (see `PageGroupType`). Only add `data_class` if you have a concrete reason.
- For a boolean editable in the form, add a `CheckboxType` (`'required' => false`). Note: booleans are commonly toggled
  from the **list** via the toggle switch instead (see §7), so they're often not in the form at all.

---

## 4. Controller — `src/Controller/Admin/{Name}Controller.php`

```php
<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\{Name};
use Sovic\Cms\Repository\{Name}Repository;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class {Name}Controller extends AdminBaseController
{
    #[Route(
        '/admin/{name}/list',
        name: 'admin:{name}:list',
    )]
    public function list(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        Request                   $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $searchRequest = $factory->createFromRequest($request);
        $searchRequest->setPaginationRoute('admin:{name}:list');

        /** @var {Name}Repository $repo */
        $repo = $em->getRepository({Name}::class);

        $project = $this->project->getEntity();
        $items = $repo->findBySearchRequest($searchRequest, $project);
        $total = $repo->countBySearchRequest($searchRequest, $project);

        $this->assign('items', $items);
        $this->assign('pagination', $searchRequest->getPagination($total));
        $this->assign('query', $searchRequest->toArray());

        return $this->render('@CmsBundle/admin/{name_snake}/list.html.twig');
    }

    #[Route(
        '/admin/{name}/edit/{id}',
        name: 'admin:{name}:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function edit(
        ?int                   $id,
        EntityManagerInterface $em,
        Request                $request,
        TranslatorInterface    $t,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repo = $em->getRepository({Name}::class);
        $entity = $repo->find($id);

        if ($entity === null) {
            $entity = new {Name}();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\{Name}::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$em->contains($entity)) {
                    $entity->setProject($this->project->getEntity());
                }

                $em->persist($entity);
                $em->flush();

                try {
                    $this->addFlash('success', $t->trans('flash.saved', domain: '{name}'));
                } catch (Throwable) {
                }

                return $this->redirectToRoute('admin:{name}:edit', ['id' => $entity->getId()]);
            }

            try {
                $this->addFlash('error', $t->trans('flash.form_error', domain: '{name}'));
            } catch (Throwable) {
            }
        }

        $editing = $id > 0;

        $this->assign('editing', $editing);
        $this->assign('entity', $entity);
        $this->assign('form', $form->createView());

        return $this->render('@CmsBundle/admin/{name_snake}/edit.html.twig');
    }
}
```

**Conventions:**

- Namespace `Sovic\Cms\Controller\Admin`; extends `AdminBaseController`; **no `declare(strict_types=1)`**.
- **No `#[IsGranted]` class attribute** — call `$this->denyAccessUnlessGranted('ROLE_ADMIN');` as the first line of every
  action (the package convention).
- Singular route paths/names: `/admin/{name}/list`, `admin:{name}:list`. Multi-line `#[Route(...)]`, `edit` uses
  `requirements: ['id' => '\d+']` and `defaults: ['id' => 0]`.
- Action parameters: one per line, names visually aligned (see existing controllers).
- Assign `pagination` via `$searchRequest->getPagination($total)` and `query` via `$searchRequest->toArray()`.
- Pass the view with `$form->createView()` (assigned as `form`).
- Project-scoped resources: filter the list by `$this->project->getEntity()` and set it on new entities via
  `if (!$em->contains($entity)) { $entity->setProject($this->project->getEntity()); }`. `$this->project` is a
  `Sovic\Common\Project\Project`; `->getEntity()` returns the `Sovic\Common\Entity\Project`. *(If the user explicitly
  wants tags/records shared across projects, pass `null` instead and skip the setProject.)*
- Flash messages via the translator, wrapped in `try { … } catch (Throwable) {}` (see `PostController`).
- Sort `$this->assign()` calls alphabetically by their snake_case key.
- Render with the `@CmsBundle/admin/...` namespace.
- For galleries/rich models, inject `#[Autowire('%base_gallery_url%')] string $galleryBaseUrl` and use
  `GalleryControllerTrait` + the entity's `*Factory` (see `PostController`, `PageController`).

---

## 5. API controller (delete / toggle) — `src/Controller/Admin/Api/Web/{Name}Controller.php`

Row actions in the list (delete, boolean toggle) are **AJAX calls to `/admin/api/web/...`**, not form posts. If the
resource already has an `Api/Web/{Name}Controller` (e.g. Tag has `suggestions`), **add methods to it** rather than
creating a duplicate.

```php
<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\{Name};
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class {Name}Controller extends AbstractBaseApiController
{
    use JsonResponseTrait;

    private const ToggleableFields = [
        'is_public',
    ];

    #[Route(
        '/admin/api/web/{name}/{id}/delete',
        name: 'admin:api:web:{name}:delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function delete(
        int                    $id,
        EntityManagerInterface $em,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entity = $em->getRepository({Name}::class)->find($id);
        if ($entity === null) {
            return $this->sendFail(404);
        }

        $em->remove($entity);
        $em->flush();

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/{name}/{id}/toggle-state',
        name: 'admin:api:web:{name}:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entity = $em->getRepository({Name}::class)->find($id);
        if ($entity === null) {
            return $this->sendFail(404);
        }

        $data = $this->getRequestData($request);
        $field = $data['field'] ?? null;
        if (!in_array($field, self::ToggleableFields, true)) {
            $this->addError('invalid_field');

            return $this->sendFail();
        }

        $state = $data['state'] ?? null;

        if ($field === 'is_public') {
            $entity->setIsPublic((bool) $state);
            $em->flush();

            $this->data['value'] = $entity->isIsPublic();  // match the entity's real getter

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }
}
```

**Conventions:**

- Extends `AbstractBaseApiController` (which already mixes in `JsonRequestTrait` + `JsonResponseTrait`; re-`use`ing
  `JsonResponseTrait` here is harmless and matches `MenuItemController`).
- Routes `/admin/api/web/{name}/{id}/{action}`, names `admin:api:web:{name}:{action}`, `methods: ['POST']`.
- Read the JSON body with `$this->getRequestData($request)`; respond with `$this->sendSuccess()` /
  `$this->sendFail(404)`; report validation problems with `$this->addError('…')`.
- `toggle-state` validates `field` against a `ToggleableFields` whitelist, flips the boolean, and returns the new value
  in `$this->data['value']` (the JS uses it to sync the checkbox). Use the entity's **actual** getter — older entities
  expose `isIsPublic()`.

---

## 6. Template: list — `templates/admin/{name_snake}/list.html.twig`

Extends the `data-list` layout (which renders the search box, pagination, and includes `data-list-js`). Import
`form` too if you use the toggle switch.

```twig
{% extends '@CmsBundle/admin/layout/data-list.html.twig' %}

{% import '@CmsBundle/admin/macros/data-list.html.twig' as data_list %}
{% import '@CmsBundle/admin/macros/form.html.twig' as form_macro %}
{% import '@CmsBundle/admin/macros/theme.html.twig' as theme %}

{% block title %}Přehled štítků{% endblock %}

{% set allow_add = true %}
{% set is_search_enabled = true %}
{% set is_filters_enabled = false %}
{% set is_group_actions_enabled = false %}

{% block add_item %}
    {% if allow_add %}
        <a class="btn btn-flex btn-light-primary" href="{{ path('admin:{name}:edit') }}">
            <i class="ki-outline ki-plus fs-3"></i>
            Nový štítek
        </a>
    {% endif %}
{% endblock %}

{% block data_list_table %}
    <table class="{{ theme.table_class }} dataTable">
        <colgroup>
            <col data-dt-column="0" style="">
            <col data-dt-column="1" style="width: 15%;">
            <col data-dt-column="2" style="width: 10%;">
        </colgroup>
        <thead>
        <tr class="{{ theme.thead_tr_class }}" role="row">
            <th class="min-w-100px" data-dt-column="0"><span class="dt-column-title">Název</span></th>
            <th class="min-w-100px" data-dt-column="1"><span class="dt-column-title">Publikováno</span></th>
            <th class="text-end min-w-70px" data-dt-column="2" aria-label="Akce"><span class="dt-column-title">Akce</span></th>
        </tr>
        </thead>
        <tbody class="{{ theme.tbody_class }}">
        {% for item in items %}
            {% set detail_url = path('admin:{name}:edit', {'id': item.id}) %}
            <tr>
                <td class="align-middle">
                    <a href="{{ detail_url }}" class="text-gray-800 text-hover-primary fs-5 fw-bold">{{ item.name }}</a>
                </td>
                <td class="align-middle">
                    <div class="{{ theme.toggle_switch_class }}"
                         data-entity="{name}"
                         data-entity-id="{{ item.id }}">
                        {{ form_macro.switch('{name}_is_public_' ~ item.id, 'is_public', 'Publikováno', item.isPublic, 'bi bi-check-circle-fill') }}
                    </div>
                </td>
                <td class="text-end">
                    {% set actions = [
                        {
                            'url': detail_url,
                            'title': 'Upravit',
                            'class': 'text-primary',
                            'icon': 'ki-duotone ki-pencil',
                        },
                        {
                            'url': '#',
                            'title': 'Smazat',
                            'method': '{name}/' ~ item.id ~ '/delete',
                            'confirm': 'Opravdu chcete smazat tento záznam?',
                            'btn_class': 'btn-active-light-danger action-button',
                            'icon': 'ki-duotone ki-trash',
                        }
                    ] %}
                    {{ data_list.actions_icons(actions) }}
                </td>
            </tr>
        {% else %}
            {{ data_list.no_items_row(3, 'Žádné záznamy') }}
        {% endfor %}
        </tbody>
        <tfoot></tfoot>
    </table>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="{{ asset('bundles/cms/js/toggle-switch.js') }}"></script>
{% endblock %}
```

**Conventions & gotchas:**

- `theme.*` macros are referenced **without parentheses** in this package: `{{ theme.table_class }}`,
  `{{ theme.thead_tr_class }}`, `{{ theme.tbody_class }}`, `{{ theme.toggle_switch_class }}`.
- `{% set is_search_enabled = true %}` puts a live search box in the card header (posts `search` query param — the
  controller reads it via the `SearchRequest`). `is_group_actions_enabled = true` adds bulk checkboxes
  (`data_list.checkbox_all()` in `<thead>`, `data_list.checkbox(item.id)` per row).
- Empty state: `{% else %}{{ data_list.no_items_row(<colCount>, 'Žádné …') }}`.
- DATE columns: `text-end` on both `<th>` and `<td>`; format with `format_date` / `format_date_time`.
- **Delete / row actions** go through `data_list.actions_icons(actions)`. Each action is
  `{ url, title, icon, class?, method?, confirm?, btn_class? }`. The JS (`data-list-js`) posts to
  `'{{ api_base_url }}/api/web/' ~ method`, so `method` must be `'{name}/' ~ item.id ~ '/delete'` and a matching API
  route must exist (§5).
- **Critical gotcha:** `actions_icons` does **not** add the `action-button` class that the click handler binds to. To
  make a `method` action actually fire, append `action-button` to its **`btn_class`**, e.g.
  `'btn_class': 'btn-active-light-danger action-button'` (pattern from `menu/list-position.html.twig`). A `confirm`
  string triggers a `window.confirm()` first.
- **Boolean toggle switch** (instead of a static badge): wrap `form_macro.switch(id, field, title, checked, icon_class)`
  in `<div class="{{ theme.toggle_switch_class }}" data-entity="{name}" data-entity-id="{{ item.id }}">`. The
  `toggle-switch.js` script (include it via the `javascripts` block) posts `{field, state}` to
  `/admin/api/web/{name}/{id}/toggle-state`, so the API `toggleState` action (§5) is required. `field` must match a
  `ToggleableFields` entry (e.g. `is_public`).
- Action icons use KeenThemes duotone classes, e.g. `ki-duotone ki-pencil`, `ki-duotone ki-trash`.

---

## 7. Template: edit — `templates/admin/{name_snake}/edit.html.twig`

```twig
{% extends '@CmsBundle/admin/layout/admin.html.twig' %}
{% form_theme form '@CmsBundle/admin/forms/form-narrow.html.twig' %}
{% import '@CmsBundle/admin/macros/form.html.twig' as form_macro %}
{% import '@CmsBundle/admin/macros/icons.html.twig' as icons %}
{% import '@CmsBundle/admin/macros/theme.html.twig' as theme %}

{% block title %}
    {{ editing ? 'Editace záznamu' : 'Nový záznam' }}
{% endblock %}

{% block content %}
    <div class="app-container container">
        <div class="{{ theme.card_class }}">
            {{ form_macro.card_header(icons.back_to_list(path('admin:{name}:list')) ~ block('title')) }}

            <div class="card-body">
                {{ block('edit_form') }}
            </div>
        </div>
    </div>
{% endblock %}

{% block edit_form %}
    {{ form_start(form) }}
    {{ form_errors(form) }}

    {{ form_macro.section_header('Základní informace') }}
    <div class="row">
        <div class="col-12 col-md-6">
            {{ form_row(form.name) }}
        </div>
    </div>

    {{ form_macro.sticky_submit(form) }}

    {% if form._token is defined %}
        {{ form_row(form._token) }}
    {% endif %}

    {{ form_end(form, {render_rest: false}) }}
{% endblock %}
```

**Conventions:**

- Extends `@CmsBundle/admin/layout/admin.html.twig`; form theme `@CmsBundle/admin/forms/form-narrow.html.twig`.
- Import `form` (`form_macro`), `icons`, and `theme` macros. `theme.card_class` is used without parentheses.
- Card header signature is `card_header(title, toolbar)` — pass the title with `back_to_list` concatenated:
  `form_macro.card_header(icons.back_to_list(path('admin:{name}:list')) ~ block('title'))`. **Do not** pass `theme` as
  the first argument. Never use a toolbar button for back-navigation.
- Group fields with `form_macro.section_header('…')` + a Bootstrap `<div class="row">`.
- Submit bar via `{{ form_macro.sticky_submit(form) }}` (a macro — do **not** hand-roll a sticky div), then render the
  CSRF token, then `form_end(form, {render_rest: false})`.
- Gallery: add tab nav/panes with the `gallery` macro and `{% include '@CmsBundle/admin/partials/gallery-scripts.html.twig' %}`.
- Rich text: import the `content-editor` macro and call e.g. `{{ content_editor.content_editor_basic('#entity_content', 'cs') }}`.

---

## 8. Routing & registration

**Nothing to register in this package.** Attribute routes on controllers under `src/Controller/` are auto-loaded by the
downstream app (it imports `@CmsBundle/Controller/` as `type: attribute`). New controllers/actions are picked up
automatically. There is no route YAML or `config/packages/doctrine.yaml` to edit here.

If the user wants a **sidebar link**, add it to `templates/admin/partials/sidebar.html.twig` pointing at
`path('admin:{name}:list')` — only when asked.

---

## 9. Checklist

After generating, verify:

- [ ] Checked for existing entity/repository/form/controller/templates first; only created what was missing.
- [ ] Namespaces are `Sovic\Cms\…`; **no** `declare(strict_types=1)`; **no** `App\` or `config/` references.
- [ ] Entity (if new) uses explicit Doctrine attributes + traits; project-scoped via `ManyToOne` to `Project` when relevant.
- [ ] Repository extends `EntityRepository`, has the `@method` block, `findBySearchRequest`/`countBySearchRequest` +
      private `buildQuery`, no `$qb` chaining, optional `?Project` filter.
- [ ] Form class is `Sovic\Cms\Form\Admin\{Name}`, imports `Sovic\Cms\Form\FormTheme`, `setMethod('POST')`, fields each
      on their own `$builder->add(`.
- [ ] Controller uses `$this->denyAccessUnlessGranted('ROLE_ADMIN')` per action (no `#[IsGranted]`), singular route
      names, `getPagination($total)` / `toArray()`, `$form->createView()`, alphabetically-sorted `assign()`.
- [ ] If delete/toggle wanted: API controller under `Admin/Api/Web/` with POST `delete` / `toggle-state`; list uses
      `method` actions with `action-button` in `btn_class`, and the toggle switch + `toggle-switch.js`.
- [ ] List template: singular route paths, `theme.*` macros without parens, `no_items_row` empty state, DATE columns
      `text-end`.
- [ ] Edit template: `form-narrow` theme, `card_header(back_to_list ~ title)`, `form_macro.sticky_submit(form)`.
- [ ] No migration generated (user does it manually). Sidebar link only if requested.
- [ ] `php -l` clean on every generated PHP file.
