$ = jQuery;
let newItem;
LibraryDataSource = new kendo.data.DataSource({
    transport: {
        read: function (options) {
            displayLoading($('body'));
            $.ajax({
                url: wpApiSettings.root + "rmbl-pubs/v1/admin/library",
                dataType: "json",
                method: "GET",
                data: options.data,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", wpApiSettings.nonce);
                },
                success: function (result) {
                    options.success(result);
                    attachTotals(result);
                    hideLoading($('body'));
                },
                error: function (result) {
                    options.error(result);
                    alert(result.responseText);
                    hideLoading($('body'));
                }
            });
        },
        update: function (options) {
            displayLoading($('body'));
            $.ajax({
                url: wpApiSettings.root + "rmbl-pubs/v1/admin/library",
                dataType: "json",
                method: "PUT",
                data: options.data,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", wpApiSettings.nonce);
                },
                success: function (result) {
                    $("#library-grid").data('kendoGrid').dataSource.read();
                    options.success(result);
                    hideLoading($('body'));
                },
                error: function (result) {
                    //  options.error(result);
                    alert(result.responseText);
                    hideLoading($('body'));
                }
            });
        },
        create: function (options) {
            displayLoading($('body'));
            $.ajax({
                url: wpApiSettings.root + "rmbl-pubs/v1/admin/library",
                dataType: "json",
                method: "POST",
                data: options.data,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", wpApiSettings.nonce);
                },
                success: function (result) {
                    newItem = result.data.data;

                    $("#library-grid").data('kendoGrid').dataSource.read().then(function () {
                        if (preventCloseOnSave) {
                            $("#library-grid").data("kendoGrid").editRow($(`#library-grid tr td:contains('${newItem.id}')`).parent());
                            preventCloseOnSave = false;
                        }
                    });

                    options.success(result);
                    hideLoading($('body'));
                    if ($("#libraryitemauthors") && $("#searchAuthors") && $("#saveItemOverlay")) {
                        $("#libraryitemauthors").prop('disabled', false); // Disable the Author Search
                        $("#searchAuthors").show() // Hide the author search.
                        // Hide the author search label
                        // Show Save Record Overlay
                        $("#saveItemOverlay").hide()
                    }
                },
                error: function (result) {
                    options.error(result);
                    alert(result.responseText);
                    hideLoading($('body'));
                }
            });
        }
    },
    serverPaging: true,
    serverFiltering: true,
    serverSorting: true,
    pageSize: 10,
    schema: {
        total: 'total',
        data: 'data',
        model: {
            id: "id",
            fields: {
                id: { type: "number" },
                reftypeId: { type: "number", validation: { required: true } },
                year: {
                    type: "number", validation: {
                        required: true, checklength
                    }
                },
                title: { type: "string", validation: { required: true } },
                volume: { type: "string" },
                edition: { type: "string" },
                publisherId: { type: "number" },
                pages: { type: "string" },
                restofreference: { type: "string" },
                journalname: { type: "string" },
                journalissue: { type: "string" },
                catalognumber: { type: "string" },
                donatedby: { type: "string" },
                chaptertitle: { type: "string" },
                bookeditors: { type: "string" },
                degree: { type: "string" },
                institution: { type: "string" },
                keywords: { type: "string" },
                comments: { type: "string" },
                bn_url: { type: "string" },
                abstract_url: { type: "string" },
                fulltext_url: { type: "string" },
                pdf_url: { type: "string" },
                copyinlibrary: { type: "string" },
                RMBL: { type: "string" },
                pending: { type: "string" },
                email: { type: "string" },
                student: { type: "string" },
                authors: { type: "object" },
                tags: { type: "object" }
                //authorIds: { type: "object" }
            }
        }
    }
});