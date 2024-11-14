<?php

namespace JobMetric\Taxonomy\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JobMetric\Taxonomy\Facades\Taxonomy;
use JobMetric\Taxonomy\Http\Requests\SetTranslationRequest;
use JobMetric\Taxonomy\Http\Requests\StoreTaxonomyRequest;
use JobMetric\Taxonomy\Http\Requests\UpdateTaxonomyRequest;
use JobMetric\Taxonomy\Http\Resources\TaxonomyResource;
use JobMetric\Taxonomy\Models\Taxonomy as TaxonomyModel;
use JobMetric\Language\Facades\Language;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Http\Controllers\Controller;
use JobMetric\Panelio\Http\Requests\ActionListRequest;
use JobMetric\Panelio\Http\Requests\ExportActionListRequest;
use JobMetric\Panelio\Http\Requests\ImportActionListRequest;
use Throwable;

class TaxonomyController extends Controller
{
    private array $route;

    public function __construct()
    {
        if (request()->route()) {
            $parameters = request()->route()->parameters();

            $this->route = [
                'index' => route('taxonomy.{type}.index', $parameters),
                'create' => route('taxonomy.{type}.create', $parameters),
                'store' => route('taxonomy.{type}.store', $parameters),
                'options' => route('taxonomy.options', $parameters),
                'import' => route('taxonomy.import', $parameters),
                'export' => route('taxonomy.export', $parameters),
                'set_translation' => route('taxonomy.set-translation', $parameters),
            ];
        }
    }

