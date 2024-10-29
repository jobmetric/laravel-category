$(document).ready(function(){
    dt = $('#datatable').on('preXhr.dt', function (e, settings, data) {
    }).DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        drawCallback: function(settings) {},
        ajax: {
            url: localize.category.route,
            data: function (data) {
                if (data.order && data.order.length > 0) {
                    data.sort = data.order[0].dir === 'asc' ? data.columns[data.order[0].column].name : `-${data.columns[data.order[0].column].name}`
                }

                // Apply any additional filters as needed
                data.filter = {
                    name: $('#filter-name').val()
                }
            }
        },
        columns: [
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
                    return `<div class="align-start">${e.name_multiple}</div>`
                },
                sortable: true
            },
            // status
            {
                name: 'status',
                data: function(e) {
                    let text = null
                    if (e.status) {
                        text = `<div class="badge badge-light-success">${localize.language.package_core.status.enable}</div>`
                    } else {
                        text = `<div class="badge badge-light-danger">${localize.language.package_core.status.disable}</div>`
                    }
                    return `<div class="align-center">${text}</div>`
                },
                sortable: true
            },
            // ordering
            {
                name: 'ordering',
                data: function(e) {
                    return `<div class="align-center">${e.ordering}</div>`
                },
                sortable: true
            },
            // action
            {
                data: function(e) {
                    return `<div class="align-center">
                                <a href="${localize.category.route}/${e.id}/edit" class="btn btn-sm btn-light-info">
                                    <i class="la la-edit fs-2 position-absolute"></i>
                                    <span class="ps-9">${localize.language.panelio.button.edit}</span>
                                </a>
                           </div>`
                },
                sortable: false
            }
        ],
        order: [
            [1, "asc"]
        ],
        searching: false,
        lengthChange: false,
        deferRender: true,
        pageLength: localize.list_view.page_limit,
        language: localize.language.datatable
    })
})
