---
name: new-admin-module
description: Scaffold a complete new admin module for the Sovic CMS (Symfony 7.2). Use whenever the user asks to create a new admin section, resource, CRUD module, or admin page. Generates Entity, Repository, Form, Controller, and Twig templates following established project conventions (page, post, menu modules as reference).
argument-hint: <ModuleName> [table_name]
---

# New Admin Module

Scaffold a complete admin CRUD module. The argument is the **singular PascalCase name** of the resource (e.g. `Invoice`,
`Product`, `Article`). The optional second argument is the snake_case table name if it differs from the default.

Derive all names from the argument:

- `{Name}` — PascalCase class name (e.g. `Invoice`)
- `{name}` — camelCase (e.g. `invoice`)
- `{name_snake}` — snake_case (e.g. `invoice`, or `invoice_item`)
- `{names}` — plural snake_case for route prefix (e.g. `invoices`)

Ask the user for the module name if not provided. Confirm derived names before generating files.

---

## Files to Create

```
src/Entity/{Name}.php
src/Repository/{Name}Repository.php
src/Form/Admin/{Name}.php
src/Controller/Admin/{Name}Controller.php
templates/admin/{name_snake}/list.html.twig
templates/admin/{name_snake}/edit.html.twig
```

Do **not** generate migrations — only create/modify the entity. The user runs migrations manually.

After creating all files, add the entity mapping to `config/packages/doctrine.yaml` (see Doctrine section below).

---

## 1. Entity — `src/Entity/{Name}.php`

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;
use Sovic\Common\Entity\Trait\CreatedAtTrait;
use Sovic\Common\Entity\Trait\UpdatedAtTrait;

#[ORM\Entity(repositoryClass: \App\Repository\{Name}Repository::class)]
#[ORM\Table(name: '{name_snake}')]
class {Name}
{
    use IdentityColumnTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    // Add domain columns here
}
```

**Conventions:**

- Always use `IdentityColumnTrait` (provides `$id`, `getId()`, `setId()`)
- Add `CreatedAtTrait` and `UpdatedAtTrait` for timestamp columns
- Add `IsPublicTrait` (provides `$isPublic`, `isPublic()`, `setIsPublic()`) for publishable resources
- Add `MetaColumnsTrait` for SEO fields (`metaTitle`, `metaDescription`, `metaKeywords`)
- Boolean columns: `is_` prefix (e.g. `is_featured`, `is_public`)
- DateTime columns: `At` suffix (e.g. `publishedAt`, `deletedAt`)
- All columns nullable unless there's a strong reason otherwise
- String lengths: 255 for names/labels, 1024 for long strings, TEXT for content
- Add `#[ORM\Index]` attributes on the `#[ORM\Table]` line for commonly filtered columns
- Use `bcmath` for financial/precise numeric calculations — never float

**Common trait imports:**

```php
use Sovic\Common\Entity\Trait\IdentityColumnTrait;   // id column
use Sovic\Common\Entity\Trait\CreatedAtTrait;         // createdAt
use Sovic\Common\Entity\Trait\UpdatedAtTrait;         // updatedAt
use App\Entity\Trait\IsPublicTrait;                   // isPublic bool
use App\Entity\Trait\MetaColumnsTrait;                // meta SEO fields
```

---

## 2. Repository — `src/Repository/{Name}Repository.php`

```php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\{Name};
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sovic\Common\DataList\SearchRequestInterface;

class {Name}Repository extends EntityRepository
{
    public function findBySearchRequest(SearchRequestInterface $searchRequest): array
    {
        $qb = $this->buildQuery($searchRequest);
        $qb->orderBy('e.id', 'DESC');
        $qb->setFirstResult($searchRequest->getOffset());
        $qb->setMaxResults($searchRequest->getLimit());

        return $qb->getQuery()->getResult();
    }

    public function countBySearchRequest(SearchRequestInterface $searchRequest): int
    {
        $qb = $this->buildQuery($searchRequest);
        $qb->select('COUNT(e.id)');

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException) {
            return 0;
        }
    }

    private function buildQuery(SearchRequestInterface $searchRequest): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');

        if ($searchRequest->getSearch()) {
            $qb->andWhere('e.name LIKE :search');
            $qb->setParameter('search', '%' . $searchRequest->getSearch() . '%');
        }

        // Add more filters based on SearchRequest properties

        return $qb;
    }
}
```

**Conventions:**

- Extends `EntityRepository` (never `ServiceEntityRepository`)
- Each `$qb->` call on its own line — no method chaining
- `findBySearchRequest()` + `countBySearchRequest()` share `buildQuery()` (private)
- `buildQuery()` returns `QueryBuilder`, never executes
- Catch both `NoResultException` and `NonUniqueResultException` in count methods
- Alias is always `e` for the main entity