    /**
     * Display a listing of the taxonomy.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View|JsonResponse
     * @throws Throwable
     */
    public function index(string $panel, string $section, string $type): View|JsonResponse
    {
        if (request()->ajax()) {
            $query = Taxonomy::query($type, with: ['translations', 'files', 'metas', 'taxonomyRelations']);

            return Datatable::of($query, resource_class: TaxonomyResource::class);
        }

        $configuration = getTaxonomyTypeArg($type, 'configuration');

        // Set data taxonomy
        $data['name'] = getTaxonomyTypeArg($type);

        // Show description
        // if show_description exist and value = true -> show description
        if (isset($configuration['list']['show_description']) && $configuration['list']['show_description']) {
            $data['description'] = getTaxonomyTypeArg($type, 'description');
        } else {
            $data['description'] = null;
        }

        // Set filter
        // if filter = false -> not show filter
        $data['show_filter'] = true;
        if (isset($configuration['list']['filter']) && !$configuration['list']['filter']) {
            $data['show_filter'] = false;
        }

        DomiTitle(getTaxonomyTypeArg($type));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name']);

        // add button
        Button::add($this->route['create']);
        Button::delete();

        // Check show button change status
        // if change_status = false -> not show button
        if (isset($configuration['list']['change_status'])) {
            if ($configuration['list']['change_status']) {
                Button::status();
            }
        } else {
            Button::status();
        }

        // Check show button import
        // if import = false or not exist -> not show button
        if (isset($configuration['list']['import']) && $configuration['list']['import']) {
            Button::import();
        }

        // Check show button export
        // if export = false or not exist -> not show button
        if (isset($configuration['list']['export']) && $configuration['list']['export']) {
            Button::export();
        }

        $data['metadata'] = getTaxonomyTypeArg($type, 'metadata');

        DomiLocalize('taxonomy', [
            'route' => $this->route['index'],
            'metadata' => collect($data['metadata'])->select('label', 'info')->map(function ($item) {
                return [
                    'label' => trans($item['label']),
                    'info' => trans($item['info']),
                ];
            }),
        ]);

        DomiPlugins('jquery.form');

        DomiAddModal('translation', '', view('translation::modals.translation-list', [
            'action' => $this->route['set_translation'],
            'items' => getTaxonomyTypeArg($type, 'translation')
        ]), options: [
            'size' => 'lg'
        ]);

        DomiScript('assets/vendor/taxonomy/js/list.js');

        $data['type'] = $type;

        $data['route'] = $this->route['options'];
        $data['import_action'] = $this->route['import'];
        $data['export_action'] = $this->route['export'];

        return view('taxonomy::list', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View
     */
    public function create(string $panel, string $section, string $type): View
    {
        $data['mode'] = 'create';

        // Set data taxonomy
        $data['name'] = getTaxonomyTypeArg($type);

        DomiTitle(trans('taxonomy::base.form.create.title', [
            'type' => $data['name']
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name'], $this->route['index']);
        Breadcrumb::add(trans('taxonomy::base.form.create.title', [
            'type' => $data['name']
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/taxonomy/js/form.js');

        $data['type'] = $type;
        $data['action'] = $this->route['store'];

        $data['hierarchical'] = getTaxonomyTypeArg($type, 'hierarchical');
        $data['translation'] = getTaxonomyTypeArg($type, 'translation');
        $data['metadata'] = getTaxonomyTypeArg($type, 'metadata');
        $data['has_url'] = getTaxonomyTypeArg($type, 'has_url');
        $data['has_base_media'] = getTaxonomyTypeArg($type, 'has_base_media');
        $data['media'] = getTaxonomyTypeArg($type, 'media');

        $data['taxonomies'] = Taxonomy::all($type);

        return view('taxonomy::form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTaxonomyRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreTaxonomyRequest $request, string $panel, string $section, string $type): RedirectResponse
    {
        $form_data = $request->all();

        $taxonomy = Taxonomy::store($request->validated());

        if ($taxonomy['ok']) {
            $this->alert($taxonomy['message']);

            if ($form_data['save'] == 'save.new') {
                return back();
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('taxonomy.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_taxonomy' => $taxonomy['data']->id
            ]);
        }

        $this->alert($taxonomy['message'], 'danger');

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param TaxonomyModel $taxonomy
     *
     * @return View
     */
    public function edit(string $panel, string $section, string $type, TaxonomyModel $taxonomy): View
    {
        $taxonomy->load(['files', 'metas', 'translations']);

        $data['mode'] = 'edit';

        // Set data taxonomy
        $data['name'] = getTaxonomyTypeArg($type);

        DomiTitle(trans('taxonomy::base.form.edit.title', [
            'type' => $data['name'],
            'name' => $taxonomy->id
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name'], $this->route['index']);
        Breadcrumb::add(trans('taxonomy::base.form.edit.title', [
            'type' => $data['name'],
            'name' => $taxonomy->id
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/taxonomy/js/form.js');

        $data['type'] = $type;
        $data['action'] = route('taxonomy.{type}.update', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type,
            'jm_taxonomy' => $taxonomy->id
        ]);

        $data['hierarchical'] = getTaxonomyTypeArg($type, 'hierarchical');
        $data['translation'] = getTaxonomyTypeArg($type, 'translation');
        $data['metadata'] = getTaxonomyTypeArg($type, 'metadata');
        $data['has_url'] = getTaxonomyTypeArg($type, 'has_url');
        $data['has_base_media'] = getTaxonomyTypeArg($type, 'has_base_media');
        $data['media'] = getTaxonomyTypeArg($type, 'media');

        $data['taxonomies'] = Taxonomy::all($type);

        $data['slug'] = $taxonomy->urlByCollection($type, true);
        $data['taxonomy'] = $taxonomy;

        $data['translation_edit_values'] = translationResourceData($taxonomy->translations);
        $data['media_values'] = $taxonomy->getMediaDataForObject();
        $data['meta_values'] = $taxonomy->getMetaDataForObject();

        $data['languages'] = Language::all();


        return view('taxonomy::form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTaxonomyRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param TaxonomyModel $taxonomy
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateTaxonomyRequest $request, string $panel, string $section, string $type, TaxonomyModel $taxonomy): RedirectResponse
    {
        $form_data = $request->all();

        $taxonomy = Taxonomy::update($taxonomy->id, $request->validated());

        if ($taxonomy['ok']) {
            $this->alert($taxonomy['message']);

            if ($form_data['save'] == 'save.new') {
                return redirect()->to($this->route['create']);
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('taxonomy.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_taxonomy' => $taxonomy['data']->id
            ]);
        }

        $this->alert($taxonomy['message'], 'danger');

        return back();
    }

    /**
     * Delete the specified resource from storage.
     *
     * @param array $ids
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function deletes(array $ids, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        $type = $params[2] ?? null;

        try {
            foreach ($ids as $id) {
                Taxonomy::delete($id);
            }

            $alert = trans_choice('taxonomy::base.messages.deleted_items', count($ids), [
                'taxonomy' => getTaxonomyTypeArg($type)
            ]);

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }

    /**
     * Change Status the specified resource from storage.
     *
     * @param array $ids
     * @param bool $value
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function changeStatus(array $ids, bool $value, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        $type = $params[2] ?? null;

        try {
            foreach ($ids as $id) {
                Taxonomy::update($id, ['status' => $value]);
            }

            if ($value) {
                $alert = trans_choice('taxonomy::base.messages.status.enable', count($ids), [
                    'taxonomy' => getTaxonomyTypeArg($type)
                ]);
            } else {
                $alert = trans_choice('taxonomy::base.messages.status.disable', count($ids), [
                    'taxonomy' => getTaxonomyTypeArg($type)
                ]);
            }

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }

    /**
     * Import data
     */
    public function import(ImportActionListRequest $request, string $panel, string $section, string $type)
    {
        //
    }

    /**
     * Export data
     */
    public function export(ExportActionListRequest $request, string $panel, string $section, string $type)
    {
        $export_type = $request->type;

        $filePath = public_path('favicon.ico');
        $fileName = 'favicon.ico';

        return response()->download($filePath, $fileName, [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    /**
     * Set Translation in list
     *
     * @param SetTranslationRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function setTranslation(SetTranslationRequest $request): JsonResponse
    {
        try {
            return $this->response(
                Taxonomy::setTranslation($request->validated())
            );
        } catch (Throwable $exception) {
            return $this->response(message: $exception->getMessage(), status: $exception->getCode());
        }
    }
}
