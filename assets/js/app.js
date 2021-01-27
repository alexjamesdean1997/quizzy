import '../styles/style.scss';
// apiKey
import secret from "./secret";

$(document).ready(function(){
    console.log('dom ready');

    if (window.location.href.indexOf("download") > -1) {
        getQuestion();
        console.log('get question');
        var intervalId = window.setInterval(function(){
            getQuestion();
            console.log('get question');
        }, 60300);
    }
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
            }
        },
        error : function(xhr, textStatus, errorThrown) {
            console.log('failed to get question');
        }
    });

}

let duplicates = 0;
let added = 0;

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