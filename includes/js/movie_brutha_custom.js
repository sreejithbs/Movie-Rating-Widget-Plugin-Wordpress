(function( $ ) {

    "use strict";

    $(document).ready( function(){
        // Handle widget Form submission
        $("#widget_form_id").submit(function(e) {
            e.preventDefault();

            $(".overlay-btn").show();

            var run_hour = $("#run_hour").val();
            var run_minutes = $("#run_minutes").val();
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
                    run_minutes,
                    run_hour,
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

        $(".title_overview_update").click(function() {

            var type = $(this).attr('data-type');
            if(type == 'title'){
                var title = $("#title-text").val();
                if(title.trim() == ""){
                    toastr.error('Please enter some text');
                    return false;
                }

                var tempData = {
                    action: "handleTitleOverviewUpdate",
                    type: 'title',
                    value: title
                }

            } else{
                var overview = $("#overview-text").val();
                if(overview.trim() == ""){
                    toastr.error('Please enter some text');
                    return false;
                }

                var tempData = {
                    action: "handleTitleOverviewUpdate",
                    type: 'overview',
                    value: overview
                }
            }

            $.ajax({
                url: ajaxurl,
                dataType: "json",
                cache: false,
                method: "post",
                data: tempData,
                success: function(data) {
                    if(data.status){
                        toastr.success(data.message);
                    }
                },
                error: function(data) {
                    console.log("errordata", data);
                }
            });
        });

        // CLONE
        $("#clone_faq").click(function(){
            $(".faq-cover-div").append('<div class="row single-clone"><div class="col-md-5"><textarea cols="55" rows="3" name="faq_question[]" class="faq_question" placeholder="Enter Question"></textarea></div><div class="col-md-5"><textarea cols="55" rows="3" name="faq_answer[]" class="faq_answer" placeholder="Enter Answer"></textarea></div><div class="col-md-2"><button type="button" class="btn btn-danger btn-sm del_clone">x</button></div></div>');
        });

        $('body').on('click', '.del_clone', function() {
            $(this).closest('div.single-clone').remove();
        });

        $("#submit_faq").click(function(event) {

            var mainArr = new Array();
            var allFilled = true;
            var faq_question = $('textarea[name="faq_question[]"]').map(function (i) {
                if(this.value.trim() == ""){
                    toastr.error("Enter all fields");
                    allFilled = false;
                    return false;
                }

                var ans = $($('textarea[name="faq_answer[]"]')[i]).val();
                if(ans.trim() == ""){
                    toastr.error("Enter all fields");
                    allFilled = false;
                    return false;
                }

                var valueToPush = {};
                valueToPush["question"] = this.value.trim();
                valueToPush["answer"] = ans.trim();
                mainArr.push(valueToPush);
            });

            if(! allFilled) return false;

            $.ajax({
                url: ajaxurl,
                dataType: 'json',
                cache: false,
                method: 'post',
                data: {
                    'action': 'handleFAQSubmission',
                    mainArr,
                },
                success: function(data) {
                    if(data.status){
                        toastr.success(data.message);
                    }
                },
                error: function(data) {
                    console.log('inside handleFormSubmission error', data);
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