$(document).ready(function(){
    dt = $('#datatable').on('preXhr.dt', function (e, settings, data) {
    }).DataTable({
        responsive: false,
        processing: true,
        serverSide: true,
        drawCallback: function(settings) {},
        ajax: {
            url: localize.taxonomy.route,
            data: function (data) {
                if (data.order && data.order.length > 0) {
                    data.sort = data.order[0].dir === 'asc' ? data.columns[data.order[0].column].name : `-${data.columns[data.order[0].column].name}`
                }

                // Apply any additional filters as needed
                data.filter = {
                    name: $('#filter-name').val()
                }

                data.metadata = {}
                $('.filter-metadata').each(function(){
                    data.metadata[$(this).attr('name')] = $(this).val();
                });
            }
        },
        columns: [
            // show details
            {
                data: function(e) {
                    return `<a href="javascript:void(0)" class="btn btn-usm btn-dark btn-icon btn-circle show-details">
                                <i class="la la-plus fs-5"></i>
                            </a>`
                },
                sortable: false
            },
            // checkbox
            {
                data: function(e) {
                    return `<div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input check-one" name="ids[]" type="checkbox" value="${e.id}" id="check-single-${e.id}"/>
                                <label class="form-check-label ms-0" for="check-single-${e.id}"></label>
                            </div>`
                },
                sortable: false
            },
            // name
            {
                name: 'name',
                data: function(e) {
                    if (e.name_multiple) {
                        return `<div class="align-start text-gray-800">${e.name_multiple}</div>`
                    } else {
                        return `<div class="align-start text-gray-800"><div class="badge badge-light-danger">${localize.language.package_core.undefined_in_this_language}</div></div>`
                    }
                },
                sortable: true
            },
            // status
            {
                name: 'status',
                data: function(e) {
                    let text = null
                    if (e.status) {
                        text = `<div class="badge badge-light-success">${localize.language.package_core.components.boolean_status.enable}</div>`
                    } else {
                        text = `<div class="badge badge-light-danger">${localize.language.package_core.components.boolean_status.disable}</div>`
                    }
                    return `<div class="align-center">${text}</div>`
                },
                sortable: true
            },
            // ordering
            {
                name: 'ordering',
                data: function(e) {
                    return `<div class="align-center text-gray-800">${e.ordering}</div>`
                },
                sortable: true
            },
            // action
            {
                data: function(e) {
                    return `<div class="align-center">
                                <div class="d-flex align-items-center">
                                    <a href="${localize.taxonomy.route}/${e.id}/edit" class="btn btn-sm btn-light-info bg-hover-secondary">
                                        <i class="la la-edit fs-2 position-absolute"></i>
                                        <span class="ps-9">${localize.language.panelio.button.edit}</span>
                                    </a>
                                </div>
                           </div>`
                },
                sortable: false
            }
        ],
        order: [
            [2, "asc"]
        ],
        searching: false,
        lengthChange: false,
        deferRender: true,
        pageLength: localize.list_view.page_limit,
        language: localize.language.datatable
    })

    // Toggle child row on click
    function show_details(data) {
        const date_created_at = new Date(data.created_at)
        const local_date_created_at =
            date_created_at.getFullYear() + '-' +
            String(date_created_at.getMonth() + 1).padStart(2, '0') + '-' +
            String(date_created_at.getDate()).padStart(2, '0') + ' ' +
            String(date_created_at.getHours()).padStart(2, '0') + ':' +
            String(date_created_at.getMinutes()).padStart(2, '0') + ':' +
            String(date_created_at.getSeconds()).padStart(2, '0')

        const date_updated_at = new Date(data.updated_at)
        const local_date_updated_at =
            date_updated_at.getFullYear() + '-' +
            String(date_updated_at.getMonth() + 1).padStart(2, '0') + '-' +
            String(date_updated_at.getDate()).padStart(2, '0') + ' ' +
            String(date_updated_at.getHours()).padStart(2, '0') + ':' +
            String(date_updated_at.getMinutes()).padStart(2, '0') + ':' +
            String(date_updated_at.getSeconds()).padStart(2, '0')

        let html = `
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="card card-xxl-stretch mb-xl-8 theme-dark-bg-body h-xl-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex flex-column mb-7">
                                <a href="javascript:void(0)" class="text-dark text-hover-primary fw-bold fs-3">${data.name}</a>
                            </div>
                            <div class="row g-0">
                                <div class="col-12">
                                    <div class="d-flex align-items-center mb-9 me-2">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-light">
                                                <i class="ki-duotone ki-calendar fs-1 text-dark">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${local_date_created_at}</div>
                                            <div class="fs-7 text-gray-600 fw-bold">${localize.language.package_core.fields.created_at}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center me-2">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-light">
                                                <i class="ki-duotone ki-calendar fs-1 text-dark">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${local_date_updated_at}</div>
                                            <div class="fs-7 text-gray-600 fw-bold">${localize.language.package_core.fields.updated_at}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card card-flush h-xl-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">${localize.language.metadata.components.metadata_card.title}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            <div class="row">
                                <div class="col-12">`
                                    $.each(data.metas, function(key, meta) {
                                        $.each(meta, function(key, value) {
                                            html += `<div class="col-12">
                                                        <div class="d-flex justify-content-between align-items-center border border-dashed border-hover-secondary p-3">
                                                            <div>${eval(`localize.taxonomy.metadata.${key}.label`)}</div>
                                                            <div>${value}</div>
                                                        </div>
                                                    </div>`
                                        })
                                    })
                        html += `</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card card-flush h-xl-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">${localize.language.package_core.tabs.connections}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            <div class="row">
                                <div class="col-12">`
                                    $.each(data.taxonomyRelations, function(key, relation) {
                                        if (relation.length === 0) {
                                            return
                                        }

                                        if (relation.taxonomizable) {

                                        } else {
                                            const date_relation_created_at = new Date(relation.created_at)
                                            const local_date_relation_created_at =
                                                date_relation_created_at.getFullYear() + '-' +
                                                String(date_relation_created_at.getMonth() + 1).padStart(2, '0') + '-' +
                                                String(date_relation_created_at.getDate()).padStart(2, '0') + ' ' +
                                                String(date_relation_created_at.getHours()).padStart(2, '0') + ':' +
                                                String(date_relation_created_at.getMinutes()).padStart(2, '0') + ':' +
                                                String(date_relation_created_at.getSeconds()).padStart(2, '0')

                                            html += `<div class="col-12">
                                                        <div class="border border-dashed border-hover-secondary p-3">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>${relation.taxonomizable_type}</div>
                                                                <div>${relation.taxonomizable_id}</div>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>${relation.collection}</div>
                                                                <div dir="ltr">${local_date_relation_created_at}</div>
                                                            </div>
                                                        </div>
                                                    </div>`
                                        }
                                    })
                        html += `</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`
        return html
    }

    $('#datatable tbody').on('click', 'td .show-details', function () {
        const tr = $(this).closest('tr')
        const row = dt.row(tr)
        const icon = $(this).find('i')

        dt.rows().every(function () {
            if (this.child.isShown() && this.index() !== row.index()) {
                this.child.hide();
                $(this.node()).removeClass('shown');
                $(this.node()).find('.show-details i').removeClass('la-minus').addClass('la-plus')
            }
        })

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('la-minus').addClass('la-plus')
        } else {
            row.child(show_details(row.data())).show();
            tr.addClass('shown');
            icon.removeClass('la-plus').addClass('la-minus')
        }
    });
})