---

## 3. Form — `src/Form/Admin/{Name}.php`

```php
<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Sovic\Cms\Form\Theme\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class {Name} extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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

        // Add more fields here

        $builder->add(
            'isPublic',
            CheckboxType::class,
            [
                'label' => 'Publikováno',
                'required' => false,
            ]
        );

        $builder->add(
            'save',
            SubmitType::class,
            [
                'label' => 'Uložit změny',
                'attr' => [
                    'class' => FormTheme::BtnSubmitClass,
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\{Name}::class,
        ]);
    }
}
```

**Conventions:**

- Each `$builder->add(` on a separate call — never chain
- `'attr'` items each on their own line
- Labels in Czech
- Submit button always last, always uses `FormTheme::BtnSubmitClass`
- If the entity has `IsPublicTrait`, add a `CheckboxType` for `isPublic`
- If the entity has `MetaColumnsTrait`, add `MetaFormTrait` and call `$this->addMetaFields($builder)` at the end

---

## 4. Controller — `src/Controller/Admin/{Name}Controller.php`

```php
<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\{Name};
use App\Form\Admin\{Name} as {Name}Form;
use App\Repository\{Name}Repository;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class {Name}Controller extends AdminBaseController
{
    #[Route('/admin/{names}/list', name: 'admin:{names}:list')]
    public function list(
        EntityManagerInterface $em,
        BasicSearchRequestFactory $factory,
        Request $request,
    ): Response {
        $searchRequest = $factory->createFromRequest($request);
        $searchRequest->setPaginationRoute('admin:{names}:list');

        /** @var {Name}Repository $repository */
        $repository = $em->getRepository({Name}::class);

        $items = $repository->findBySearchRequest($searchRequest);
        $total = $repository->countBySearchRequest($searchRequest);
        $searchRequest->setTotal($total);

        $this->assign('items', $items);
        $this->assign('pagination', $searchRequest->getPagination());
        $this->assign('query', $searchRequest->getQueryParams());

        return $this->render('admin/{name_snake}/list.html.twig');
    }

    #[Route('/admin/{names}/edit/{id}', name: 'admin:{names}:edit', defaults: ['id' => 0])]
    public function edit(
        ?int $id,
        EntityManagerInterface $em,
        Request $request,
    ): Response {
        $entity = $id ? $em->getRepository({Name}::class)->find($id) : null;
        $editing = $entity !== null;

        if (!$entity) {
            $entity = new {Name}();
        }

        $form = $this->createForm({Name}Form::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', 'Záznam byl uložen.');

            return $this->redirectToRoute('admin:{names}:edit', ['id' => $entity->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $this->assign('editing', $editing);
        $this->assign('entity', $entity);
        $this->assign('form', $form);

        return $this->render('admin/{name_snake}/edit.html.twig');
    }
}
```

**Conventions:**

- Extends `AdminBaseController` from `sovic/cms`
- `#[IsGranted('ROLE_ADMIN')]` on the class
- Routes: `admin:{names}:{action}` pattern (plural for the resource segment)
- Edit route default `id: 0`, parameter `?int $id`
- `$this->assign()` calls sorted alphabetically by key (snake_case keys)
- No factory/model wrapper needed unless the entity has a gallery or complex model logic
- For gallery support, inject `#[Autowire('%gallery_base_url%')] string $galleryBaseUrl` in constructor and use
  `GalleryControllerTrait`

---

## 5. Template: list — `templates/admin/{name_snake}/list.html.twig`

```twig
{% extends '@CmsBundle/admin/layout/data-list.html.twig' %}

{% import '@CmsBundle/admin/macros/data-list.html.twig' as data_list %}
{% import '@CmsBundle/admin/macros/theme.html.twig' as theme %}

{% set allow_add = true %}
{% set is_search_enabled = true %}
{% set is_filters_enabled = false %}
{% set is_group_actions_enabled = false %}

{% block data_list_title %}<h2>{Names}</h2>{% endblock %}

{% block add_item %}
    <a href="{{ path('admin:{names}:edit') }}" class="btn btn-sm btn-primary">
        Nový záznam
    </a>
{% endblock %}

{% block data_list_table %}
    <table class="{{ theme.default.table_class() }}">
        <thead>
            <tr class="{{ theme.default.thead_tr_class() }}">
                <th>Název</th>
                <th>Publikováno</th>
                <th class="text-end">Akce</th>
            </tr>
        </thead>
        <tbody class="{{ theme.default.tbody_class() }}">
            {% for item in items %}
                <tr>
                    <td>{{ item.name }}</td>
                    <td>
                        {% if item.isPublic %}
                            <span class="badge badge-light-success">Ano</span>
                        {% else %}
                            <span class="badge badge-light-danger">Ne</span>
                        {% endif %}
                    </td>
                    <td class="text-end">
                        {{ data_list.actions_icons([
                            { url: path('admin:{names}:edit', { id: item.id }), icon: 'edit', title: 'Upravit' },
                        ]) }}
                    </td>
                </tr>
            {% else %}
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">Žádné záznamy</td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
```

