$(function () {
    $('.js-basic-example').DataTable({
        responsive: true,
        "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]]
    });
});