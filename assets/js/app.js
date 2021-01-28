import '../styles/style.scss';
// apiKey
import secret from "./secret";

let currentQuestion = 1;
let correctAnswers = 0;

$(document).ready(function(){

    if (window.location.href.indexOf("download") > -1) {
        getQuestion();
        console.log('get question');
        var intervalId = window.setInterval(function(){
            getQuestion();
            console.log('get question');
        }, 61100);
    }

    if($('.quiz-game').length){
        $('.question-wrapper').hide();
        $('.question-wrapper[data-question="1"]').show();
    }

    $('.quiz-choice').click(function() {
        $('.quiz-choice').removeClass('selected');
        $(this).addClass('selected');
        $('#submitQuiz').prop("disabled", false);
    });

    $('#submitQuiz').click(function() {
        if($(this).hasClass('next-question')){
            console.log('next');
            $(this).removeClass('next-question').text('Valider').prop("disabled", true);
            $('.question-wrapper[data-question="' + currentQuestion + '"]').remove();
            currentQuestion = currentQuestion + 1;
            $('.question-wrapper[data-question="' + currentQuestion + '"]').show();
        }else{
            console.log('submit');
            let questionId = $('.question-wrapper[data-question="' + currentQuestion + '"]').data('qid');
            console.log(questionId);
            getAnswer(questionId);
        }
    });
});

function getQuestion() {

    $.ajax({
        url:        'https://www.openquizzdb.org/api.php?key=' + secret.apiKey,
        type:       'POST',
        dataType:   'json',
        async:      true,

        success: function(data, status) {
            console.log(data);
            if(data.response_code === 0){
                saveQuestion(data);
            }else {
                console.log('to many requests');
                blocked = blocked + 1;
                $('.blocked').text(blocked);
            }
        },
        error : function(xhr, textStatus, errorThrown) {
            console.log('failed to get question');
        }
    });

}

let duplicates = 0;
let added = 0;
let blocked = 0;

function saveQuestion(data) {

    let question = data;

    $.ajax({
        url:        '/savequestion/ajax?data=' + JSON.stringify(data),
        type:       'POST',
        dataType:   'json',
        async:      true,

        success: function(data, status) {
            console.log(data.message);
            console.log(data.categories);

            $('.categories').empty();
            for (const [key, value] of Object.entries(data.categories)) {
                $('.categories').append('<li>' + key + ' : <b>' + value + '</b></li>');
            }

            if(data.duplicate){
                duplicates = duplicates + 1;
            }else{
                added = added + 1;

                let today = new Date();
                let date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
                let time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                let dateTime = date +' '+ time;
                $('.questions').append('<div>' + dateTime +'</div>');
                $('.questions').append('<li>' + question.results[0].question +'<div><b>'+ question.results[0].reponse_correcte + '</b></div></li>');
                $('.counter').text(data.count);
            }

            $('.new').text(added);
            $('.duplicate').text(duplicates);
            $('.success').text(((added / (added + duplicates)) * 100) + '%');

        },
        error : function(xhr, textStatus, errorThrown) {
            console.log('failed to navigate calendar');
        }
    });

}

function getAnswer(questionId) {

    $.ajax({
        url:        '/getcorrectanswers/ajax?data=' + JSON.stringify(questionId),
        type:       'POST',
        dataType:   'json',
        async:      true,

        success: function(data, status) {
            let correctAnswer = data.answer;
            manageResult(correctAnswer);
        },
        error : function(xhr, textStatus, errorThrown) {
            console.log('failed to get question correct answer');
        }
    });

}

function manageResult(correctAnswer){
    console.log('MANAGER');
    let selectedChoiceValue = $('.quiz-choice.selected').data('value');
    console.log({correctAnswer});
    console.log({selectedChoiceValue});

    if(selectedChoiceValue.toString() === correctAnswer.toString()){
        console.log('correct');
        $('.quiz-choice.selected').addClass('correct');
        correctAnswers = correctAnswers + 1;
    }else{
        console.log('wrong');
        $('.quiz-choice.selected').addClass('wrong');
        $('.quiz-choice[data-value="' + correctAnswer + '"]').addClass('correct');
    }

    $('#submitQuiz').addClass('next-question').text('Continuer');
}