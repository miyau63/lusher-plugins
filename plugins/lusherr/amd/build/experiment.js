define(['jquery'], function(text) { // Moodle needs this to recognise $ https://docs.moodle.org/dev/jQuery .
    // JQuery is available via $.

    return {
        initialise: function (text) {
            var questionText = document.getElementsByClassName("qtext");
            questionText.item(0).innerHTML = text;

        }
    }
});