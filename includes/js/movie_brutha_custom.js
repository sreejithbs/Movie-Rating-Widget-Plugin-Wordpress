(function( $ ) {

    "use strict";

    $(document).ready( function(){
        // Handle widget Form submission
        $("#widget_form_id").submit(function(e) {
            e.preventDefault();

            $(".overlay-btn").show();

            var run_time = $("#run_time").val();
            var age_of_film = $("#age_of_film").val();
            var director_score = $("#director_score").val();
            var director_name = $("#director_score :selected").text().trim();

            // var genres = [];
            // var temp;
            // $('input[name="genres[]"]').each(function (j, ob) {
            //     var temp = {
            //         key: $(ob).val(),
            //         status: $(ob).is(":checked")
            //     };
            //     genres.push(temp);
            // });

            var checked_genres = [];
            var genres = $(".genres > option").map(function() {
                if ($(this).is(':selected')) {
                    checked_genres.push(this.value);
                }
                return $(this).is(':selected');
            }).get();

            $.ajax({
                url: ajaxurl,
                dataType: 'json',
                cache: false,
                method: 'post',
                data: {
                    'action': 'handleFormSubmission',
                    run_time,
                    age_of_film,
                    director_score,
                    genres,
                    checked_genres,
                    director_name
                },
                success: function(response) {
                    if(response.status){

                        $(".js-select2").val(null).trigger('change');

                        $("#score_span").text(response.score);
                        $("#formDiv").hide();
                        $(".overlay-btn").hide();
                        $("#scoreDiv").show();

                    } else {
                        console.log(data.message);
                    }
                },
                error: function(data) {
                    console.log('inside handleFormSubmission error');
                }
            });
        });

        // Show widget Form
        $("#retake_rating").click(function() {
            $("#widget_form_id")[0].reset();
            $("#score_span").empty();
            $("#scoreDiv").hide();
            $("#formDiv").show();
        });

        $(".js-select2").select2({
            closeOnSelect : false,
            placeholder : "Select Genres",
            allowClear: true,
        });

        $('#btCategoriaA').click(function() {
            $('#listaCategoriaA').slideToggle('slow', function() {
                $('#btCategoriaA').toggleClass('showing', $(this).is(':visible'));
            });
        });

        $(".rate-box-outer").show();
    });

})(jQuery);