**Conventions:**

- `data_list.actions_icons()` takes an array of `{ url, icon, title }` objects
- DATE columns: `text-end` on both `<th>` and `<td>`
- Use `format_date` / `format_date_time` Twig filters for dates
- `is_group_actions_enabled = true` enables checkboxes; add `data_list.checkbox_all()` in `<thead>` and
  `data_list.checkbox(item.id)` in each row
- Pagination is rendered automatically by the `data-list` layout when `pagination` variable is set

---

## 6. Template: edit — `templates/admin/{name_snake}/edit.html.twig`

```twig
{% extends '@CmsBundle/admin/layout/admin.html.twig' %}

{% form_theme form '@CmsBundle/admin/forms/form-narrow.html.twig' %}

{% import '@CmsBundle/admin/macros/form.html.twig' as form_macro %}
{% import '@CmsBundle/admin/macros/icons.html.twig' as icons %}
{% import '@CmsBundle/admin/macros/theme.html.twig' as theme %}

{% block title %}{{ editing ? 'Upravit záznam' : 'Nový záznam' }}{% endblock %}

{% block content %}
    <div class="d-flex align-items-center mb-5 gap-3">
        {{ icons.back_to_list(path('admin:{names}:list')) }}
        <h1 class="mb-0">{{ editing ? 'Upravit záznam' : 'Nový záznam' }}</h1>
    </div>

    {{ form_start(form) }}
    {{ form_errors(form) }}

    <div class="{{ theme.default.card_class() }}">
        <div class="{{ theme.default.card_header_class() }}">
            {{ form_macro.card_header(theme, 'Základní informace') }}
        </div>
        <div class="card-body py-5">
            {{ form_row(form.name) }}
            {{ form_row(form.isPublic) }}
        </div>
    </div>

    {# Add more card sections for additional field groups #}

    {# Sticky submit bar #}
    <div class="fixed-bottom bg-white border-top py-3 px-5 d-flex justify-content-end">
        {{ form_row(form._token) }}
        {{ form_widget(form.save) }}
    </div>

    {{ form_end(form, { render_rest: false }) }}
{% endblock %}
```

**Conventions:**

- Always use `@CmsBundle/admin/forms/form-narrow.html.twig` as form theme
- Group fields into cards with `form_macro.card_header(theme, 'Section Title')`
- Sticky bottom bar for the submit button
- If module has gallery, add tabs and include `gallery_macro.gallery_tabs_nav(galleries)` /
  `gallery_macro.gallery_tabs_panes(model, entity.id, galleries)` and
  `{% include '@CmsBundle/admin/partials/gallery-scripts.html.twig' %}`
- If module has rich text content, import `content_editor` macro and call
  `{{ content_editor.content_editor_basic('#entity_content') }}`
- Back-to-list link always at top using `icons.back_to_list()`

---

## 7. Doctrine Registration

Add the entity to `config/packages/doctrine.yaml` under `mappings`. Auto-mapping is disabled, so every entity must be
explicitly listed:

```yaml
doctrine:
  orm:
    mappings:
      App{Name}:
        type: attribute
        is_bundle: false
        dir: '%kernel.project_dir%/src/Entity'
        prefix: 'App\Entity'
        alias: App{Name}
```

Or if it's grouped with existing App entities, confirm whether there's already a catch-all App mapping. Check the
current `config/packages/doctrine.yaml` first before adding.

---

## 8. Checklist

After generating all files, verify:

- [ ] Entity uses `IdentityColumnTrait` and correct traits
- [ ] Repository extends `EntityRepository`, no method chaining on `$qb`
- [ ] Form fields each on separate `$builder->add(` calls, `'attr'` items each on new line
- [ ] Controller `$this->assign()` calls sorted alphabetically by snake_case key
- [ ] Route names follow `admin:{names}:{action}` pattern
- [ ] List template: DATE columns have `text-end` on `<th>` and `<td>`
- [ ] Edit template: uses `form-narrow.html.twig` theme, has sticky submit bar
- [ ] Entity mapping added to `config/packages/doctrine.yaml`
- [ ] No migration generated (user does this manually)
