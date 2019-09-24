(function( $ ) {

    "use strict";

    $(document).ready( function(){
        // Handle widget Form submission
        $("#widget_form_id").submit(function(e) {
            e.preventDefault();

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
            var genres = $('input[name="genres[]"]').map(function () {
                if($(this).is(":checked")){
                    checked_genres.push($(this).val());
                }
                return $(this).is(":checked");
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
                        $("#score_span").text(response.score);
                        $("#formDiv").hide();
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
    });

})(jQuery);