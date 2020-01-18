/** * Created by Muhammad Faizan Khalil on 9/30/2019. */
/* get courses details */
function get_courses_by_class_id(class_id) {
    $.ajax({
        type    : 'get',
        url     : '/courses_by_class_id/'+class_id,
        success : function (data) {
            $("#expand_courses").html('');
            var course_id = '';
            var course_name = '';
            var i;
            if (data.length > 0){
                var expand_data = '';
                for (i = 0; i < data.length; i++){
                    course_id = data[i]['id'];
                    course_name = data[i]['name'];
                    expand_data += '<option value="'+course_id+'">'+course_name+'</option>';
                }
                $("#expand_courses").append(expand_data);
            } else {
                $("#expand_courses").append('no course found');
            }
            },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Exception:' + errorThrown);
        }
    });
}
/* to display the questions in pop up box */
function open_question_popup(quiz_id) {
    $.ajax({
        type: "get",
        url: '/get_quiz_questions/'+quiz_id,
        success: function (data) {
            console.log(data);
            $('#myModal').modal('show');
            $('#myModalLabel').html('Questions');
            $('#mydata').html(data);
        }
    })
}
    /* to display the questions in pop up box */
function open_confirm_popup(quiz_id) {
    $.ajax({
        type: "get",
        url: '/get_quiz_questions/'+quiz_id,
        success: function (data) {
            $('#myModal').modal('show');
            $('#myModalLabel').html('Questions');
            $('#mydata').html(data);
        }
    })
}
/* function to view answers */
function view_answer(question_id,student_id) {
    $.ajax({
        type: "get",
        url: '/student_quiz_answers',
        data:"question_id="+question_id+"&student_id="+student_id,
        success: function (data) {
            console.log(data);
            $('#myModalanswer').modal('show');
            $('#myModalLabelanswer').html('Quiz Answers');
            $('#mydataanswer').html(data);
        }
    })
}
function add_marks_and_review(question_id,student_id) {
    $.ajax({
        type: "get",
        url: '/add_quiz_marks',
        data:"question_id="+question_id+"&student_id="+student_id,
        success: function (data) {
            console.log(data);
            $('#myModalanswer').modal('show');
            $('#myModalLabelanswer').html('Add Marks And Review');
            $('#mydataanswer').html(data);
        }
    })
}
function open_quiz_marks_suggesstion(question_id) {
    $.ajax({
        type: "get",
        url: '/display-student-result',
        data:"question_id="+question_id,
        success: function (data) {
            $('#myModal').modal('show');
            $('#myModalLabel').html('Quiz Test');
            $('#mydata').html(data);
        }
    })
}
function again_test_request(quiz_id) {
    $.ajax({
        type: "get",
        url: '/add_test_request',
        data:"quiz_id="+quiz_id,
        success: function (data) {
            $('#myModalanswer').modal('show');
            $('#myModalLabelanswer').html('Add Marks And Review');
            $('#mydataanswer').html(data);
            },
        error: function (msg,data) {
            console.log(msg);
            console.log(data);
        }
    })
}
/* open solution of quiz */
function open_solution(quiz_id) {
    $.ajax({
        type: "get",
        url: '/display-quiz-result',
        data:"quiz_id="+quiz_id,
        success: function (data) {
            $('#myModalanswer').modal('show');
            $('#myModalLabelanswer').html('Quiz Test');
            $('#mydataanswer').html(data);
        }
    })
}
function approve_cancel_test_request(test_status,request_id) {
    $.ajax({
        type: "get",
        url: '/action_test_request',
        data:"request_id="+request_id+"&test_status="+test_status,
        success: function (data) {
            $('#myModal').modal('show');
            $('#myModalLabel').html('Action Test Request');
            $('#mydata').html(data);
        }
    })
}

