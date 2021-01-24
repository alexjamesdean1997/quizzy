/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

// apiKey
import secret from "./secret";

$(document).ready(function(){
    console.log('dom ready');
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
            let today = new Date();
            let date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
            let time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            let dateTime = date +' '+ time;
            $('.questions').append('<div>' + dateTime +'</div>');
            $('.questions').append('<li>' + question.results[0].question +'<div><b>'+ question.results[0].reponse_correcte + '</b></div></li>');
            $('.counter').text(data.count);
            console.log(data.message);

            if(data.duplicate){
                duplicates = duplicates + 1;
            }else{
                added = added + 1;
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

getQuestion();
var intervalId = window.setInterval(function(){
    getQuestion();
    console.log('get question');
}, 60100);