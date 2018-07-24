(function($){
  $(document).ready(function () {
    $('.nav-tree .has-substeps').toggleClass('open');
    $('.nav-tree .in-path').toggleClass('open');
    $('.nav-tree .open-toggle').on('click', function() {
      $(this).parent().parent().toggleClass('open');
    })
  });
}(jQuery));


